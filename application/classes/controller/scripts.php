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
		                system($cmd);
		        }
		}
		Doctrine::em()->getConnection()->close();
		exit();
	}

	public function action_update_stas()
	{
		$STA_RRDS = Kohana::$config->load('system.default.rrd.client_path');

		# Get the list of users
		$query = Doctrine::em()->createQuery("SELECT DISTINCT ra.callingstationid FROM Model_Radacct ra");
                $results = $query->getResult();

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
		$whererecent = "";
		$all_stas = $this->request->param('all');
		if (empty($all_stas))
		{
			$hourago_dt = new \DateTime('-1 hour');
                	$hourago = $hourago_dt->format("Y-m-d H:i:s");
			$cs_query_str = "SELECT ra.callingstationid FROM Model_Radacct ra WHERE ra.acctstoptime IS NULL OR ra.acctstoptime > '$hourago'";
			$cs_query = Doctrine::em()->createQuery($cs_query_str);
			$cs_results = $cs_query->getResult();
			if (sizeof($cs_results) == 0)
                        {
                                exit();
                        }
			$callingstationids = array();
			foreach ($cs_results as $cs_row)
                	{
				$callingstationids[] = $cs_row['callingstationid'];
			}
			$whererecent = "WHERE ra.callingstationid IN ('".implode("', '", $callingstationids)."')";
		}
		$query_str = "SELECT ra.callingstationid, SUM(ra.acctinputoctets) as acctinputoctets_total, SUM(ra.acctoutputoctets) as acctoutputoctets_total FROM Model_Radacct ra $whererecent GROUP BY ra.callingstationid";
		$query = Doctrine::em()->createQuery($query_str);
		$results = $query->getResult();
		
		# Update each RRD
		foreach ($results as $row)
                {
			if (!empty($row['callingstationid'])) 
			{
        			$callingstation = str_replace(":", "_", $row['callingstationid']);
		        	$start_date = time();
		        	$cmd="/usr/bin/rrdtool update {$STA_RRDS}/{$callingstation}.rrd".
  	                      		" {$start_date}:{$row['acctinputoctets_total']}:{$row['acctoutputoctets_total']}";
        			system($cmd);
			}
		}
		Doctrine::em()->getConnection()->close();
		exit();
	}

	public function action_update_dns_zones() 
	{
		$tmpdir = $this->request->param('tmpdir');
      		if (empty($tmpdir)) $tmpdir = '/tmp';
		$tmpdir = str_replace("+", "/", $tmpdir);

                $nameservers = Doctrine::em()->createQuery("SELECT si.IPv4Addr, si.IPv6Addr, si.hostname, sic.cname FROM Model_ServerInterface si JOIN si.cnames sic WHERE si.hostname LIKE 'ns%' OR sic.cname LIKE 'ns%' ORDER BY sic.cname")->getResult();
		$wwwserver = Doctrine::em()->createQuery("SELECT si.IPv4Addr, si.IPv6Addr, si.hostname, sic.cname FROM Model_ServerInterface si JOIN si.cnames sic WHERE si.hostname LIKE 'www' OR sic.cname LIKE 'www'")->setMaxResults(1)->getResult();
		$server_interfaces = Doctrine::em()->createQuery("SELECT si.IPv4Addr, si.IPv6Addr, si.hostname FROM Model_ServerInterface si JOIN si.vlan v JOIN si.server s WHERE v.name = '".Kohana::$config->load('system.default.vlan.local')."' AND s.retired != 1 AND (si.IPv4Addr != '' OR si.IPv6Addr != '') ORDER BY si.IPv4Addr ASC")->getResult(); 
		$servers = Doctrine::em()->getRepository('Model_Server')->findByRetired(0);
		$other_hosts = Doctrine::em()->getRepository('Model_OtherHost')->findByRetired(0);
		DNSUtils::generateHostsReverseFragment($tmpdir, $nameservers, $server_interfaces, $other_hosts);
                DNSUtils::generateHostsForwardFragment($tmpdir, $nameservers, $servers, $other_hosts, $wwwserver[0]);

      		$nodes_query = Doctrine::em()->createQuery("SELECT n.id, n.boxNumber, i2.IPv4Addr AS DNSIPv4Addr, ve.IPv4Addr AS VPNIPv4Addr, ve.IPv6Addr, d.latitude, d.longitude, na.mac, d.type, n.firmwareImage FROM Model_Node n LEFT JOIN n.vpnEndpoint ve JOIN n.interfaces i JOIN i.networkAdapter na LEFT JOIN n.nodeDeployments nd LEFT JOIN nd.deployment d LEFT JOIN n.dnsInterface i2 WHERE (nd.endDate > CURRENT_TIMESTAMP() OR nd.endDate IS NULL) AND i.name = 'eth0' ORDER BY n.boxNumber ASC");
		$nodes = $nodes_query->getResult();
      		DNSUtils::generateNodesReverseFragment($tmpdir, $nodes);
      		DNSUtils::generateNodesForwardFragment($tmpdir, $nodes);

      		DNSUtils::generateZoneHeader($tmpdir);
      		DNSUtils::generateReverseZoneIPv4Header($tmpdir);
      		DNSUtils::generateReverseZoneIPv6Header($tmpdir);
		Doctrine::em()->getConnection()->close();
		exit();
	}

}	
