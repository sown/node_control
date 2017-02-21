<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Data extends Controller
{
	public function action_default()
	{
		echo "<p>The following data calls are available:</p><ul>";
		echo "<li><a href=\"data/current_radius_users\">current_radius_users</a></li>";
		echo "<li><a href=\"data/radius_users_day/2013/09/28\">radius_users_day (2013/09/28)</a></li>";
		echo "</ul>";
		echo "<p>The following graph calls are available:</p><ul>";
		echo "<li><a href=\"data/graph/user/day/now\">Concurrent Users in last 24 hours</a></li>";
                echo "<li><a href=\"data/graph/connection/day/now\">Concurrent Connections in last 24 hours</a></li>";
		echo "<li><a href=\"data/graph/user/day\">Users per day</a></li>";
		echo "<li><a href=\"data/graph/connection/day\">Connections per day</a></li>";
		echo "<li><a href=\"data/graph/user/month\">Users per month</a></li>";
                echo "<li><a href=\"data/graph/connection/month\">Connections per month</a></li>";
		echo "<li><a href=\"data/graph/user/hour\">Users per hour</a></li>";
                echo "<li><a href=\"data/graph/connection/hour\">Connections per hour</a></li>";
		echo "<li><a href=\"data/graph/user/node\">Users per node</a></li>";
                echo "<li><a href=\"data/graph/connection/node\">Connections per node</a></li>";
		echo "</ul>";
	}

	public function action_current_radius_users()
	{
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra');
		$qb->select("DISTINCT ra.callingstationid AS mac");
		$qb->where("ra.acctstoptime IS NULL");
		// 600 seconds because records only get updated every 300 seconds so session time may have been increased since 
		// record was updated. 600 seconds gives enough leeway without including users who have likely disconnected.
		$qb->andWhere("UNIX_TIMESTAMP(ra.acctstarttime) + ra.acctsessiontime + 600 > UNIX_TIMESTAMP(CURRENT_TIMESTAMP())");
		$query = $qb->getQuery();
                $curusers = $query->getResult();
		$activeusers = sizeof($curusers);
                if ($activeusers < 1) 
			echo '0';
		else 
			echo $activeusers;
	}

	public function action_current_radius_node_users()
        {
                $qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra');
                $qb->select("DISTINCT ra.callingstationid AS user_mac, ra.calledstationid AS node_mac");
                $qb->where("ra.acctstoptime IS NULL");
                // 600 seconds because records only get updated every 300 seconds so session time may have been increased since 
                // record was updated. 600 seconds gives enough leeway without including users who have likely disconnected.
                $qb->andWhere("UNIX_TIMESTAMP(ra.acctstarttime) + ra.acctsessiontime + 600 > UNIX_TIMESTAMP(CURRENT_TIMESTAMP())");
                $query = $qb->getQuery();
                $curusers = $query->getResult();
		$nodeusers = array();
		$nodeusersnode = array();
		foreach ($curusers as $curuser)
		{
			$node_mac = trim(strtoupper(str_replace("-", ":", substr($curuser['node_mac'], 0, strpos($curuser['node_mac'], ":")))));
			if (!isset($nodeusers[$node_mac]))
			{
				$nodeusers[$node_mac] = 0;
			}
			$nodeusers[$node_mac]++;
		}
		$netadapters = Doctrine::em()->getRepository('Model_NetworkAdapter')->findAll();
		foreach ($netadapters as $na)
		{
			$namac = strtoupper($na->mac);
			if (isset($nodeusers[$namac]))
			{
				$nodeusersnode[$na->node->boxNumber] = $nodeusers[$namac];
			}
		}
		echo json_encode($nodeusersnode);
        }


	public function action_radius_users_day() 
	{
		$formatted_date = $this->request->param('year') . "-" . $this->request->param('month') . "-" . $this->request->param('day');
		if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $formatted_date))
		{
			die("Invalid date");
		}
		$results = $this->_get_radius_user_results('%Y-%m-%d', $formatted_date, '%Y-%m-%d');	
		$array = $this->_format_radius_users($results);
		$this->_print_serialized($array);
	}

	public function action_day_graph()
	{
		$type = $this->request->param('type');
		if (!in_array($type, array('user', 'connection')))
		{
			throw new HTTP_Exception_404();
		}
		$response = $this->request->response();
		$response->headers('Content-Type', 'image/png');
		$firstdate = new \DateTime("-1 month");
		$firstdateformat = $firstdate->format('Y-m-d');
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '%Y-%m%-%d') AS thedate, SUM(1) AS connections, ra.callingstationid")
                        ->where("DATE_FORMAT(ra.acctstarttime, '%Y-%m%-%d') = '$firstdateformat' OR ra.acctstarttime > :firstdate")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                	->groupBy("thedate, ra.callingstationid")
                        ->orderBy("thedate")
			->setParameter("firstdate", $firstdate);
                $results = $qb->getQuery()->getResult();
                $array = $this->_format_radius_users($results);
		foreach ($array as $record)
		{
			$xdata[] = $record['thedate'];
			$ydata[] = $record['no_'.$type.'s'];
		}
			
		SOWN::draw_bar_graph('No. of SOWN '.ucfirst($type).'s - By Day', '', '', $xdata, $ydata, 600, 400, array(45,20,30,90), 60);		
	}

	public function action_month_graph()
        {
                $type = $this->request->param('type');
                if (!in_array($type, array('user', 'connection')))
                {
                        throw new HTTP_Exception_404();
                }
                $response = $this->request->response();
                $response->headers('Content-Type', 'image/png');
                $firstdate = new \DateTime("-12 months");
                $firstdateformat = $firstdate->format('Y-m');
                $qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '%Y-%m') AS orderdate, DATE_FORMAT(ra.acctstarttime, '%b %Y') AS thedate, SUM(1) AS connections, ra.callingstationid")
                        ->where("DATE_FORMAT(ra.acctstarttime, '%Y-%m') = '$firstdateformat' OR ra.acctstarttime > :firstdate")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                        ->groupBy("thedate, ra.callingstationid")
                        ->orderBy("orderdate")
                        ->setParameter("firstdate", $firstdate);
                $results = $qb->getQuery()->getResult();
                $array = $this->_format_radius_users($results);
                foreach ($array as $record)
                {
                        $xdata[] = $record['thedate'];
                        $ydata[] = $record['no_'.$type.'s'];
                }
                SOWN::draw_bar_graph('No. of SOWN '.ucfirst($type).'s - By Month', '', '', $xdata, $ydata, 600, 400, array(55,20,30,85), 60);
        }

	public function action_hour_graph()
	{
		$type = $this->request->param('type');
                if (!in_array($type, array('user', 'connection')))
                {
                        throw new HTTP_Exception_404();
                }
                $response = $this->request->response();
		$response->headers('Content-Type', 'image/png');
		$week = $this->_format_radius_users_hours($this->_get_radius_user_hour_results(7));
		$month = $this->_format_radius_users_hours($this->_get_radius_user_hour_results(30));
		$year = $this->_format_radius_users_hours($this->_get_radius_user_hour_results(365));
		for ($h=0; $h < 24; $h++) 
		{
			$xdata[] = $h;
			$weekcount = 0;
                        $monthcount = 0;
			$yearcount = 0;
                        if (isset($week[$h])) $weekcount = $week[$h]['no_'.$type.'s'];
                        if (isset($month[$h])) $monthcount = $month[$h]['no_'.$type.'s'];
			if (isset($year[$h])) $yearcount = $year[$h]['no_'.$type.'s'];
                        $ydata[0][] = $weekcount;
                        $ydata[1][] = $monthcount - $weekcount;
                        $ydata[2][] = $yearcount - $monthcount;
		}
		$legend = array("Last 7 Days", "Last 30 Days", "Last 365 Days");
		SOWN::draw_accbar_graph('No. of SOWN '.ucfirst($type).'s - By Hour', '', '', $xdata, $ydata, $legend, 600, 400, array(45,20,30,60), 0, 'vertical', 90);
	}

	public function action_node_graph()
        {
                $type = $this->request->param('type');
                if (!in_array($type, array('user', 'connection')))
                {
                        throw new HTTP_Exception_404();
                }
                $response = $this->request->response();
		$response->headers('Content-Type', 'image/png');
                $week = $this->_format_radius_users_nodes($this->_get_radius_user_node_results(7));
                $month = $this->_format_radius_users_nodes($this->_get_radius_user_node_results(30));
                $year = $this->_format_radius_users_nodes($this->_get_radius_user_node_results(365));
		foreach ($year as $n => $node)
                {
                        $xdata[] = $n;
			$weekcount = 0;
			$monthcount = 0;
			if (isset($week[$n])) $weekcount = $week[$n]['no_'.$type.'s'];
			if (isset($month[$n])) $monthcount = $month[$n]['no_'.$type.'s'];
			$ydata[0][] = $weekcount;
                        $ydata[1][] = $monthcount - $weekcount;
                        $ydata[2][] = $node['no_'.$type.'s'] - $monthcount;
                }
		$legend = array("Last 7 Days", "Last 30 Days", "Last 365 Days");
                SOWN::draw_accbar_graph('No. of SOWN '.ucfirst($type).'s - By Node', '', '', $xdata, $ydata, $legend, 600, 500, array(75,25,40,50), 90, "horizontal", 60);
        }
	
	public function action_deployment_graph()
        {
		$type = $this->request->param('type');
                if (!in_array($type, array('user', 'connection')))
                {
                        throw new HTTP_Exception_404();
                }
		$response = $this->request->response();
                $response->headers('Content-Type', 'image/png');
		$week = array();
		$month = array();
		$day = array();
		$now = time();
		$yearago = date('Y-m-d H:i:s', strtotime("-1 year"));
		$deployments = Model_Deployment::getAllDeploymentsDuring($yearago);
		foreach ($deployments as $d)
		{
			$nds = $d->nodeDeployments;
			$calledstationids = array();
			foreach ($nds[0]->node->interfaces as $interface)
			{
				$calledstationids[strtoupper(str_replace(":", "-", $interface->networkAdapter->mac))] =2;
			}
			$calledstationids = array_keys($calledstationids);
			$start_seconds = $d->startDate->format('U');
			$end_seconds = $d->endDate->format('U');
		
		#	echo time(). " | ".$nds[0]->node->boxNumber.":<br/>\n";
                	$dweek = $this->_get_radius_user_deployment_results($start_seconds, $end_seconds, $now, $calledstationids, 7, $type);
		#	echo "week: $dweek;<br/>\n" ;
                	$dmonth = $this->_get_radius_user_deployment_results($start_seconds, $end_seconds, $now, $calledstationids, 30, $type);
		#	echo "month: $dmonth;<br/>\n";
                	$dyear = $this->_get_radius_user_deployment_results($start_seconds, $end_seconds, $now, $calledstationids, 365, $type);
		#	echo "year: $dyear;<br/>\n";
			$xdata[] = $d->name . " (#".$nds[0]->node->boxNumber.")";
                        $ydata[0][] = $dweek;
                        $ydata[1][] = $dmonth - $dweek;
                        $ydata[2][] = $dyear - $dmonth;
                }
                $legend = array("Last 7 Days", "Last 30 Days", "Last 365 Days");
		SOWN::draw_accbar_graph('No. of SOWN '.ucfirst($type).'s - By Deployment', '', '', $xdata, $ydata, $legend, 600, 500, array(75,25,40,210), 90, "horizontal", 45);

        }
	

	public function action_through_day_graph()
	{
		$type = $this->request->param('type');
		$date = $this->request->param('date');
		$interval = 300;
                if (!in_array($type, array('user', 'connection')) || (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date) && $date != "now"))
                {
                        throw new HTTP_Exception_404();
                }
		if ($date == "now") 
		{
                        $nowsecs = strtotime($date);
                        $nowsecs = floor($nowsecs / $interval) * $interval - 86400;
                        $date = date("H:i:s Y-m-d", $nowsecs);
			$date_title = "Last 24 Hours";
                }
		else 
		{
			$date_title = 'Throughout '.$date;
		}
                $response = $this->request->response();
                $response->headers('Content-Type', 'image/png');
		$through_day = $this->_format_radius_users_through_day($this->_get_radius_users_through_day_results($date), $date, $interval);
		$xdata = $through_day['thetime'];
                $ydata = $through_day['no_'.$type.'s'];
		SOWN::draw_line_graph('No. of SOWN '.ucfirst($type).'s - '.$date_title, '', '', $xdata, $ydata, 600, 400, array(45,20,30,90), 0, 12);
	}


	private function _get_radius_user_hour_results($days)
	{
		$seconds = $days * 86400;
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '%k') AS thehour, SUM(1) AS connections, ra.callingstationid")
                        ->where("UNIX_TIMESTAMP(ra.acctstarttime) > UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - $seconds")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                	->groupBy("thehour, ra.callingstationid")
                        ->orderBy("thehour");
                $results = $qb->getQuery()->getResult();
                return $results;

	}

	private function _format_radius_users_hours($results)
        {
                $users = 0;
                $connections = 0;
                $lasthour = -1;
		$array = array();
                foreach ($results as $result)
                {
                        if ($lasthour != -1 && $result['thehour'] != $lasthour)
                        {
                                $array[$lasthour] = array('thehour' => $lasthour, 'no_users' => $users, 'no_connections' => $connections);
                                $users = 0;
                                $connections = 0;
                        }
                        $users++;
                        $connections += $result['connections'];
                        $lasthour = $result['thehour'];
                }
                if ($users > 0) {
                         $array[$lasthour] = array('thehour' => $lasthour, 'no_users' => $users, 'no_connections' => $connections);
                }
		return $array;
        }

	private function _get_radius_user_node_results($days)
	{
		$seconds = $days * 86400;
                $qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("ra.calledstationid AS thenode, SUM(1) AS connections, ra.callingstationid")
                        ->where("UNIX_TIMESTAMP(ra.acctstarttime) > UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - $seconds")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                        ->groupBy("thenode, ra.callingstationid")
                        ->orderBy("thenode");
                $results = $qb->getQuery()->getResult();
                return $results;
	}

	private function _get_radius_user_deployment_results($start_seconds, $end_seconds, $now, $calledstationids, $days, $type = "user")
	{
		if ($start_seconds < $now - $days * 86400)
		{
			$start_seconds = $now - $days * 86400;
		}
		if ($start_seconds > $end_seconds)	
		{
			return 0;
		}
		$calledstationids_str = implode(' ', $calledstationids);
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("ra.calledstationid AS thenode, SUM(1) AS connections, ra.callingstationid")
                        ->where("UNIX_TIMESTAMP(ra.acctstarttime) >= $start_seconds");
		if ($end_seconds < $now)
		{
			$qb->andWhere("UNIX_TIMESTAMP(ra.acctstarttime) < $end_seconds");
		}
		$qb->andWhere("ra.calledstationid LIKE '$calledstationids_str%'")
                        ->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                        ->groupBy("ra.callingstationid");
		$sql = $qb->getQuery()->getSQL();
		#echo time()." | $sql<br/>\n";
                $results = $qb->getQuery()->getResult();
		if ($type == "user")
		{
			return sizeof($results);
		}
		$connections = 0;
                foreach ($results as $result)
                {
                        $connections += $result['connections'];
                }
                return $connections;
	}

	private function _get_radius_users_through_day_results($date)
	{
		$daybefore = strtotime($date) - 86400;
		$dayafter = strtotime($date) + 86400;
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
			->select("UNIX_TIMESTAMP(ra.acctstarttime) AS start, UNIX_TIMESTAMP(ra.acctstoptime) AS stop, ra.callingstationid AS mac")
			->where("ra.acctinputoctets+ra.acctoutputoctets > 0 OR (ra.acctsessiontime <= 600 AND ra.acctsessiontime IS NULL)")
			->andwhere("UNIX_TIMESTAMP(ra.acctstarttime) >= $daybefore")
			->andwhere("UNIX_TIMESTAMP(ra.acctstarttime) <= $dayafter");
		$results = $qb->getQuery()->getResult();
		return $results;
	}		

	private function _format_radius_users_through_day($results, $date, $interval)
	{
		$day_start_secs = strtotime($date);
		$day_end_secs = $day_start_secs + 86400;
		$users = array();
		$connections = array();
		$skip = 0;
		for ( $secs = $day_start_secs; $secs <= $day_end_secs; $secs += $interval ) 
		{
        		if ($secs > strtotime("now"))
	        	{
        	        	$skip = 1;
	        	}
	        	$time = date("H:i:s", $secs);
			if (isset($users[$time])) 
			{
				$time = " $time";
			}
			$users[$time] = 0;
		        $connections[$time] = 0;
			$found_users = array();
        		if (!$skip) 
		        {
        		        foreach ( $results as $ts )
                		{
                        		if ( $ts['start'] < $secs && (empty($ts['stop']) || $ts['stop'] > $secs))
                        		{
						if (!in_array($ts['mac'], $found_users))
						{
							$users[$time]++;
							$found_users[] = $ts['mac'];
						}
                                		$connections[$time]++; 
	                        	}	
        	        	}
        		}
		}
		return array("thetime" => array_keys($connections), "no_users" => array_values($users), "no_connections" => array_values($connections));
	}	
	
	private function _format_radius_users_nodes($results)
        {
                $users = 0;
                $connections = 0;
                $calledstationid = '';
                $array = array();
                foreach ($results as $result)
                {
                        if (!empty($calledstationid) && $result['thenode'] != $calledstationid)
                        {
				$array = $this->_set_update_node_users_connections($array, $calledstationid, $users, $connections);
                                $users = 0;
                                $connections = 0;
                        }
                        $users++;
                        $connections += $result['connections'];
                        $calledstationid = $result['thenode'];
                }
                if ($users > 0) 
		{
			$array = $this->_set_update_node_users_connections($array, $calledstationid, $users, $connections);
                }
		ksort($array);
                return $array;
        }

	private function _set_update_node_users_connections($array, $calledstationid, $users, $connections)
	{
		$csibits = explode(":", $calledstationid);
                $csimac = str_replace("-", ":", $csibits[0]);
                $node = Model_Node::getByMac($csimac);
		if (empty($node))
                {
			return $array;
		}
		$index = $node->boxNumber;
		if (isset($array[$index]))
                {
                        $array[$index]['no_users'] += $users;
                        $array[$index]['no_users'] += $connections;
                }
                else
                {
                        $array[$index] = array('thenode' => $index, 'no_users' => $users, 'no_connections' => $connections);
        	}
		return $array;
	}

	private function _get_radius_user_results($select_date_format, $formatted_date = '', $where_date_format = '') 
	{
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '$select_date_format') AS thedate, SUM(1) AS connections, ra.callingstationid")
			->where("1=1");
		if (!empty($formatted_date)) 
		{
                        $qb->andWhere("DATE_FORMAT(ra.acctstarttime, '$where_date_format') = '$formatted_date'");
		}
		$qb->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                	->groupBy("thedate, ra.callingstationid")
                	->orderBy("thedate");
                $results = $qb->getQuery()->getResult();
		return $results;
	}
	
	private function _format_radius_users($results)
	{
		$users = 0;
                $connections = 0;
                $lastdate = '';
                foreach ($results as $result)
                {
                        if (!empty($lastdate) && $result['thedate'] != $lastdate)
                        {
                                $array[] = array('thedate' => $lastdate, 'no_users' => $users, 'no_connections' => $connections);
                                $users = 0;
                                $connections = 0;
                        }
                        $users++;
                        $connections += $result['connections'];
                        $lastdate = $result['thedate'];
                }

		# morse 2013/12/15: declare $array outside the scope block,
		# so that if there are 0 users (which happens at midnight)
		# then we don't get undefined variable warnings from this code.
		# also - who called a variable 'array'?
                $array[] = array('thedate' => $lastdate, 'no_users' => $users, 'no_connections' => $connections);

		return $array;
	}

	private function _print_serialized($array)
	{
		if (sizeof($array) == 1)
                {
                        print serialize($array[0]);
                }
		else
		{
			print serialize($array);
		}
	}

}

