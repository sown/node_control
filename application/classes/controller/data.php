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
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra');
		$qb->where("ra.acctstoptime IS NULL");
		$qb->andWhere("ra.acctinputoctets > 0 OR ra.acctoutputoctets > 0");
		// 600 seconds because records only get updated every 300 seconds so session time may have been increased since 
		// record was updated. 600 seconds gives enough leeway without including users who have likely disconnected.
		$qb->andWhere("UNIX_TIMESTAMP(ra.acctstarttime) + ra.acctsessiontime + 600 > UNIX_TIMESTAMP(CURRENT_TIMESTAMP())");
		$query = $qb->getQuery();
		echo $query->getSql();
                $curusers = $query->getResult();
		$activeusers = sizeof($curusers);
                if ($activeusers < 1) 
			echo '0';
		else 
			echo $activeusers;
	}

}

