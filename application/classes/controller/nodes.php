<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Nodes extends Controller_AbstractAdmin
{
	public function action_default()
	{
		$this->check_login("systemadmin");

		$this->template->title = "Nodes List";
		$this->template->sidebar = View::factory('partial/sidebar');

		$content = View::factory('partial/nodes_table');	
		$content->nodes = Doctrine::em()->getRepository('Model_Node')->findAll();
	
		$this->template->content = $content;	
	}
}
