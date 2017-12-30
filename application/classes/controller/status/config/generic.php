<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Status_Config_Generic extends Controller
{
	public function action_default()
	{
		$request_name = $this->request->param('request_name');
		$hostname     = $this->request->param('hostname');
		$os           = $this->request->param('os');
		switch($os)
		{
			case 'node':
				$host = Model_Node::getByHostname($hostname);
				break;
			case 'server':
				$host = Model_Server::getByHostname($hostname);
				break;
		}

		$classname = 'Check_'.$request_name;
		$check = new $classname($host);

		Doctrine::em()->getConnection()->close();

		if(Request::$user_agent == 'icinga')
		{
			$check->format_icinga();
		}
		else
		{
			print_r($check);
		}
	}
}

