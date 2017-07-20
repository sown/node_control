<?php defined('SYSPATH') or die('No direct script access.');

class Controller_OtherHosts extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Other Host" => Route::url('create_host'), "Current Other Hosts" => Route::url('current_hosts'), "All Other Hosts" => Route::url('hosts'));
                $title = 'Other Hosts';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Other Hosts";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'name' => 'Name',
			'type' => 'Type',
			'acquiredDate' => 'Acquired',
			'parent' => 'Parent',
			'hostname' => 'Hostname',
			'cnames' => 'CName(s)',
			'IPv4Addr' => 'IPv4 Address',
			'IPv6Addr' => 'IPv6 Address',
			'retired' => '',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_OtherHost')->findAll();
		$objectType = 'host';
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
                $subtitle = "Curent Other Hosts";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
                        'name' => 'Name',
                        'type' => 'Type',
                        'acquiredDate' => 'Acquired',
                        'parent' => 'Parent',
                        'hostname' => 'Hostname',
                        'cnames' => 'CName(s)',
                        'IPv4Addr' => 'IPv4 Address',
                        'IPv6Addr' => 'IPv6 Address',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
                $rows = Doctrine::em()->getRepository('Model_OtherHost')->findByRetired(0);
                $objectType = 'host';
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
		$subtitle = "Create Other Host";
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
                                ->rule('type', 'not_empty', array(':value'));
			if ($validation->check())
        		{
				$otherHost = Model_OtherHost::build($formValues['name'], $formValues['description'], $formValues['type']);
				$otherHost->save();
				$url = Route::url('edit_host', array('id' => $otherHost->id));
                        	$success = "Successfully created Other Host with ID: <a href=\"$url\">" . $otherHost->id . "</a>."; 
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
				'type' => '',
				'alias' => '',
                        );
                }
                $formTemplate = array(
                        'name' => array('title' => 'Name', 'type' => 'input', 'size' => 20, 'hint' => "e.g. GW, AUTH2, etc."),
                        'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100, 'hint' => "What is the purpose of the server?"),
                        'type' => array('title' => 'Type', 'type' => 'select', 'options' => Kohana::$config->load('system.default.host_types')),
                );

                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_other_host', $formTemplate, $formValues, array('createOtherHost' => 'Create Other Host'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_host', array('id' => $this->request->param('id'))));
                }
		$subtitle = "View Other Host";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_other_host', $formTemplate, $formValues, array('edit_other_host' => 'Edit Other Host'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Other Host";
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
				$success = "Successfully updated Other Host";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_other_host', $formTemplate, $formValues, array('updateOtherHost' => 'Update Other Host'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$other_host = Doctrine::em()->getRepository('Model_OtherHost')->findOneById($this->request->param('id'));
                if (!is_object($other_host))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Other Host";
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'OtherHost';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted Other Host with ID " . $formValues['id'] .".</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete Other Host with ID " . $formValues['id'] .".</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Other Host with ID " . $formValues['id'] . " was not deleted.</p>";
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
				'message' => "Are you sure you want to delete Other Host with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_other_host', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->sidebar = View::factory('partial/sidebar');
	}

	
	private function _validate($formValues) 
	{
		$errors = array();
		$validation = Validation::factory($formValues);

                if (!$validation->check())
                {
			$errors = $validation->errors();
                }
		foreach ($formValues['contacts']['currentContacts'] as $c => $contact)
                {
                        if(!empty($contact['name']))
                        {
                                $validation = Validation::factory($contact)
                                        ->rule('email', 'not_empty', array(':value'))
                                        ->rule('email', 'email', array(':value'));
                                if (!$validation->check())
                                {
                                        foreach ($validation->errors() as $e => $error)
                                        {
                                                $errors["Contact " . $contact['name'] . " $e"] = $error;
                                        }
                                }
                        }
                }
		return $errors;
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$other_host = Doctrine::em()->getRepository('Model_OtherHost')->findOneById($id);
                if (!is_object($other_host))
                {
                        throw new HTTP_Exception_404();
                }
		Doctrine::em()->refresh($other_host);

		$formValues = array(
                        'id' => $other_host->id,
                        'name' => $other_host->name,
                        'description' => $other_host->description,
			'type' => $other_host->type,
                        'parent' => $other_host->parent,
                        'acquiredDate' => '',
                        'retired' => $other_host->retired,
			'internal' => $other_host->internal,
                        'location' => '',
                        'case' => $other_host->case,
                        'hostname' => $other_host->hostname,
			'cnames' => Model_OtherHostCname::getList($other_host->cnames),
			'mac' => $other_host->mac,
			'IPv4Addr' => $other_host->IPv4Addr,
                        'IPv6Addr' => $other_host->IPv6Addr,
			'alias' => $other_host->alias,
			'checkCommand' => $other_host->checkCommand,
			'services' => array(),
			'contacts' => array(
                                'currentContacts' => array(),
                        ),
                );

		$c = 0;
                $contact_fields = array('id', 'name', 'email');
                foreach($other_host->contacts as $c => $contact)
                {
                        foreach ($contact_fields as $cf)
                        {
                                $formValues['contacts']['currentContacts'][$c][$cf] = $contact->$cf;
                        }
                }

		if (is_object($other_host->location) && is_int($other_host->location->id))
                {
                        $formValues['location'] = $other_host->location->id;
                }
                if (is_object($other_host->acquiredDate) && $other_host->acquiredDate->format('U') > 86400)
                {
                        $formValues['acquiredDate'] = $other_host->acquiredDate->format('Y-m-d');
                }
		
		if ($action == 'view')
                {	
			$formValues['retired'] = ( $formValues['retired'] ? 'Yes' : 'No');
			$formValues['internal'] = ( $formValues['internal'] ? 'Yes' : 'No');
			$services = array();
                        foreach ($other_host->services as $hostService)
                        {
                                $services[] = $hostService->service->label;
                        }
                        $formValues['services'] = implode(", ", $services);
		}
		if ($action == 'edit')
                {
                        foreach ($contact_fields as $cf)
                        {
                                $formValues['contacts']['currentContacts'][$c+1][$cf] = '';
                        }
                        foreach ($other_host->services as $hostService)
                        {
                                $formValues['services'][] = $hostService->service->id;
                        }
                }

		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$locations = Sown::get_all_locations();
		$services = Sown::get_all_host_services();
		$formTemplate = array(
                        'id' => array('type' => 'hidden'),
                        'name' => array('title' => 'Name', 'type' => 'input', 'size' => 20),
                        'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
                       	'type' => array('title' => 'Type', 'type' => 'select', 'options' => Kohana::$config->load('system.default.host_types')), 
			'parent' => array('title' => 'Parent Host', 'type' => 'input', 'size' => 20, 'hint' => "e.g. SWITCH"),
                        'acquiredDate' => array('title' => 'Acquired Date', 'type' => 'date'),
                        'retired' => array('title' => 'Retired?', 'type' => 'checkbox'),
			'internal' => array('title' => 'Internal?', 'type' => 'checkbox'),
			'location' => array('title' => 'Location', 'type' => 'select', 'options' => $locations),
                        'case' => array('title' => 'Case', 'type' => 'input', 'size' => 20, 'hint' => "e.g. form factor, switch model, etc."),
                        'hostname' => array('title' => 'Hostname', 'type' => 'input', 'size' => 50),
                        'cnames' => array('title' => 'CName(s)', 'type' => 'input', 'size' => 50),
			'mac' => array('title' => 'MAC', 'type' => 'input', 'size' => 17),
                        'IPv4Addr' => array('title' => 'IPv4', 'type' => 'input', 'size' => 15),
                        'IPv6Addr' => array('title' => 'IPv6', 'type' => 'input', 'size' => 50),
			'alias' => array('title' => 'Alias', 'type' => 'input', 'size' => 50),
			'checkCommand' => array('title' => 'Check Command', 'type' => 'input', 'size' => 50),
			'services' => array('title' => 'Services', 'type' => 'multiselect', 'options' => $services),
			'contacts' => array(
                                'title' => 'Contacts',
                                'type' => 'fieldset',
                                'fields' => array(
                                        'currentContacts' => array(
                                                'title' => '',
                                                'type' => 'table',
                                                'fields' => array(
                                                        'id' => array('type' => 'hidden'),
                                                        'name' => array('title' => 'Name', 'type' => 'input'),
                                                        'email' => array('title' => 'Email', 'type' => 'input'),
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
		$other_host = Doctrine::em()->getRepository('Model_OtherHost')->findOneById($id);
		$other_host->name = $formValues['name'];
                $other_host->description = $formValues['description'];
                $other_host->type = $formValues['type'];
                $other_host->parent = $formValues['parent'];
                $other_host->acquiredDate = (!empty($formValues['acquiredDate']) ? new \DateTime($formValues['acquiredDate']) : null);
                $other_host->retired = FormUtils::getCheckboxValue($formValues, 'retired');
		$other_host->internal = FormUtils::getCheckboxValue($formValues, 'internal');
                $other_host->location = (!empty($formValues['location']) ? Doctrine::em()->getRepository('Model_Location')->find($formValues['location']) : null);
                $other_host->case = $formValues['case'];
		$other_host->hostname = $formValues['hostname'];
		$other_host->updateCnames($formValues['cnames']);
		$other_host->mac = $formValues['mac'];	
		$other_host->IPv4Addr = $formValues['IPv4Addr'];
		$other_host->IPv6Addr = $formValues['IPv6Addr'];
		$other_host->alias = $formValues['alias'];
		$other_host->checkCommand = $formValues['checkCommand'];

		$dbServices = array();
                $hostServices = Doctrine::em()->getRepository('Model_HostService')->findByOtherHost($other_host);
                foreach ($hostServices as $hostService)
                {
                        $serviceId = $hostService->service->id;
                        if (!in_array($serviceId, $formValues['services']))
                        {
                                Model_Builder::destroy_simple_object($hostService->id, 'HostService');
                        }
                        else
                        {
                                $dbServices[] = $serviceId;
                        }
                }
		if (!empty($formValues['services']))
		{
	                foreach ($formValues['services'] as $serviceId)
        	        {
                	        if (!in_array($serviceId, $dbServices))
                        	{
                                	$service = Doctrine::em()->getRepository('Model_Service')->find($serviceId);
	                                $hostService = Model_HostService::build(null, $other_host, $service);
        	                        if (is_object($hostService))
                	                {
                        	                $hostService->save();
	                                }
        	                }
        	        }
		}
		foreach ($formValues['contacts']['currentContacts'] as $c => $contactValues)
                {
                        if (empty($contactValues['name']) && empty($contactValues['email']))
                        {
                                if (!empty($contactValues['id']))
                                {
                                        $contact = Doctrine::em()->getRepository('Model_Contact')->find($contactValues['id']);
                                        $contact->delete();
                                }
                        }
                        else
                        {
                                if (empty($contactValues['id'])) {
                                        $other_host->contacts->add(Model_Contact::build(
                                                'OtherHost',
                                                $other_host,
                                                $contactValues['name'],
                                                $contactValues['email']
                                        ));
                                }
                                else
                                {
                                        $contact = Doctrine::em()->getRepository('Model_Contact')->find($contactValues['id']);
                                        $contact->name = $contactValues['name'];
                                        $contact->email = $contactValues['email'];
                                        $contact->save();
                                }
                        }
                }
		$other_host->save();
	}
}
	
