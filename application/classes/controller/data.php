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
		$qb->where("ra.acctstoptime IS NULL");
		$qb->andWhere("ra.acctinputoctets > 0 OR ra.acctoutputoctets > 0");
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
                        ->select("DATE_FORMAT(ra.acctstarttime, '%Y-%m%-%d') AS thedate, SUM(1) AS connections, ra.username")
                        ->where("DATE_FORMAT(ra.acctstarttime, '%Y-%m%-%d') = '$firstdateformat'")
			->orWhere("ra.acctstarttime > :firstdate")
                	->groupBy("thedate, ra.username")
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
                        ->select("DATE_FORMAT(ra.acctstarttime, '%Y-%m') AS orderdate, DATE_FORMAT(ra.acctstarttime, '%b %Y') AS thedate, SUM(1) AS connections, ra.username")
                        ->where("DATE_FORMAT(ra.acctstarttime, '%Y-%m') = '$firstdateformat' OR ra.acctstarttime > :firstdate")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                        ->andWhere("ra.acctsessiontime > 0")
                        ->groupBy("thedate, ra.username")
                        ->orderBy("orderdate")
                        ->setParameter("firstdate", $firstdate);
                $results = $qb->getQuery()->getResult();
                $array = $this->_format_radius_users($results);
                foreach ($array as $record)
                {
                        $xdata[] = $record['thedate'];
                        $ydata[] = $record['no_'.$type.'s'];
                }
                SOWN::draw_bar_graph('No. of SOWN '.ucfirst($type).'s - By Month', '', '', $xdata, $ydata, 600, 400, array(45,20,30,90), 60);
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
                        $ydata[2][] = $yearcount - $monthcount - $weekcount;
		}
		$legend = array("Last 7 Days", "Last 30 Days", "Last 365 Days");
		SOWN::draw_accbar_graph('No. of SOWN '.ucfirst($type).'s - By Hour', '', '', $xdata, $ydata, $legend, 600, 400, array(45,20,30,60), 0);
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
                        $ydata[2][] = $node['no_'.$type.'s'] - $monthcount - $weekcount;
                }
		$legend = array("Last 7 Days", "Last 30 Days", "Last 365 Days");
                SOWN::draw_accbar_graph('No. of SOWN '.ucfirst($type).'s - By Node', '', '', $xdata, $ydata, $legend, 600, 400, array(60,25,40,130), 90, "horizontal");
        }

	private function _get_radius_user_hour_results($days)
	{
		$seconds = $days * 86400;
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '%k') AS thehour, SUM(1) AS connections, ra.username")
                        ->where("UNIX_TIMESTAMP(ra.acctstarttime) > UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - $seconds")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                        ->andWhere("ra.acctsessiontime > 0")
                	->groupBy("thehour, ra.username")
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
                        ->select("ra.calledstationid AS thenode, SUM(1) AS connections, ra.username")
                        ->where("UNIX_TIMESTAMP(ra.acctstarttime) > UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - $seconds")
			->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
			->andWhere("ra.acctsessiontime > 0")
                        ->groupBy("thenode, ra.username")
                        ->orderBy("thenode");
                $results = $qb->getQuery()->getResult();
                return $results;
	}

	private function _format_radius_users_nodes($results)
        {
                $users = 0;
                $connections = 0;
                $lastnode = '';
                $array = array();
                foreach ($results as $result)
                {
                        if (!empty($lastnode) && $result['thenode'] != $lastnode)
                        {
				$lnbits = explode(":", $lastnode);
				$lnmac = str_replace("-", ":", $lnbits[0]);
				$node = Model_Node::getByMac($lnmac);	
				$nodename = $lnmac;
				if (!empty($node))
				{
					$nodename =  $node->boxNumber;
				}
                                $array[$nodename] = array('thenode' => $nodename, 'no_users' => $users, 'no_connections' => $connections);
                                $users = 0;
                                $connections = 0;
                        }
                        $users++;
                        $connections += $result['connections'];
                        $lastnode = $result['thenode'];
                }
                if ($users > 0) {
                         $array[] = array('thenode' => $lastnode, 'no_users' => $users, 'no_connections' => $connections);
                }
                return $array;
        }

	private function _get_radius_user_results($select_date_format, $formatted_date = '', $where_date_format = '') 
	{
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '$select_date_format') AS thedate, SUM(1) AS connections, ra.username")
			->where("1=1");
		if (!empty($formatted_date)) 
		{
                        $qb->andWhere("DATE_FORMAT(ra.acctstarttime, '$where_date_format') = '$formatted_date'");
		}
		$qb->andWhere("ra.acctinputoctets+ra.acctoutputoctets > 0")
                	->andWhere("ra.acctsessiontime > 0")
                	->groupBy("thedate, ra.username")
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
                if ($users > 0) 
		{
                         $array[] = array('thedate' => $lastdate, 'no_users' => $users, 'no_connections' => $connections);
                }
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

