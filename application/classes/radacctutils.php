<?php defined('SYSPATH') or die('No direct script access.');

class RadAcctUtils {

	public static function byteUnits($num_bytes, $as_string = false)
	{
        	$rv['unit'] = ' B';
        	$rv['value'] = $num_bytes;

		if($rv['value'] > 1024*1024*1024)
        	{
                	$rv['value'] /= 1024*1024*1024;
                	$rv['value'] = sprintf("%02.2f", $rv['value']);
                	$rv['unit'] = "GB";
        	}
	       else if($rv['value'] > 1024*1024)
        	{
                	$rv['value'] /= 1024*1024;
	                $rv['value'] = sprintf("%02.2f", $rv['value']);
        	        $rv['unit'] = "MB";
	       }
        	else if($rv['value'] > 1024)
        	{	
                	$rv['value'] /= 1024;
                	$rv['value'] = sprintf("%02.2f", $rv['value']);
                	$rv['unit'] = "KB";
        	}
		if($as_string)
		{
			return $rv['value']." ".$rv['unit'];
		}
        	return $rv;
	}

	public static function getStartDate()
	{
       	 return mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
	}

	private static function getDataSet($rrd_file, $resolution)
	{
		$rv = array();
		$thirteen_months = 60*60*24*31*13;

		$options = array("AVERAGE", "-s", "-$thirteen_months", "-r", $resolution);
		
		$data = rrd_fetch($rrd_file, $options);

		if(!is_array($data))
		{
			return array();
		}

		foreach($data['data']['ds0'] as $time=>$value)
		{
			if(is_nan($value))
			{
				$one = 0;
				$two = 0;
                	}
			else
			{
		                $one = ((int)$value) * $resolution;
	       	         	$two = ((int)$data['data']['ds1'][$time]) * $resolution;
			}

			$year = (int)date("Y", $time);
			$month = (int)date("m", $time);
			$day = (int)date("j", $time);

			if(!isset($rv[$year][$month][$day]['down']))
			{
				$rv[$year][$month][$day]['down'] = 0;
			}
			if(!isset($rv[$year][$month][$day]['up']))
			{
				$rv[$year][$month][$day]['up'] = 0;
			}

			$rv[$year][$month][$day]['down'] += $two;
			$rv[$year][$month][$day]['up'] += $one;
		}

		return $rv;
	}

	public static function combineNodeDeploymentsData($nodeDeployments)
	{
		if (sizeof($nodeDeployments) == 1)
			return array_shift($nodeDeployments);
		
		$deployment_data = array();
		foreach ($nodeDeployments as $nodeDeployment)
		{
			foreach(array_keys($nodeDeployment) as $year)
	                {
				foreach(array_keys($nodeDeployment[$year]) as $month)
	                        {
	                        	foreach(array_keys($nodeDeployment[$year][$month]) as $day)
	                               	{
						if (!isset($deployment_data[$year][$month][$day]))
						{
							$deployment_data[$year][$month][$day]['down'] = $nodeDeployment[$year][$month][$day]['down'];
							$deployment_data[$year][$month][$day]['up'] = $nodeDeployment[$year][$month][$day]['up'];
						}
						else
						{
							$deployment_data[$year][$month][$day]['down'] += $nodeDeployment[$year][$month][$day]['down'];
							$deployment_data[$year][$month][$day]['up'] += $nodeDeployment[$year][$month][$day]['up'];
						}
					}
				}
					
			}
		}
		return $deployment_data;
	}
						
	private static function mergeData($data_one, $data_two)
	{
		$rv = $data_one;

		if(!is_array($data_one))
		{
			return $data_two;
		}
		if(!is_array($data_two))
		{
			return $data_one;
		}


		$years = array_unique(array_merge(array_keys($data_one), array_keys($data_two)));

		foreach($years as $year)
		{
			$d1m = array();
			$d2m = array();

			if(isset($data_one[$year]))
			{
				$d1m = $data_one[$year];
			}
			if(isset($data_two[$year]))
			{
				$d2m = $data_two[$year];
			}

			$months = array_unique(array_merge(array_keys($d1m), array_keys($d2m)));
			foreach($months as $month)
			{

				$d1d = array();
				$d2d = array();

				if(isset($data_one[$year][$month]))
				{
					$d1d = $data_one[$year][$month];
				}
				if(isset($data_two[$year][$month]))
				{
					$d2d = $data_two[$year][$month];
				}

				$days = array_unique(array_merge(array_keys($d1d), array_keys($d2d)));

				foreach($days as $day)
				{
					if (isset($d1d[$day]))
						$v1 = $d1d[$day];
					else
						$v1 = 0;
					if (isset($d2d[$day]))
						$v2 = $d2d[$day];
					else
						$v2 = 0;

					if($v1 > $v2)
					{
						$rv[$year][$month][$day] = $v1;
					}
					else
					{
						$rv[$year][$month][$day] = $v2;
					}
				}	
			}
		}

		return $rv;
	}

	public static function getData($rrd_file)
	{
		$rv = array();

		$data_one = RadAcctUtils::getDataSet($rrd_file, 300);
		$data_two = RadAcctUtils::getDataSet($rrd_file, 1800);
		$data_three = RadAcctUtils::getDataSet($rrd_file, 7200);
		$data_four = RadAcctUtils::getDataSet($rrd_file, 86400);
	
		if(!is_array($data_two))
		{
			print "Data one isn't an array\n";
		}

		# start with a copy of the daily data
		$rv = RadAcctUtils::mergeData($data_four, $data_three);
		$rv = RadAcctUtils::mergeData($rv, $data_two);
		$rv = RadAcctUtils::mergeData($rv, $data_one);

		return $rv;
	}

	public static function getDataSummary($data, $deployment = null)
	{
		if(!is_array($data))
		{
			return FALSE;
        	}

		$total = 0;
		$string = "";

		foreach($data as $year=>$year_data)
		{
			foreach($year_data as $month=>$days)
			{
				$bytes_up = 0;
				$bytes_down = 0;
				foreach($days as $day)
				{
					$bytes_up += $day['up'];
					$bytes_down += $day['down'];
				}

				$bytes = $bytes_up + $bytes_down;
				$total += $bytes;
				$t = RadAcctUtils::byteUnits($bytes);
				$bw = $t['value']." ".$t['unit'];

				$percentage_used = "";
				if (isset($deployment) && $deployment->cap > 0)
				{
					$percentage_used = ($bytes * 100 ) / $deployment->cap;
					$percentage_used = "(" . sprintf("%02.2f", $percentage_used) . " %)";
				}
				
				$month_name = date('F', mktime(0, 0, 0, $month));
				$string .= "<b>$month_name</b> $bw $percentage_used<br/>\n";
			}
		}

		return array($total, $string);
	}

	public static function getMonthlyTotals($data)
	{
		
		if (!is_array($data))
                        return FALSE;

                $monthly_totals = array();

		foreach($data as $year => $year_data)
                {
                        foreach($year_data as $month => $days)
                        {
				$monthly_total = 0;
                                foreach($days as $day)
                                {
					$monthly_total += $day['up'] + $day['down'];
                                }
				$monthly_totals[date('M y', mktime(0, 0, 0, $month, 1, $year))] = $monthly_total;
			}
		}
		return $monthly_totals;
	}

	public static function getBandwidthUsage($rrd_file,$duration = 30) {
		if (!file_exists($rrd_file)) {
			return 0;
		}
		$stuff = RadAcctUtils::getData($rrd_file);
		$total = 0;
		for ($i=0;$i<$duration;$i++) {
			$date = @date();
			$year = date('Y',strtotime('-'.$i.' day' . $date));
			$month = date('n',strtotime('-'.$i.' day' . $date));
			$day = date('j',strtotime('-'.$i.' day' . $date));
			$total += @$stuff[$year][$month][$day]["up"];
			$total += @$stuff[$year][$month][$day]["down"];
		}
		return $total;
	}

	public static function IsLocalUser($username)
	{
		$config = Kohana::$config->load('database')->get('accounts-'.str_replace('.', '_', RadAcctUtils::GetDomainPart($username)));
		return !is_null($config);
	}

	public static function loadDatabaseConfig($username)
	{
		return Kohana::$config->load('database.accounts-' . str_replace('.', '_', RadAcctUtils::GetDomainPart($username)) . '.connection');
	}
	
	private static function GetUserPart($username)
	{
		list($username, $domain) = explode('@', $username, 2);
		return $username;
	}
	
	private static function GetDomainPart($username)
	{
		list($username, $domain) = explode('@', $username, 2);
		return $domain;
	}

	public static function UserNotExists($username)
	{
		$conn = RadAcctUtils::loadDatabaseConfig($username);
                $pdo = new PDO("mysql:hostname=" . $conn['hostname'] . ";dbname=" . $conn['database'], $conn['username'], $conn['password']);
		$query = $pdo->prepare("SELECT COUNT(*) FROM radcheck WHERE username = :username");
		$userpart = RadAcctUtils::GetUserPart($username);
		$query->bindParam(':username', $userpart, PDO::PARAM_STR);
		$query->execute();	
		$numUsers = $query->fetchColumn(0);
		return ($numUsers == 0);
	}

	public static function generateRandomString($stringLength = 8)
        {
                $string = "";
                $stringChars = "0123456789";
                $stringChars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $stringChars .= "abcdefghijklmnopqrstuvwxyz";

                for ($i=0; $i<$stringLength; $i++){
                        $string .= $stringChars[(rand() % strlen($stringChars))];
                }
                return $string;
        }
	
	private static function Hash($password)
	{
		$hash = new smbHash();
		return $hash->nthash($password);
	}
	
	public static function AddUser($username, $password)
	{
		return RadAcctUtils::AddUserHash($username, RadAcctUtils::Hash($password));
	}
	
	private static function AddUserHash($username, $hash)
	{
		$conn = RadAcctUtils::loadDatabaseConfig($username);
                $pdo = new PDO("mysql:hostname=" . $conn['hostname'] . ";dbname=" . $conn['database'], $conn['username'], $conn['password']);
		$query = $pdo->prepare("INSERT INTO radcheck SET username = :username, attribute = 'NT-Password', value = :hash, Op = ':='");
		$userpart = RadAcctUtils::GetUserPart($username);
		$query->bindParam(':username', $userpart, PDO::PARAM_STR);
                $query->bindParam(':hash', $hash, PDO::PARAM_STR);
		try 
		{
			$query->execute();
		} 
		catch (PDOException $e) 
		{
			return FALSE;
		}	
		return TRUE;	
	}

	public static function ResetPassword($username, $newpassword)
        {
		if (empty($username))
                {
                        return FALSE;
                }
                if(!RadAcctUtils::IsLocalUser($username))
                {
                        return FALSE;
                }

                return RadAcctUtils::UpdateUser($username, $newpassword);
        }
	
	public static function UpdateUser($username, $password, $oldpassword = NULL)
	{
		if ($oldpassword === NULL)
			return RadAcctUtils::UpdateUserHashNoOldPassword($username, RadAcctUtils::Hash($password));
		return RadAcctUtils::UpdateUserHash($username, RadAcctUtils::Hash($password), RadAcctUtils::Hash($oldpassword));
	}
	
	public static function DeleteUser($username)
	{
		$conn = RadAcctUtils::loadDatabaseConfig($username);
                $pdo = new PDO("mysql:hostname=" . $conn['hostname'] . ";dbname=" . $conn['database'], $conn['username'], $conn['password']);
                $query = $pdo->prepare("DELETE FROM radcheck WHERE username = :username AND attribute = 'NT-Password' AND Op = ':='");
		$userpart = RadAcctUtils::GetUserPart($username);
                $query->bindParam(':username', $userpart, PDO::PARAM_STR);
                try
                {
                        $query->execute();
                }
                catch (PDOException $e)
                {
                        return FALSE;
                }
                return $query->rowCount();;
	}
	
	private static function UpdateUserHash($username, $hash, $oldhash)
	{
                $conn = RadAcctUtils::loadDatabaseConfig($username);
		$pdo = new PDO("mysql:hostname=" . $conn['hostname'] . ";dbname=" . $conn['database'], $conn['username'], $conn['password']);
                $query = $pdo->prepare("UPDATE radcheck SET value = :hash WHERE username = :username AND attribute = 'NT-Password' AND Op = ':=' AND value = :oldhash");
		$userpart = RadAcctUtils::GetUserPart($username);
                $query->bindParam(':username', $userpart, PDO::PARAM_STR);
		$query->bindParam(':hash', $hash, PDO::PARAM_STR);
		$query->bindParam(':oldhash', $oldhash, PDO::PARAM_STR);
                try
                {
                        $query->execute();
                }
                catch (PDOException $e)
                {
                        return FALSE;
                }
                return $query->rowCount();
	}

	private static function UpdateUserHashNoOldPassword($username, $hash)
	{
		$conn = RadAcctUtils::loadDatabaseConfig($username);
                $pdo = new PDO("mysql:hostname=" . $conn['hostname'] . ";dbname=" . $conn['database'], $conn['username'], $conn['password']);
                $query = $pdo->prepare("UPDATE radcheck SET value = :hash  WHERE username = :username AND attribute = 'NT-Password' AND Op = ':='");
		$userpart = RadAcctUtils::GetUserPart($username);
                $query->bindParam(':username', $userpart, PDO::PARAM_STR);
                $query->bindParam(':hash', $hash, PDO::PARAM_STR);
                try
                {
                        $query->execute();
                }
                catch (PDOException $e)
                {
                        return FALSE;
                }
                return $query->rowCount();
	}
}
