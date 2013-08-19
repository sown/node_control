<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Scripts extends Controller_Template 
{
	public function before()
        {
		if (isset($_SERVER['HTTP_USER_AGENT'])) 
			die("Scripts can only be run command line.");
		$_SERVER['REQUEST_URI'] = 'http://localhost';
		$_SERVER['HTTP_HOST'] = 'localhost';  
	}

	public function action_update_nass()
	{
		$NAS_RRDS = Kohana::$config->load('system.default.rrd.deployment_path');
		# Get the list of users
		$dt = new \DateTime();
		$now = $dt->format("Y-m-d H:i:s");
		$query = Doctrine::em()->createQuery("SELECT nd.id, i.ssid, na.mac, nd.startDate, nd.endDate FROM Model_NodeDeployment nd JOIN nd.node n JOIN n.interfaces i JOIN i.networkAdapter na WHERE EXISTS (SELECT DISTINCT ra.calledstationid FROM Model_Radacct ra WHERE SUBSTRING(ra.calledstationid, 1, 2) = SUBSTRING(na.mac, 1, 2) AND SUBSTRING(ra.calledstationid, 4, 2) = SUBSTRING(na.mac, 4, 2) AND SUBSTRING(ra.calledstationid, 7, 2) = SUBSTRING(na.mac, 7, 2) AND SUBSTRING(ra.calledstationid, 10, 2) = SUBSTRING(na.mac, 10, 2) AND SUBSTRING(ra.calledstationid, 13, 2) = SUBSTRING(na.mac, 13, 2) AND SUBSTRING(ra.calledstationid, 16, 2) = SUBSTRING(na.mac, 16, 2)) AND (nd.endDate > '$now' OR nd.endDate IS NULL) AND i.is1x = TRUE");

		$results = $query->getResult();
//		\Doctrine\Common\Util\Debug::dump($results);
//		exit();
		
		# This will store the mapping built in phase 1, used in phase2
		$calledstation_mapping = array();


		# PHASE 1 - Check each user has an rrd file - create for new users
		foreach ($results as $row)
		{
		        $calledstationid = str_replace(":", "-", $row['mac']) . ":" . $row['ssid'];
			$calledstationid_sanitized = str_replace($calledstationid,  ":", "_");
		        $calledstation_mapping[$calledstationid] = array(
				'nodedeploymentid' => $row['id'],
				'startdate' => $row['startDate'],
				'enddate' => $row['endDate'],
				'calledstationid_sanitized' => $calledstationid_sanitized,
			);
		        if( $row['id'] !== NULL)
        		{
                		$path = "{$NAS_RRDS}/node_deployment{$row['id']}.rrd";
				# if we find an old format name, migrate it
		          	$old_path = "{$NAS_RRDS}/{$calledstationid_sanitized}.rrd";
                		if(file_exists($old_path))
                		{
                        		echo "Migrating {$old_path} to {$path}\n";
                        		rename($old_path, $path);
                		}
                		$old_path = "{$NAS_RRDS}/deployment{$row['id']}.rrd";
                		if(file_exists($old_path))
                		{
                        		echo "Migrating {$old_path} to {$path}\n";
                        		rename($old_path, $path);
                		}
        		}
        		else
        		{
                		$path = "{$NAS_RRDS}/{$calledstationid_sanitized}.rrd";
        		}

        		# Create the new rrd if it doesn't exists
        		if(!file_exists($path))
        		{
                		$start_date = time() - 30;
                		$cmd = "/usr/bin/rrdtool create {$path}" .
                	        	" --start {$start_date} " .
        	                	" DS:ds0:COUNTER:600:0:1250000" .
	                        	" DS:ds1:COUNTER:600:0:1250000" .
                        		" RRA:AVERAGE:0.5:1:800" .
                        		" RRA:AVERAGE:0.5:6:800" .
                	        	" RRA:AVERAGE:0.5:24:800" .
        	                	" RRA:AVERAGE:0.5:288:800" .
	                        	" RRA:MAX:0.5:1:800" .
                        		" RRA:MAX:0.5:6:800" .
                        		" RRA:MAX:0.5:24:800" .
                        		" RRA:MAX:0.5:288:800";
                		system($cmd);
        		}
		}

	        foreach($calledstation_mapping as $calledstationid => $data)
       	 	{
			# Get the current bandwidth counters from the database
                	if($data['nodedeploymentid'] != NULL && $data['startdate'] != NULL)
                	{
				$query = Doctrine::em()->createQuery("SELECT ra.calledstationid, SUM(ra.acctinputoctets) AS acctinputoctets_total, SUM(ra.acctoutputoctets) AS acctoutputoctets_total FROM Model_Radacct ra WHERE ra.acctstarttime > '".$data['startdate']->format("Y-m-d H:i:s")."' AND ra.calledstationid = '".$calledstationid."' GROUP BY ra.calledstationid");
                	}
                	else
                	{
				$query = Doctrine::em()->createQuery("SELECT ra.calledstationid, SUM(ra.acctinputoctets) AS acctinputoctets_total, SUM(ra.acctoutputoctets) AS acctoutputoctets_total FROM Model_Radacct ra WHERE ra.calledstationid = '".$calledstationid."' GROUP BY ra.calledstationid");	
                	}
		        $results = $query->getResult();
		        if(isset($results[0]))
                 	{
		                if(isset($data['nodedeploymentid']))
                	        {
                               		$path = "$NAS_RRDS/node_deployment{$data['nodedeploymentid']}.rrd";
                        	}
		                else
                	        {
                	                $path = "$NAS_RRDS/{$data['calledstationid_sanitized']}.rrd";
                        	}
		                $start_date = time();
                	        $cmd = "/usr/bin/rrdtool update $path {$start_date}:{$results[0]['acctinputoctets_total']}:{$results[0]['acctoutputoctets_total']}";
				echo "$cmd\n";
		                system($cmd);
		        }
		}
		exit();
	}

	public function action_update_stas()
	{
		$STA_RRDS = Kohana::$config->load('system.default.rrd.client_path');

		# Get the list of users
		$query = Doctrine::em()->createQuery("SELECT DISTINCT ra.callingstationid FROM Model_Radacct ra");
                $results = $query->getResult();
//              	\Doctrine\Common\Util\Debug::dump($results);
  //            	exit();

		# Check each user has an rrd file - create for new users
		foreach ($results as $row)   
		{
        		$callingstation = str_replace(":", "_", $row['callingstationid']);
			$path = "{$STA_RRDS}/{$callingstation}.rrd";
		        if(!file_exists($path))
		        {
                		$start_date = time() - 30;
		                $cmd="/usr/bin/rrdtool create {$path}".
   		             	        " --start {$start_date} ".
	                	        " DS:ds0:COUNTER:600:0:1250000".
                        		" DS:ds1:COUNTER:600:0:1250000".
        		                " RRA:AVERAGE:0.5:1:800".
                        		" RRA:AVERAGE:0.5:6:800".
		                        " RRA:AVERAGE:0.5:24:800".
                		        " RRA:AVERAGE:0.5:288:800".
		                        " RRA:MAX:0.5:1:800".
                		        " RRA:MAX:0.5:6:800".
		                        " RRA:MAX:0.5:24:800".
                		        " RRA:MAX:0.5:288:800";
			        system($cmd);
		        }
		}

		# Get the current bandwidth counters from the database
		$query = $query = Doctrine::em()->createQuery("SELECT ra.callingstationid, SUM(ra.acctinputoctets) as acctinputoctets_total, SUM(ra.acctoutputoctets) as acctoutputoctets_total FROM Model_Radacct ra GROUP BY ra.callingstationid");
		$results = $query->getResult();
		# Update each RRD
		foreach ($results as $row)
                {
        		$callingstation = str_replace(":", "_", $row['callingstationid']);
		        $start_date = time();
		        $cmd="/usr/bin/rrdtool update {$STA_RRDS}/{$callingstation}.rrd".
  	                      " {$start_date}:{$row['acctinputoctets_total']}:{$row['acctoutputoctets_total']}";
        		system($cmd);
		}
		exit();
	}

}	
