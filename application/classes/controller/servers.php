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
			'icingaName' => 'Icinga Name',
			'acquiredDate' => 'Acquired',
			'location' => 'Location',
			'name' => 'ECS FQDN',
			'externalIPs' => 'ECS IPv4/IPv6',
			'internalName' => 'SOWN Hostname',
			'internalCname' => 'SOWN CName',
			'internalIPs' => 'SOWN IPv4/IPv6',
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
                        'icingaName' => 'Icinga Name',
			'acquiredDate' => 'Acquired',
			'location' => 'Location',
                        'name' => 'ECS FQDN',
                        'externalIPs' => 'ECS IPv4/IPv6',
                        'internalName' => 'SOWN Hostname',
                        'internalCname' => 'SOWN CName',
                        'internalIPs' => 'SOWN IPv4/IPv6',
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
                                ->rule('icingaName', 'not_empty', array(':value'))
				->rule('icingaName', 'Model_Server::uniqueIcingaName', array(':value'))
				->rule('description', 'not_empty', array(':value'));
                        if ($validation->check())
                        {
                                $server = Model_Server::build($formValues['icingaName']);
                                $success = "Successfully created Server with Icinga name: <a href=\"/admin/servers/$server->id/edit\">$server->icingaName</a>.";

                        }
                        else
                        {
                                $errors = $validation->errors();
                        }
                }
                else
                {
                        $formValues = array(
                                'icingaName' => '',
                        );

                }
                $formTemplate = array(
                        'icingaName' => array('title' => 'Icinga Name', 'type' => 'input', 'size' => 20, 'hint' => "e.g. GW, AUTH2, etc."),
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100, 'hint' => "What is the purpose of the server?"),
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
		$server = Doctrine::em()->getRepository('Model_Server')->findOneById($this->request->param('id'));
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
                $server = Doctrine::em()->getRepository('Model_Server')->findOneById($this->request->param('id'));
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
		$server = Doctrine::em()->getRepository('Model_Server')->findOneById($id);
                if (!is_object($server))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
                        'id' => $server->id,
                        'icingaName' => $server->icingaName,
			'description' => $server->description,
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
			'internal' => array(
				'internalInterface' => $server->internalInterface,
                                'internalName' => $server->internalName,
				'internalCname' => $server->internalCname,
                                'internalMac' => $server->internalMac,
				'internalSwitchport' => $server->internalSwitchport,
				'internalCable' => $server->internalCable,
                                'internalIPv4' => $server->internalIPv4,
                                'internalIPv6' => $server->internalIPv6,
                        ),
			'external' => array(
				'externalInterface' => $server->externalInterface,
				'name' => $server->name,
				'externalMac' => $server->externalMac,
				'externalSwitchport' => $server->externalSwitchport,
                                'externalCable' => $server->externalCable,
				'externalIPv4' => $server->externalIPv4,
				'externalIPv6' => $server->externalIPv6,
			),	
                );
		if (is_object($server->location))
		{
			$formValues['location'] = $server->location->id;
		}
		if ($server->acquiredDate->format('U') > 86400)
                {
			$formValues['acquiredDate'] = $server->acquiredDate->format('Y-m-d');
		}
		if ($action == 'view')
                {
                        $formValues['retired'] = ( $formValues['retired'] ? 'Yes' : 'No');
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$locations = Sown::get_all_locations();
		$formTemplate = array(
                        'id' => array('type' => 'hidden'),
                        'icingaName' => array('title' => 'Icinga Name', 'type' => 'input', 'size' => 20),
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
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
                        'internal' => array(
				'title' => 'SOWN Interface',
				'type' => 'fieldset',
				'fields' => array(
					'internalInterface' => array('title' => 'Interface Name', 'type' => 'input', 'size' => 6),
                                	'internalName' => array('title' => 'Hostname', 'type' => 'input', 'size' => 20),
                                	'internalCname' => array('title' => 'CName', 'type' => 'input', 'size' => 20),
                                	'internalMac' => array('title' => 'MAC Address', 'type' => 'input', 'size' => 17),
					'internalSwitchport' => array('title' => 'Switchport', 'type' => 'input', 'size' => 40),
					'internalCable' => array('title' => 'Cable', 'type' => 'input', 'size' => 20, 'hint' => 'E.g. yellow'),
                                	'internalIPv4' => array('title' => 'IPv4 Address', 'type' => 'input', 'size' => 15),
					'internalIPv6' => array('title' => 'IPv6 Address', 'type' => 'input', 'size' => 40),
				),
                        ),
			
                        'external' => array(
				'title' => 'External Interface',
                                'type' => 'fieldset',
                                'fields' => array(
					'externalInterface' => array('title' => 'Interface Name', 'type' => 'input', 'size' => 6),
                                        'name' => array('title' => 'Hostname', 'type' => 'input', 'size' => 40),
                                        'externalMac' => array('title' => 'MAC Address', 'type' => 'input', 'size' => 17),
					'externalSwitchport' => array('title' => 'Switchport', 'type' => 'input', 'size' => 40),
                                        'externalCable' => array('title' => 'Cable', 'type' => 'input', 'size' => 20, 'hint' => 'E.g. yellow'),
                                        'externalIPv4' => array('title' => 'IPv4 Address', 'type' => 'input', 'size' => 15),
                                        'externalIPv6' => array('title' => 'IPv6 Address', 'type' => 'input', 'size' => 40),
                                ),

                        ),
		);
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$server = Doctrine::em()->getRepository('Model_Server')->findOneById($id);
		$server->icingaName = $formValues['icingaName'];
		$server->description = $formValues['description'];
		$server->acquiredDate = new \DateTime($formValues['acquiredDate']);
		$server->retired = FormUtils::getCheckboxValue($formValues, 'retired');
		$server->location = null;
		if (!empty($formValues['location']))
		{
			$server->location = Doctrine::em()->getRepository('Model_Location')->findOneById($formValues['location']);
		}
		$server->serverCase = $formValues['serverCase'];
		$server->processor = $formValues['processor'];
		$server->memory = $formValues['memory'];
		$server->hardDrive = $formValues['hardDrive'];
		$server->networkPorts = $formValues['networkPorts'];
		$server->wakeOnLan = $formValues['wakeOnLan'];
                $server->kernel = $formValues['kernel'];
                $server->os = $formValues['os'];
		$server->internalInterface = $formValues['internal']['internalInterface'];
                $server->internalName = $formValues['internal']['internalName'];
		$server->internalCname = $formValues['internal']['internalCname'];
		$server->internalMac = $formValues['internal']['internalMac'];
		$server->internalSwitchport = $formValues['internal']['internalSwitchport'];
		$server->internalCable = $formValues['internal']['internalCable'];
		$server->internalIPv4 = $formValues['internal']['internalIPv4'];
		$server->internalIPv6 = $formValues['internal']['internalIPv6'];
		$server->externalInterface = $formValues['external']['externalInterface'];
                $server->name = $formValues['external']['name'];
                $server->externalMac = $formValues['external']['externalMac'];
		$server->externalSwitchport = $formValues['external']['externalSwitchport'];
                $server->externalCable = $formValues['external']['externalCable'];
                $server->externalIPv4 = $formValues['external']['externalIPv4'];
                $server->externalIPv6 = $formValues['external']['externalIPv6'];
		$server->save();
	}

	private function _load_generated_wiki_page($server_id)
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
                        'wikiMarkup' => array('title' => '', 'type' => 'textarea', 'rows' => 30, 'cols' => 120),
		);
		$server = Doctrine::em()->getRepository('Model_Server')->findOneById($server_id);
		$formValues = array(
			'id' => $server_id,
			'wikiMarkup' => $server->toWikiMarkup(),
		);
		return array($formTemplate, $formValues);
	}
}
	
