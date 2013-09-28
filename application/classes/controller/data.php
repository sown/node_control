<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Data extends Controller
{
	public function action_default()
	{
		echo "<p>The following data calls are available:</p><ul>";
		echo "<li><a href=\"data/current_radius_users\">current_radius_users</a></li>";
		echo "<li><a href=\"data/radius_users_day/2013/09/28\">radius_users_day (2013/09/28)</a></li>";
		echo "<li><a href=\"data/radius_users_day/2013/09\">radius_users_days_month (2013/09)</a></li>";
		echo "<li><a href=\"data/radius_users_day/2013\">radius_users_days_year (2013)</a></li>";
		echo "<li><a href=\"data/radius_users_month/2013/09\">radius_users_month (2013/09)</a></li>";
		echo "<li><a href=\"data/radius_users_month/2013\">radius_users_months_year (2013)</a></li>";
		echo "<li><a href=\"data/radius_users_year/2013\">radius_users_year (2013)</a></li>";
		echo "<li><a href=\"data/radius_users_years\">radius_users_years</a></li>";
		echo "<li><a href=\"data/radius_users_hours_week\">radius_users_hours_week</a></li>";
		echo "<li><a href=\"data/radius_users_hours_month\">radius_users_hours_month</a></li>";
		echo "<li><a href=\"data/radius_users_hours_year\">radius_users_hours_year</a></li>";
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
		$this->_print_radius_users($results);
	}

	public function action_radius_users_days_month()
        {
                $formatted_date = $this->request->param('year') . "-" . $this->request->param('month');
                if (!preg_match("/^\d{4}-\d{2}$/", $formatted_date))
                {
                        die("Invalid month");
                }
		$results = $this->_get_radius_user_results('%Y-%m-%d', $formatted_date, '%Y-%m');
                $this->_print_radius_users($results);
        }

	public function action_radius_users_days_year()
        {
                $formatted_date = $this->request->param('year');
                if (!preg_match("/^\d{4}$/", $formatted_date))
                {
                        die("Invalid year");
                }
                $results = $this->_get_radius_user_results('%Y-%m-%d', $formatted_date, '%Y');
                $this->_print_radius_users($results);
        }

	public function action_radius_users_month()
        {
                $formatted_date = $this->request->param('year') . "-" . $this->request->param('month');
                if (!preg_match("/^\d{4}-\d{2}$/", $formatted_date))
                {
                        die("Invalid month");
                }
                $results = $this->_get_radius_user_results('%Y-%m', $formatted_date, '%Y-%m');
                $this->_print_radius_users($results);
        }

	public function action_radius_users_months_year()
        {
                $formatted_date = $this->request->param('year');
                if (!preg_match("/^\d{4}$/", $formatted_date))
                {
                        die("Invalid year");
                }
                $results = $this->_get_radius_user_results('%Y-%m', $formatted_date, '%Y');
                $this->_print_radius_users($results);
        }

	public function action_radius_users_year()
        {
                $formatted_date = $this->request->param('year');
                if (!preg_match("/^\d{4}$/", $formatted_date))
                {
                        die("Invalid year");
                }
                $results = $this->_get_radius_user_results('%Y', $formatted_date, '%Y');
                $this->_print_radius_users($results);
        }

	public function action_radius_users_years()
        {
                $results = $this->_get_radius_user_results('%Y');
                $this->_print_radius_users($results);
        }

	public function action_radius_users_hours_week()
	{
		$results = $this->_get_radius_user_hour_results(7);
		$this->_print_radius_users_hours($results);
	}

	public function action_radius_users_hours_month()
        {
                $results = $this->_get_radius_user_hour_results(30);
                $this->_print_radius_users_hours($results);
        }

	public function action_radius_users_hours_year()
        {
                $results = $this->_get_radius_user_hour_results(365);
                $this->_print_radius_users_hours($results);
        }

	private function _get_radius_user_hour_results($days)
	{
		$seconds = $days * 86400;
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '%h') AS thehour, SUM(1) AS connections, ra.username")
                        ->where("UNIX_TIMESTAMP(ra.acctstarttime) > UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) - $seconds")
                	->groupBy("thehour, ra.username")
                        ->orderBy("thehour");
                $results = $qb->getQuery()->getResult();
                return $results;

	}
	private function _print_radius_users_hours($results)
        {
                $users = 0;
                $connections = 0;
                $lasthour = '';
                foreach ($results as $result)
                {
                        if (!empty($lasthour) && $result['thehour'] != $lasthour)
                        {
                                print serialize(array('thehour' => $lasthour, 'no_users' => $users, 'no_connections' => $connections)) . "\n";
                                $users = 0;
                                $connections = 0;
                        }
                        $users++;
                        $connections += $result['connections'];
                        $lasthour = $result['thehour'];
                }
                if ($users > 0) {
                         print serialize(array('thehour' => $lasthour, 'no_users' => $users, 'no_connections' => $connections));
                }
        }

	private function _get_radius_user_results($select_date_format, $formatted_date = '', $where_date_format = '') 
	{
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("DATE_FORMAT(ra.acctstarttime, '$select_date_format') AS thedate, SUM(1) AS connections, ra.username");
		if (!empty($formatted_date)) 
		{
                        $qb->where("DATE_FORMAT(ra.acctstarttime, '$where_date_format') = '$formatted_date'");
		}
                $qb->groupBy("thedate, ra.username")
                	->orderBy("thedate");
                $results = $qb->getQuery()->getResult();
		return $results;
	}

	private function _print_radius_users($results)
	{
		$users = 0;
                $connections = 0;
                $lastdate = '';
                foreach ($results as $result)
                {
                        if (!empty($lastdate) && $result['thedate'] != $lastdate)
                        {
                                print serialize(array('thedate' => $lastdate, 'no_users' => $users, 'no_connections' => $connections)) . "\n";
                                $users = 0;
                                $connections = 0;
                        }
                        $users++;
                        $connections += $result['connections'];
                        $lastdate = $result['thedate'];
                }
                if ($users > 0) {
                         print serialize(array('thedate' => $lastdate, 'no_users' => $users, 'no_connections' => $connections));
                }
	}

}

