<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller
{
	public function check_login($isSystemAdmin = false)
	{
		if (!Auth::instance()->logged_in())
		{
			$this->request->redirect(Route::url('login').URL::query(array('url' => $this->request->url())));
		}
	}

	public function action_default()
	{
		$this->check_login();
		$user = Doctrine::em()->getRepository('Model_User')->findOneByEmail(Auth::instance()->get_user());
		echo "<html><head><title>Welcome to the SOWN Admin System</title></head><body>";
		echo "<h1>Welcome to the SOWN Admin System</h1>";
		
		if(is_object($user))
			echo "<p>How are you, ".$user->email."?</p>";
		else
			echo "<p>I don't know who you are ".Auth::instance()->get_user().".</p>";
		
		echo "<h2>Menu</h2>";
		echo "<ul>";
		echo "<li><a href='admin/test'>Test</a></li>";
		echo "<li><a href='admin/deployments/usage'>My Deployments Usage</a></li>";
		echo "<li><a href='admin/deployments/usage/all'>All Deployments Usage (Admin only)</a></li>";
		echo "</ul>";
		echo "<form method='POST' action='logout'>";
                echo "<input type='submit' value='Logout' />";
		echo "</form>";
		echo "</body></html>";

	}
}
