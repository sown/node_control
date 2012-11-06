<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Deployments_Usage extends Controller_AbstractAdmin
{
	private function _initialize_graph()
	{
		$response = $this->request->response();
                // Changed /usr/share/php/kohana3.2/system/classes/kohana/http/header.php replaceing Text:: with Kohana_Text::
                $response->headers('Content-Type', 'image/png');

                $deployment = Doctrine::em()->getRepository('Model_Deployment')->find($this->request->param('deployment_id'));

                if (!Auth::instance()->logged_in('systemadmin'))
                {
                        $user = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
                        if (!$deployment->isCurrentDeploymentAdmin($user->id))
                        {
                                return;
                        }
                }

                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph', 'php');
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_bar', 'php');
		return $deployment;
	}
	
	private function _draw_bar_graph($title, $xlabel, $ylabel, $xdata, $ydata)
	{
		$graph = new Graph(600, 200);
                $graph->SetScale('textlin');
                $graph->img->SetMargin(70,10,30,60);
                $graph->title->Set($title);
                $graph->xaxis->title->Set($xlabel);
                $graph->yaxis->title->Set($ylabel);
		$graph->xaxis->SetTitleMargin(30); 
		$graph->yaxis->SetTitleMargin(50); 
		$graph->xaxis->SetLabelAngle(50);	
                $graph->xaxis->SetTickLabels($xdata);
                $barplot= new BarPlot($ydata);
                $barplot->SetFillColor('blue@0.5');
                $graph->Add($barplot);
                $graph->Stroke();
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

		$node_deployment_usage = array();
		$path = Kohana::$config->load('system.default.rrd_deployment_path');
		foreach($deployment->node_deployments as $node_deployment)
                {
                        $rrd_file = $path .  "node_deployment" . $node_deployment->id . ".rrd";
                        $node_deployments_usage[] = RadAcctUtils::getData($rrd_file);
                }
		$month_totals = RadAcctUtils::getMonthlyTotals(RadAcctUtils::combineNodeDeploymentsData($node_deployments_usage));

		$m = 0;
		foreach($months as $month){
			if (isset($month_totals[$month]))
				$ydata[$m++] = $month_totals[$month]/1024/1024/1024;
			else
				$ydata[$m++] = 0;
		}
		
		$this->_draw_bar_graph('Last 12 months usage', 'Month', 'Usage (GB)', $months, $ydata);	
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
		
		$node_deployment_usage = array();
                $path = Kohana::$config->load('system.default.rrd_deployment_path');
                foreach($deployment->node_deployments as $node_deployment)
                {
                        $rrd_file = $path .  "node_deployment" . $node_deployment->id . ".rrd";
                        $node_deployments_usage[] = RadAcctUtils::getData($rrd_file);
                }
		$day_totals = RadAcctUtils::combineNodeDeploymentsData($node_deployments_usage);
		
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
		$this->_draw_bar_graph('Last 30 days usage', 'Day', 'Usage (MB)', $days, $ydata);
	
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
			if (is_object($user) && ($user->isSystemAdmin || $deployment->isCurrentDeploymentAdmin(Auth::instance()->get_user())))
			{
				$title = "Usage for ".$deployment->name;
				$content = $this->_render_deployment_usage($deployment);
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
			$title = "Your Deployment(s) Usage";
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

	private function _render_deployment_usage($deployment)
	{
		// This should be moved to view/partial
		$content = "<h2>" . $deployment->name . "</h2>\n";
                if ($deployment->cap == 0) 
			$cap = "(unlimited)";
                else 
			$cap = "/ " . $deployment->cap . " MB";
                $content .= "<p>Usage: ". round($deployment->consumption, 2). " MB " . $cap . "</p>\n";

		if ($deployment->cap > 0) 
		{
			$usagebar = View::factory('partial/percentage_usage_bar');
        	        $usagebar->limit = $deployment->cap;
			$usagebar->used = round($deployment->consumption, 2);
			$content .= (string) $usagebar->render()."\n<br/>\n";
		}

		$content .="<img src=\"/admin/deployments/usage/graphs/daily/{$deployment->id}\" alt=\"Last 30 days usage graph for {$deployment->name} deployment\" />\n<br/><br/>";
		$content .="<img src=\"/admin/deployments/usage/graphs/monthly/{$deployment->id}\" alt=\"Historical monthly usage graph for {$deployment->name} deployment\" />\n";
		return $content;
	}

	private function _render_page($title, $content) 
	{
                $this->template->title = $title;
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->content = $content;
	}

}
