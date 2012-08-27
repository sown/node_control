<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Usage_Config_Generic extends Controller
{
	public function check_login()
	{
		if (!Auth::instance()->logged_in())
		{
			$this->request->redirect(Route::url('package_login').URL::query(array('url' => $this->request->url())));
		}
	}

	public function action_default()
	{
		$this->check_login();
		$user = Doctrine::em()->getRepository('Model_User')->findOneByEmail(Auth::instance()->get_user());
		if ($user->isSystemAdmin)
		{
			$deployments = Doctrine::em()->getRepository('Model_Deployment')->where_is_active();
		}
		else
		{
			$deployments = $user->deploymentsAsAdmin;
		}
		echo sizeof($deployments);
		
	}
}
