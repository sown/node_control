<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Servers extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Server" => Route::url('create_server'), "Current Servers" => Route::url('current_servers'), "All Servers" => Route::url('servers'));
                $title = 'Servers';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Servers";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'name' => 'Name',
			'state' => 'State',
                        'purpose' => 'Purpose',
			'parent' => 'Parent',
			'acquiredDate' => 'Acquired',
			'location' => 'Location',
			'os' => 'OS',
			'kernel' => 'Kernel',
			'retired' => '',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_Server')->findAll();
		$objectType = 'server';
                $idField = 'id';
		$content = View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_current()
        {
                $this->check_login("systemadmin");
                $subtitle = "Current Servers";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
                        'name' => 'Name',
			'state' => 'State',
			'purpose' => 'Purpose',
			'parent' => 'Parent',
			'acquiredDate' => 'Acquired',
			'location' => 'Location',
                        'os' => 'OS',
                        'kernel' => 'Kernel',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_Server')->findByRetired(0);
                $objectType = 'server';
                $idField = 'id';
                $content = View::factory('partial/table')
                        ->bind('fields', $fields)
                        ->bind('rows', $rows)
                        ->bind('objectType', $objectType)
                        ->bind('idField', $idField);
                $this->template->content = $content;
        }

	public function action_create()
	{
		$this->check_login("systemadmin");
		$subtitle = "Create Server";
		View::bind_global('subtitle', $subtitle);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues)
                                ->rule('name', 'not_empty', array(':value'))
				->rule('name', 'Model_Server::uniqueName', array(':value'))
				->rule('description', 'not_empty', array(':value'))
				->rule('state', 'not_empty', array(':value'))
				->rule('purpose', 'not_empty', array(':value'));
                        if ($validation->check())
                        {
                                $server = Model_Server::build($formValues['name'], $formValues['description'], $formValues['state'], $formValues['purpose'], $formValues['parent']);
                                $success = "Successfully created Server with name: <a href=\"/admin/servers/$server->id/edit\">$server->name</a>.";

                        }
                        else
                        {
                                $errors = $validation->errors();
                        }
                }
                else
                {
                        $formValues = array(
                                'name' => '',
				'description' => '',
				'parent' => 'SWITCH',
                        );

                }
                $formTemplate = array(
                        'name' => array('title' => 'Name', 'type' => 'input', 'size' => 20, 'hint' => "e.g. GW, AUTH2, etc."),
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100, 'hint' => "What is the purpose of the server?"),
			'state' => array('title' => 'State', 'type' => 'select', 'options' => array('phys' => 'Physical', 'virt' => 'Virtual')),
			'purpose' => array('title' => 'Purpose', 'type' => 'select', 'options' => array('cor' => 'Core', 'dev' => 'Development', 'bac' => 'Backup', 'exc' => 'External (Core)',  'exd' => 'External (Development)')),
			'parent' => array('title' => 'Parent Host', 'type' => 'input', 'size' => 20, 'hint' => "e.g. SWITCH"),
                );

                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_server', $formTemplate, $formValues, array('createServer' => 'Create Server'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
			$formValues = FormUtils::parseForm($this->request->post());
                        if (!empty($formValues['editServer']))
			{
	                        $this->request->redirect(Route::url('edit_server', array('id' => $this->request->param('id'))));
			}
			elseif (!empty($formValues['generateWikiPage']))
			{
				$this->request->redirect(Route::url('generate_server_wiki_page', array('id' => $this->request->param('id'))));
			}
                }
		$subtitle = "View Server";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$serverLocation = Doctrine::em()->getRepository('Model_Location')->find($formValues['location']);
		if (is_object($serverLocation)) 
		{
                	$formValues['location'] = "{$serverLocation->longName} ({$serverLocation->name})";
		}
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_server', $formTemplate, $formValues, array('editServer' => 'Edit Server', 'generateWikiPage' => 'Generate Wiki Page'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Server";
		View::bind_global('subtitle', $subtitle);
		$cssFiles =  array('jquery-ui.css');
                View::bind_global('cssFiles', $cssFiles);
                $jsFiles = array('jquery.js', 'jquery-ui.js');
                View::bind_global('jsFiles', $jsFiles);
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$errors = array();
                $success = "";
		if ($this->request->method() == 'POST')
                {
                        $formValues = FormUtils::parseForm($this->request->post());
			$errors = $this->_validate($formValues);
			if (sizeof($errors) == 0)
			{
				$this->_update($this->request->param('id'), $formValues);
				$success = "Successfully updated Server";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_server', $formTemplate, $formValues, array('updateServer' => 'Update Server'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$server = Doctrine::em()->getRepository('Model_Server')->find($this->request->param('id'));
                if (!is_object($server))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Server";
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'Server';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted Server with ID " . $formValues['id'] .".</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete Server with ID " . $formValues['id'] .".</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Server with ID " . $formValues['id'] . " was not deleted.</p>";
                        }
			
		}
		else
		{
			$formTemplate = array(
				'id' =>	array('type' => 'hidden'),
				'message' => array('type' => 'message'),
			);
			$formValues = array(
				'id' => $this->request->param('id'),
				'message' => "Are you sure you want to delete Server with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_server', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->sidebar = View::factory('partial/sidebar');
	}

	public function action_generate_wiki_page()
	{
		$this->check_login("systemadmin");
                $server = Doctrine::em()->getRepository('Model_Server')->find($this->request->param('id'));
                if (!is_object($server))
                {
                        throw new HTTP_Exception_404();
                }
		if ($this->request->method() == 'POST')
                {
                	$this->request->redirect(Route::url('view_server', array('id' => $this->request->param('id'))));
		}	
		$subtitle = "Generated Server Wiki page";
                View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		list($formTemplate, $formValues) = $this->_load_generated_wiki_page($this->request->param('id'));
                $this->template->content = FormUtils::drawForm('generate_server_wiki_page', $formTemplate, $formValues, array( 'viewServer' => '<< Back to Server View Page'));

	}

	public function action_generate_icinga()
       	{
		$this->auto_render = FALSE;
                $this->check_login("systemadmin");
		$this->response->headers('Content-Type','application/json');
		$servers = Doctrine::em()->getRepository('Model_Server')->findByRetired(0);
		$servers_icinga = array();
		foreach($servers as $server) 
		{
			$attrs = array(
				'type' => $server->state.$server->purpose,
				'parent' => $server->parent,
				'internal_ipv4' => null,
				'internal_ipv6' => null,
				'external_ipv4' => null,
				'external_ipv6' => null,
			);
			
			foreach ($server->interfaces as $interface) 
			{
				if (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.internal')))
				{	
					$attrs['internal_ipv4'] = (strlen(trim($interface->IPv4Addr)) > 0 ? $interface->IPv4Addr : null);
					$attrs['internal_ipv6'] = (strlen(trim($interface->IPv6Addr)) > 0 ? $interface->IPv6Addr : null);
				}
				elseif (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.external')))
				{
					$attrs['external_ipv4'] = (strlen(trim($interface->IPv4Addr)) > 0 ? $interface->IPv4Addr : null);
                                        $attrs['external_ipv6'] = (strlen(trim($interface->IPv6Addr)) > 0 ? $interface->IPv6Addr : null);
				}
			}
			$servers_icinga[$server->name] = $attrs;
		}
		$this->response->body(SOWN::jsonpp(json_encode($servers_icinga)));
	}

	public function action_incoming()
        {
                Sown::process_server_attributes($this->request);
        }	

	private function _validate($formValues) 
	{
		$errors = array();
		$validation = Validation::factory($formValues);

                if (!$validation->check())
                {
			$errors = $validation->errors();
                }
		return $errors;
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$server = Doctrine::em()->getRepository('Model_Server')->find($id);
                if (!is_object($server))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
                        'id' => $server->id,
                        'name' => $server->name,
			'description' => $server->description,
			'state' => $server->state,
			'purpose' => $server->purpose,
			'parent' => $server->parent,
			'acquiredDate' => '',
			'retired' => $server->retired,
			'location' => '',
			'serverCase' => $server->serverCase,
			'processor' => $server->processor,
			'memory' => $server->memory,
			'hardDrive' => $server->hardDrive,
			'networkPorts' => $server->networkPorts,
			'wakeOnLan' => $server->wakeOnLan,
			'kernel' => $server->kernel,
			'os' => $server->os,
			'interfaces' => array(
                                'currentInterfaces' => array(),
                        ),
                );
		$i = 0;
		$intf_fields = array('id', 'vlan', 'name', 'hostname', 'cname', 'mac', 'switchport', 'cable', 'IPv4Addr', 'IPv6Addr');
                foreach ($server->interfaces as $i => $interface)
                {
			foreach ($intf_fields as $if)
			{
				if ($if == "vlan")
				{
					$formValues['interfaces']['currentInterfaces'][$i][$if] = $interface->$if->id;
				}
				else 
				{
					$formValues['interfaces']['currentInterfaces'][$i][$if] = $interface->$if;
				}
                        }
			if ($action == 'view')
                        {
                                $formValues['interfaces']['currentInterfaces'][$i]['vlan'] = $interface->vlan->name;
                        }
                }

		if (is_object($server->location))
		{
			$formValues['location'] = $server->location->id;
		}
		if (is_object($server->acquiredDate) && $server->acquiredDate->format('U') > 86400)
                {
			$formValues['acquiredDate'] = $server->acquiredDate->format('Y-m-d');
		}
		
		if ($action == 'view')
                {
                        $formValues['retired'] = ( $formValues['retired'] ? 'Yes' : 'No');
		}
		if ($action == 'edit')
                {
			foreach ($intf_fields as $if)
                        {
                                $formValues['interfaces']['currentInterfaces'][$i+1][$if] = '';
                        }
                }
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$locations = Sown::get_all_locations();
		$vlans = Sown::get_all_vlans();
		$formTemplate = array(
                        'id' => array('type' => 'hidden'),
                        'name' => array('title' => 'Name', 'type' => 'input', 'size' => 20),
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
			'state' => array('title' => 'State', 'type' => 'select', 'options' => array('phys' => 'Physical', 'virt' => 'Virtual')),
                        'purpose' => array('title' => 'Purpose', 'type' => 'select', 'options' => array('cor' => 'Core', 'dev' => 'Development', 'bac' => 'Backup', 'exc' => 'External (Core)',  'exd' => 'External (Development)')),
			'parent' => array('title' => 'Parent Host', 'type' => 'input', 'size' => 20, 'hint' => "e.g. SWITCH"),
			'acquiredDate' => array('title' => 'Acquired Date', 'type' => 'date'),
			'retired' => array('title' => 'Retired?', 'type' => 'checkbox'),
			'location' => array('title' => 'Location', 'type' => 'select', 'options' => $locations),
			'serverCase' => array('title' => 'Case', 'type' => 'input', 'size' => 50, 'hint' => 'E.g. 2U Dell R730'),
			'processor' => array('title' => 'Processor', 'type' => 'input', 'size' => 50),
			'memory' => array('title' => 'Memory', 'type' => 'input', 'size' => 6, 'hint' => 'E.g. 4GiB'),
			'hardDrive' => array('title' => 'Hard Drive', 'type' => 'input', 'size' => 100),
			'networkPorts' => array('title' => 'Network Ports', 'type' => 'input', 'size' => 100, 'hint' => 'E.g. 2 x BCM2715 Gigabit'),
			'wakeOnLan' => array('title' => 'Wake-On-Lan', 'type' => 'input', 'size' => 100),
                        'kernel' => array('title' => 'Kernel', 'type' => 'input', 'size' => 50),
                        'os' => array('title' => 'Operating System', 'type' => 'input', 'size' => 50),
			'interfaces' => array(
                                'title' => 'Interfaces',
                                'type' => 'fieldset',
                                'fields' => array(
                                        'currentInterfaces' => array(
                                                'title' => '',
                                                'type' => 'table',
                                                'fields' => array(
                                                        'id' => array('type' => 'hidden'),
							'vlan' => array('title' => 'VLAN', 'type' => 'select', 'options' => $vlans),
                                                        'name' => array('title' => 'Name', 'type' => 'input', 'size' => 3),
							'hostname' => array('title' => 'Hostname', 'type' => 'input', 'size' => 25),
							'cname' => array('title' => 'CName', 'type' => 'input', 'size' => 15),
							'mac' => array('title' => 'MAC', 'type' => 'input', 'size' => 14),
							'switchport' => array('title' => 'Switchport', 'type' => 'input', 'size' => 29),
							'cable' => array('title' => 'Csble', 'type' => 'input', 'size' => 4),
                                                        'IPv4Addr' => array('title' => 'IPv4', 'type' => 'input', 'size' => 11),
                                                        'IPv6Addr' => array('title' => 'IPv6', 'type' => 'input', 'size' => 25),
                                                ),
                                        ),
                                ),
                        )
		);
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$server = Doctrine::em()->getRepository('Model_Server')->find($id);
		$server->name = $formValues['name'];
		$server->description = $formValues['description'];
		$server->state = $formValues['state'];
		$server->purpose = $formValues['purpose'];
		$server->parent = $formValues['parent'];
		$server->acquiredDate = new \DateTime($formValues['acquiredDate']);
		$server->retired = FormUtils::getCheckboxValue($formValues, 'retired');
		$server->location = null;
		if (!empty($formValues['location']))
		{
			$server->location = Doctrine::em()->getRepository('Model_Location')->find($formValues['location']);
		}
		$server->serverCase = $formValues['serverCase'];
		$server->processor = $formValues['processor'];
		$server->memory = $formValues['memory'];
		$server->hardDrive = $formValues['hardDrive'];
		$server->networkPorts = $formValues['networkPorts'];
		$server->wakeOnLan = $formValues['wakeOnLan'];
                $server->kernel = $formValues['kernel'];
                $server->os = $formValues['os'];

		foreach ($formValues['interfaces']['currentInterfaces'] as $i => $interfaceValues)
                {
                        if (empty($interfaceValues['name']) && empty($interfaceValues['hostname']))
                        {
                                if (!empty($interfaceValues['id']))
                                {
                                        $interface = Doctrine::em()->getRepository('Model_ServerInterface')->find($interfaceValues['id']);
                                        $interface->delete();
                                }
                        }
                        else
                        {
				$vlan = Doctrine::em()->getRepository('Model_Vlan')->find($interfaceValues['vlan']);
                                if (empty($interfaceValues['id'])) {
                                        $server->interfaces->add(Model_ServerInterface::build(
						$server,
						$vlan,	
						$interfaceValues['name'],
						$interfaceValues['hostname'],
						$interfaceValues['cname'],	
						$interfaceValues['mac'],
						$interfaceValues['switchport'],
						$interfaceValues['cable'],
						$interfaceValues['IPv4Addr'],
                                        	$interfaceValues['IPv6Addr']
                                        ));
                                }
                                else
                                {
                                        $interface = Doctrine::em()->getRepository('Model_ServerInterface')->find($interfaceValues['id']);
                                        $interface->vlan = $vlan;
					$interface->name = $interfaceValues['name'];
					$interface->hostname = $interfaceValues['hostname'];
					$interface->cname = $interfaceValues['cname'];
					$interface->mac = $interfaceValues['mac'];
					$interface->switchport = $interfaceValues['switchport'];
                                        $interface->cable = $interfaceValues['cable'];
                                        $interface->IPv4Addr = $interfaceValues['IPv4Addr'];
                                        $interface->IPv6Addr = $interfaceValues['IPv6Addr'];
                                        $interface->save();
                                }
                        }
                }
		$server->save();
	}

	private function _load_generated_wiki_page($server_id)
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
                        'wikiMarkup' => array('title' => '', 'type' => 'textarea', 'rows' => 30, 'cols' => 120),
		);
		$server = Doctrine::em()->getRepository('Model_Server')->find($server_id);
		$formValues = array(
			'id' => $server_id,
			'wikiMarkup' => $server->toWikiMarkup(),
		);
		return array($formTemplate, $formValues);
	}
}
	
