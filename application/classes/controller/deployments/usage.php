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
		$content = "";
		$user = Doctrine::em()->getRepository('Model_User')->findOneByEmail(Auth::instance()->get_user());
		if(is_object($user))
		{
			$deployments = $user->deploymentsAsCurrentAdmin;
			foreach ($deployments as $deployment)
			{
				$content .= $this->_render_deployment_usage($deployment);
			}
		}
		$this->_render_page("Your Deployment(s) Usage", $content);
	}

	public function action_all()
	{
		$this->check_login(true);
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
		$content = "<h2>" . $deployment->name . "</h2>\n";
                if ($deployment->cap == 0) 
			$cap = "(unlimited)";
                else 
			$cap = "/ " . $deployment->cap . " MB";
                $content .= "<p>Usage: ". round($deployment->consumption, 2). " MB " . $cap . "</p>\n";
		return $content;
	}

	private function _render_page($title, $content) 
	{
		$view = View::factory('template');
                $view->title = $title;

                $sidebar = View::factory('partial/sidebar');
                $view->sidebar = $sidebar;

                $view->content = $content;

                echo (string) $view->render();
	}

}
