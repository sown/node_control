<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Deployments_Usage extends Controller_AbstractAdmin
{
	private function _initialize_graph()
	{
		$response = $this->request->response();

                $deployment = Doctrine::em()->getRepository('Model_Deployment')->find($this->request->param('deployment_id'));
		if (!is_object($deployment))
		{
			throw new HTTP_Exception_404('There is no deployment with this ID.');
		}

                if (!Auth::instance()->logged_in('systemadmin'))
                {
			if (!in_array($_SERVER['REMOTE_ADDR'], Kohana::$config->load('system.default.admin_system.valid_query_ips')))
 	               	{
				$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
                        	if (!is_object($user) || !$deployment->hasCurrentDeploymentAdmin($user->id))
                        	{
                                	throw new HTTP_Exception_403('You do not have permission to access this page.');
                        	}
			}
                }

		// Changed /usr/share/php/kohana3.2/system/classes/kohana/http/header.php replaceing Text:: with Kohana_Text::
		$response->headers('Content-Type', 'image/png');
		return $deployment;
	}
	
	public function action_monthly_graph()
	{
		$deployment = $this->_initialize_graph();
		
		if(!is_object($deployment))
			return;
		
		$allmonths = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"); 
		$months = array();
		foreach (array_slice($allmonths, date("n")) as $month)
		{
			$lastyear = date('y')-1;
			$months[] = $month . " ". $lastyear;
		}
		foreach (array_slice($allmonths, 0, date("n")) as $month)
                        $months[] = $month . " ". date('y');

		$nodeDeploymentsUsage = array();
		$path = Kohana::$config->load('system.default.rrd.deployment_path');
		foreach($deployment->nodeDeployments as $nodeDeployment)
                {
                        $rrd_file = $path .  "node_deployment" . $nodeDeployment->id . ".rrd";
                        $nodeDeploymentsUsage[] = RadAcctUtils::getData($rrd_file);
                }
		$month_totals = RadAcctUtils::getMonthlyTotals(RadAcctUtils::combineNodeDeploymentsData($nodeDeploymentsUsage));

		$m = 0;
		foreach($months as $month){
			if (isset($month_totals[$month]))
				$ydata[$m++] = $month_totals[$month]/1024/1024/1024;
			else
				$ydata[$m++] = 0;
		}
		
		SOWN::draw_bar_graph('Last 12 months usage', 'Month', 'Usage (GB)', $months, $ydata);	
	}

	public function action_daily_graph()
	{
		$deployment = $this->_initialize_graph();

                if(!is_object($deployment))
                        return;
		
		$now = time();
		$days = array(date('j/n', $now));
		for ($i=1; $i<=30; $i++) 
		{
			$days[]	= date('j/n', $now-(86400*$i));
		}
		$days = array_reverse($days);
		
		$nodeDeploymentUsage = array();
                $path = Kohana::$config->load('system.default.rrd.deployment_path');
                foreach($deployment->nodeDeployments as $nodeDeployment)
                {
                        $rrd_file = $path .  "node_deployment" . $nodeDeployment->id . ".rrd";
                        $nodeDeploymentsUsage[] = RadAcctUtils::getData($rrd_file);
                }
		$day_totals = RadAcctUtils::combineNodeDeploymentsData($nodeDeploymentsUsage);
		
		$d = 0;
		$curyear = date('Y', $now);
                foreach($days as $day){
			list($day, $month) = explode("/", $day);
			if ($month == 12)
				$year = $curyear-1;
 			else
				$year = $curyear;
                        if (isset($day_totals[$year][$month][$day]))
                                $ydata[$d++] = ($day_totals[$year][$month][$day]['down']+$day_totals[$year][$month][$day]['up'])/1024/1024;
                        else
                                $ydata[$d++] = 0;
                }
		SOWN::draw_bar_graph('Last 30 days usage', 'Day', 'Usage (MB)', $days, $ydata);
	
	}

	public function action_throughout_day_average_graph()
	{
		$deployment = $this->_initialize_graph();
		if(!is_object($deployment))
                        return;

		#$type = $this->request->param('type');
		$type = "user";
                $interval = 600;
                $response = $this->request->response();
                #$response->headers('Content-Type', 'image/png');
		$start_date = $deployment->getLastNodeDeployment()->startDate;
                $through_day = $this->_format_radius_connections_throughout_day_average($this->_get_radius_connections_through_day_for_deployment_results($deployment), $interval, $start_date);
                $xdata = $through_day['thetime'];
                $ydata = $through_day['no_connections'];
                SOWN::draw_line_graph('Average connections throughout day', '', '', $xdata, $ydata, 600, 400, array(45,20,30,90), 0, 'vertical', 6);
	}

	public function action_default()
	{
		$this->check_login();

		$content = "";
		$id = $this->request->param('id');
		$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
		if (!empty($id))
		{
			$deployment = Doctrine::em()->getRepository('Model_Deployment')->findOneById($this->request->param('id'));
                	if (!is_object($deployment))
                	{
                        	throw new HTTP_Exception_404();
                	}
			if (is_object($user) && ($user->isSystemAdmin || $deployment->hasCurrentDeploymentAdmin(Auth::instance()->get_user())))
			{
				$title = "Deployments";
				$subtitle =  "Deployment Usage (" . $deployment->name . ")";
				$bannerItems = array("Create Deployment" => Route::url('create_deployment'), "Current Deployments" => Route::url('current_deployments'), "All Deployments" => Route::url('deployments'));
				$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $bannerItems);
				$content = $this->_render_deployment_usage($deployment, $subtitle);
			}	
			else
			{
				throw new HTTP_Exception_403('You do not have permission to access this page.');
			}
		}
		else
		{
			if(is_object($user))
			{
				$deployments = $user->deploymentsAsCurrentAdmin;
				foreach ($deployments as $deployment)
				{
					$content .= $this->_render_deployment_usage($deployment);
				}
			}
			$title = "My Deployment(s) Usage";
		}
		$this->_render_page($title, $content);
	}

	public function action_all()
	{
		$this->check_login('systemadmin');
		$content = "";
		$deployments = Doctrine::em()->getRepository('Model_Deployment')->where_is_active();
                foreach ($deployments as $deployment)
		{
			$content .= $this->_render_deployment_usage($deployment);
                }
		$this->_render_page("All Deployments Usage", $content);
	}

	public function action_consumption()
	{
		if (in_array($_SERVER['REMOTE_ADDR'], Kohana::$config->load('system.default.admin_system.valid_query_ips')))
                {
			$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($this->request->param('deployment_id'));
			echo $deployment->getConsumption();
			exit;		
		}
		throw new HTTP_Exception_403('You do not have permission to access this page.');	
	}

	private function _render_deployment_usage($deployment, $title = null)
	{
		// This should be moved to view/partial
		if (empty($title))
		{
			$title = $deployment->name;
		}
		$content = "<h2 style=\"text-align: center;\">${title}</h2>\n";
                if ($deployment->cap == 0) 
			$cap = "(unlimited)";
                else 
			$cap = "/ " . $deployment->cap . " MB";
                $content .= "<p style=\"text-align: center;\">Usage: ". round($deployment->consumption, 2). " MB " . $cap . "</p>\n<div style=\"text-align: center; width: 100%;\">\n";

		if ($deployment->cap > 0) 
		{
			$usagebar = View::factory('partial/percentage_usage_bar');
        	        $usagebar->limit = $deployment->cap;
			$usagebar->used = round($deployment->consumption, 2);
			$content .= (string) $usagebar->render()."\n<br/>\n";
		}

		$content .="<img src=\"/admin/deployments/usage/graphs/daily/{$deployment->id}\" alt=\"Last 30 days usage graph for {$deployment->name} deployment\" />\n<br/><br/>";
		$content .="<img src=\"/admin/deployments/usage/graphs/monthly/{$deployment->id}\" alt=\"Historical monthly usage graph for {$deployment->name} deployment\" /><br/><br/>\n";
		$content .="<img src=\"/admin/deployments/usage/graphs/throughout_day_average/{$deployment->id}\" alt=\"Throughout day average usage graph for {$deployment->name} deployment\" />\n";
		$content .= "</div>\n";
		return $content;
	}

	private function _get_radius_connections_through_day_for_deployment_results($deployment)
        {
                $calledstationid = "[UNSET]";
                $node = $deployment->getLastNodeDeployment()->node;
                foreach ($node->interfaces as $interface)
                {
                        $ssid = $interface->ssid;
                        if (!empty($ssid))
                        {
                                $calledstationid = str_replace(":", "-", strtoupper($interface->networkAdapter->mac));
                                break;
                        }
                }
                $qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra')
                        ->select("UNIX_TIMESTAMP(ra.acctstarttime) AS start, UNIX_TIMESTAMP(ra.acctstoptime) AS stop, ra.acctsessiontime AS length, ra.callingstationid AS mac")
                        ->where("ra.acctinputoctets+ra.acctoutputoctets > 0 OR (UNIX_TIMESTAMP(ra.acctstarttime) + ra.acctsessiontime + 600 > UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) AND ra.acctstoptime IS NULL)")
                        ->andWhere("ra.calledstationid LIKE '%$calledstationid%'");
		$sql = $qb->getQuery()->getSql();
                $results = $qb->getQuery()->getResult();
                return $results;
        }

	private function _format_radius_connections_throughout_day_average($results, $interval, $start_date)
        {
                $connections = array();

                for ( $secs = 0; $secs < 86400; $secs += $interval )
                {
			# date returns 1am for 0 seconds
                        $time = date("H:i:s", $secs-3600);
                        $connections[$time] = 0;
                        foreach ( $results as $ts )
                        {
				if (empty($ts['stop']))
                                {
                                        $ts['stop'] = time();
                                }
                                $start = $ts['start'] % 86400;
				$stop = $ts['stop'] % 86400;
				$days = floor($ts['length'] / 86400);
				$connections[$time] = $connections[$time] + $days;
				if ($start < $stop && $start <= $secs && $stop >= $secs)
                                {
                                        $connections[$time]++;
                                }
				elseif ($start > $stop && ($start >= $secs || $stop <= $secs))
				{
					$connections[$time]++;
				}
				elseif ($ts['start'] - $ts['stop'] < $interval && (($start >= $secs && $stop < $secs + $interval * 2) || ($start > $secs - $interval && $stop < $secs + $interval)))
				{
					$connections[$time]++;
				}
                        }
                }
		$start_date_secs = $start_date->format('U');
		$end_date_secs = time();
		$days = floor(($end_date_secs - $start_date_secs ) / 86400);
		$start_date_tod = $start_date_secs % 86400;
		$end_date_tod = $end_date_secs % 86400;
		for ( $secs = 0; $secs < 86400; $secs += $interval )
                {
			$time = date("H:i:s", $secs);
			if ( $start_date_tod > $end_date_tod )
			{
				if ($secs >= $start_date_tod || $secs <= $end_date_tod)
				{
					$connections[$time] = $connections[$time] / ($days + 1);
				}
				else 
				{
					$connections[$time] = $connections[$time] / $days;
				}
			}
			else {
				if ($secs >= $start_date_tod && $secs <= $end_date_tod)
                                {
                                        $connections[$time] = $connections[$time] / ($days + 1);
                                }
                                else
                                {
                                        $connections[$time] = $connections[$time] / $days;
                                }
			}
		}
                return array("thetime" => array_keys($connections), "no_connections" => array_values($connections));
        }


	private function _render_page($title, $content) 
	{
                $this->template->title = $title;
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->content = $content;
	}

}
