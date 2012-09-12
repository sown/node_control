<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller_AbstractAdmin
{
	public function action_default()
	{
		$this->check_login();

		$view = View::factory('template');
		$view->title = "Home";
		$view->heading = "Welcome to the SOWN Admin System";
	
		$sidebar = View::factory('partial/sidebar');
		$view->sidebar = $sidebar;

		$content = View::factory('pages/home');	
		$content->username = Auth::instance()->get_user();
		$view->content = $content;
		
		echo (string) $view->render();
	}
}
