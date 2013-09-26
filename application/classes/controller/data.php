<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Data extends Controller
{
	public function action_default()
	{
		echo "<p>The following data calls are available:</p><ul>";
		echo "<li><a href=\"data/current_radius_users\">current_radius_users</a></li>";
		echo "</ul>";
	}

	public function action_current_radius_users()
	{
		$lru_filename = Kohana::$config->load('system.default.static_files.lastradusers');
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra');
		$qb->where("ra.acctstoptime IS NULL");
		$qb->andWhere("ra.acctinputoctets > 0 OR ra.acctoutputoctets > 0");
		$query = $qb->getQuery();
                $curusers = $query->getResult();
		$lastusers = array();
		if (file_exists($lru_filename))
		{
			$csvdata = file_get_contents($lru_filename);
			$userarray = CSV::string_to_array($csvdata, true);
			foreach($userarray as $user) {
				$lastusers[$user['radacctid']] = $user;
			}
		}
		$activeusers = 0;
		foreach ($curusers as $curuser) 
		{
			$acctstarttime = $curuser->acctstarttime->format('U');
			if ($acctstarttime > time() - 3600 || empty($lastusers[$curuser->radacctid]) || ($curuser->acctinputoctets > $lastusers[$curuser->radacctid]['acctinputoctets'] || $curuser->acctoutputoctets > $lastusers[$curuser->radacctid]['acctoutputoctets']))
				$activeusers++;
		}
		if (!file_exists($lru_filename) || filemtime($lru_filename) < date('s') - 3600)
		{
			$fh = fopen($lru_filename, 'w');
			fwrite($fh, "radacctid,acctinputoctets,acctoutputoctets\n");
			foreach ($curusers as $curuser) 
			{
				fwrite($fh, "{$curuser->radacctid},{$curuser->acctinputoctets},{$curuser->acctoutputoctets}\n");
			}
			fclose($fh);
		}
                if ($activeusers < 1) 
			echo '0';
		else 
			echo $activeusers;
	}

}

