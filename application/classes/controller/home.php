<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller_AbstractAdmin
{
	public function action_default()
	{
		$this->check_login();

		$this->template->title = "Home";
		$this->template->heading = "Welcome to the SOWN Admin System";
		$this->template->sidebar = View::factory('partial/sidebar');

		$content = View::factory('pages/home');	
		$content->username = Auth::instance()->get_user();
		$this->template->content = $content;	
	}
}
