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

	public function action_create()
	{
		$this->check_login("systemadmin");
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$form = $this->request->post();
			$validation = Validation::factory($form)
				->rule('boxNumber', 'digit')
				->rule('boxNumber', 'max_length', array(':value', '4'))
				->rule('boxNumber', 'Model_Node::nonUniqueBoxNumber', array(':value'))
 				->rule('vpnServer', 'not_empty')
				->rule('wiredMac', 'not_empty', array(':value'))
				->rule('wiredMac', 'SownValid::mac', array(':value'))
				->rule('wirelessMac', 'SownValid::mac', array(':value'))
                                ->rule('wirelessMac', 'not_empty', array(':value'));
				
			
			if ($validation->check())
        		{
				$node = Model_Builder::create_node($form['boxNumber'], $form['vpnServer'], $form['wiredMac'], $form['wirelessMac'], $form['firmwareImage'], $form['notes']);
                        	$success = "Successfully created node with box number: <a href=\"/admin/nodes/$node->id\">$node->boxNumber</a>.";
 
        		}
			else 
			{
				$errors = $validation->errors();
			}
 
                }
		else
		{
			$form = array(
				'boxNumber' => '',
				'vpnServer' => '',
				'wiredMac' => '',
				'wirelessMac' => '',
				'firmwareImage' => 'backfire',
				'notes' => 'The config for this node was autogenerated.',
			);
			
		}
		$form_template = array(
			'boxNumber' => array('title' => 'Box Number', 'type' => 'input', 'hint' => "Leave empty to auto-assign box number"),
			'vpnServer' => array('title' => 'VPN Server', 'type' => 'select', 'options' => Model_VpnServer::getVpnServerNames()),
			'wiredMac' => array('title' => 'Wired Mac', 'type' => 'input', 'hint' => "e.g. 01:23:45:67:89:AB"),
                        'wirelessMac' => array('title' => 'Wireless Mac', 'type' => 'input', 'hint' => "e.g. 01:23:45:67:89:AB"),
                        'firmwareImage' => array('title' => 'Firmware Image', 'type' => 'input'),
                        'notes' => array('title' => 'Notes', 'type' => 'textarea'),
		);
	
                $this->template->title = "Create Node";
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->content = FormUtils::drawForm($form_template, $form, array('create_node' => 'Create Node'), $errors, $success);
	}
}
