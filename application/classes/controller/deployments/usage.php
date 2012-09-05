<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Deployments_Usage extends Controller
{
	public function check_login($isSystemAdmin = false)
	{
		if (Auth::instance()->logged_in())
		{
			if (!$isSystemAdmin)	
				return;
			$user = Doctrine::em()->getRepository('Model_User')->findOneByEmail(Auth::instance()->get_user());
			if (empty($user) || !$user->isSystemAdmin)
				throw new HTTP_Exception_403('You do not have permission to access this page.');
		}
		else
			$this->request->redirect(Route::url('login').URL::query(array('url' => $this->request->url())));
	}

	public function action_default()
	{
		$this->check_login();
		$user = Doctrine::em()->getRepository('Model_User')->findOneByEmail(Auth::instance()->get_user());
		echo "<h1>Node Usage</h1>";
		if(is_object($user))
		{
			$deployments = $user->deploymentsAsCurrentAdmin;
			foreach ($deployments as $deployment)
			{
				$this->_display_deployment_usage($deployment);
			}
		}
	}

	public function action_all(){
		$this->check_login(true);
		echo "<h1>All Node Usage</h1>";
                {
			$deployments = Doctrine::em()->getRepository('Model_Deployment')->where_is_active();
                        foreach ($deployments as $deployment)
			{
				$this->_display_deployment_usage($deployment);
                        }
		}
	}

	private function _display_deployment_usage($deployment)
	{
		echo "<h2>" . $deployment->name . "</h2>";
                if ($deployment->cap == 0) 
			$cap = "(unlimited)";
                else 
			$cap = "/ " . $deployment->cap . " MB";
                echo "<p>Usage: ". round($deployment->consumption, 2). " MB " . $cap . "</p>";
	}

}
