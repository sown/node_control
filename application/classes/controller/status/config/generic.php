<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Status_Config_Generic extends Controller_AbstractAdmin
{
	public function action_default()
	{
		$request_name = $this->request->param('request_name');
		$hostname     = $this->request->param('hostname');
		$os           = $this->request->param('os');
		switch($os)
		{
			case 'backfire':
				$host = Model_Node::getByHostname($hostname);
				break;
			case 'lucid':
				$host = Model_Server::getByName($hostname);
				break;
		}

		$classname = 'Check_'.$request_name;
		$check = new $classname($host);

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

