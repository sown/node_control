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
		$dateymd = date('Y-m-d', strtotime('-1 month'));
                $countdql = "SELECT COUNT(DISTINCT ra.callingstationid) FROM Model_Radacct ra WHERE ra.acctstarttime >= '$dateymd 00:00:00' AND ra.acctstoptime IS NULL";
                $count = Doctrine::em()->createQuery($countdql)->getSingleScalarResult();
                if ($count < 1) 
			echo '0';
		else 
			echo $count;
	}
}

