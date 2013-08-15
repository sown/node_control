<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Deployments_Main extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Deployment" => Route::url('create_deployment'), "Deployment List" => Route::url('deployments'));
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$title = "Deployment List";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'name' => 'Name',
			'deploymentBoxNumber' => 'Latest Box Number',
			'startDate' => 'Start Date',
			'endDate' => 'End Date',
			'latestNote' => 'Latest Note',
                        'view' => '',
			'usage' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_Deployment')->findAll();
		$objectType = "deployment";
		$idField = "id";
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
		$title = "Create Deployment";
		View::bind_global('title', $title);
		$cssFiles =  array('jquery-ui.css');
                View::bind_global('cssFiles', $cssFiles);
                $jsFiles = array('jquery.js', 'jquery-ui.js');
                View::bind_global('jsFiles', $jsFiles);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues)
				->rule('nodeId', 'not_empty')
				->rule('name', 'not_empty')
				->rule('longitude', 'not_empty')
				->rule('longitude',  'numeric')
				->rule('latitude', 'not_empty')
                                ->rule('latitude', 'numeric')
				->rule('cap', 'not_empty')
                                ->rule('cap', 'digit')
				->rule('admin', 'not_empty');
			if ($validation->check())
        		{
				$deployment = Model_Builder::create_deployment($formValues['nodeId'], $formValues['name'], $formValues['longitude'], $formValues['latitude'], $formValues['cap'], $formValues['admin']);
				$url = Route::url("view_deployment", array('id' => $deployment->id));
                        	$success = "Successfully created Deployment with name: <a href=\"$url\">" . $deployment->name . "</a>.";
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
				'nodeId' => '',
				'latitude' => '',
				'longitude' => '',
				'cap' => '5120',
				'admin' => '',
			);
			
		}
		$formTemplate = array(
			'nodeId' => array('title' => 'Node', 'type' => 'select', 'options' => Model_Node::getUndeployedNodes()),
			'name' => array('title' => 'Name', 'type' => 'input'),
			'longitude' => array('title' => 'Longitude', 'type' => 'input', 'size' => 15, 'hint' => 'e.g. -1.397702'),
			'latitude' => array('title' => 'Latitude', 'type' => 'input', 'size' => 15, 'hint' => 'e.g. 50.93733'),
			'cap' => array('title' => 'Usage cap', 'type' => 'input', 'size' => 10, 'hint' => 'MB'),
			'admin' => array('title' => 'Administrator', 'type' => 'autocomplete', 'autocompleteUrl' => Route::url('user_autocomplete'), 'size' => 50),
		);
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('Deployment', $formTemplate, $formValues, array('createDeployment' => 'Create Deployment'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
		{
			$this->request->redirect(Route::url('edit_deployment', array('id' => $this->request->param('id'))));
		}
		$title = "View Deployment";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$notesFormValues = Controller_Notes::load_from_database('Deployment', $formValues['id'], 'view');
		$notesFormTemplate = Controller_Notes::load_form_template('view');
		$this->template->content = FormUtils::drawForm('Deployment', $formTemplate, $formValues, array('editDeployment' => 'Edit Deployment')) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null);
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$title = "Edit Deployment";
		View::bind_global('title', $title);
		$cssFiles =  array('jquery-ui.css');
		View::bind_global('cssFiles', $cssFiles);
		$jsFiles = array('jquery.js', 'jquery-ui.js');
		View::bind_global('jsFiles', $jsFiles);
                $this->template->sidebar = View::factory('partial/sidebar');
		$errors = array();
                $success = "";
		if ($this->request->method() == 'POST')
                {
                        $formValues = FormUtils::parseForm($this->request->post());
			if (isset($formValues['endDeployment']))
			{
				$this->request->redirect(Route::url('end_deployment', array('id' => $this->request->param('id'))));
			}
			$errors = $this->_validate($formValues);
			if (sizeof($errors) == 0)
			{
				$this->_update($this->request->param('id'), $formValues);
				$success = "Successfully updated Deployment";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
		$submits = array('updateDeployment' => 'Update Deployment');
		if (strtotime($formValues['endDate']) > time())
		{
			$submits['endDeployment'] = "End Deployment";
		}
		$notesFormValues = Controller_Notes::load_from_database('Deployment', $formValues['id'], 'edit');
                $notesFormTemplate = Controller_Notes::load_form_template('edit');
                $this->template->content = FormUtils::drawForm('Deployment', $formTemplate, $formValues, $submits, $errors, $success) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null) . Controller_Notes::generate_form_javascript();
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($this->request->param('id'));
                if (!is_object($deployment))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$title = "Delete Deployment";
		View::bind_global('title', $title);
                $deploymentName = $deployment->name;
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
	                        if (Model_Builder::destroy_deployment($formValues['id']))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted deployment with name $deploymentName.  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete deployment with name $deploymentName.  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Deployment with name $deploymentName was not deleted.  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p>";
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
				'message' => "Are you sure you want to delete deployment with name $deploymentName?",
			);
			$this->template->content = FormUtils::drawForm('Deployment', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->sidebar = View::factory('partial/sidebar');
	}

	public function action_end()
        {
                $this->check_login("systemadmin");
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($this->request->param('id'));
                if (!is_object($deployment))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
                $title = "End Deployment";
                View::bind_global('title', $title);
                $deploymentName = $deployment->name;

                if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
                        if (!empty($formValues['yes']))
                        {
                                if (Model_Builder::end_deployment($formValues['id']))
                                {
                                        $this->template->content = "      <p class=\"success\">Successfully ended deployment with name " . $deploymentName .".  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p></p>";
                                }
                                else
                                {
                                        $this->template->content = "      <p class=\"error\">Could not end deployment with name " . $deploymentName .".  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p>";
                                }
                        }
                        elseif (!empty($formValues['no']))
                        {
                                $this->template->content = "      <p class=\"success\">Deployment with name " . $deploymentName . " was not deleted.  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p>";
                        }
                }
                else
                {
                        $formTemplate = array(
                                'id' => array('type' => 'hidden'),
                                'message' => array('type' => 'message'),
                        );
                        $formValues = array(
                                'id' => $this->request->param('id'),
                                'message' => "Are you sure you want to end deployment with name $deploymentName?",
                        );
                        $this->template->content = FormUtils::drawForm('Deployment', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
                }
	        $this->template->sidebar = View::factory('partial/sidebar');
        }
	
	private function _validate($formValues) 
	{
		$errors = array();
		$validation = Validation::factory($formValues)
			->rule('name', 'not_empty')
			->rule('url', 'url');
                if (!$validation->check())
                {
			$errors = $validation->errors();
                }	
		$validation = Validation::factory($formValues['configuration'])
			->rule('cap', 'not_empty')
                        ->rule('cap', 'digit')
                        ->rule('allowedPorts', 'SownValid::csvlist', array(':value'));
		if (!$validation->check())
                {
                        $errors = $validation->errors();
                }
		$validation = Validation::factory($formValues['location'])
                        ->rule('longitude', 'not_empty')
                        ->rule('longitude', 'numeric')
                        ->rule('latitude', 'not_empty')
                        ->rule('latitude', 'numeric')
                        ->rule('range', 'not_empty')
                        ->rule('range', 'digit');
                if (!$validation->check())
                {
                        $errors = $validation->errors();
                }
		return $errors;
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($id);
                if (!is_object($deployment))
                {
                        throw new HTTP_Exception_404();
                }
		$latest_end_datetime =  Kohana::$config->load('system.default.admin_system.latest_end_datetime');
		$nodes = Doctrine::em()->createQuery("SELECT n.boxNumber FROM Model_NodeDeployment nd JOIN nd.node n WHERE nd.deployment = " . $deployment->id . "ORDER BY nd.startDate DESC")->getResult();
		$formValues = array(
                        'id' => $deployment->id,
                        'name' => $deployment->name,
			'boxNumber' => $nodes[0]['boxNumber'],
			'url' => $deployment->url,
			'startDate' => $deployment->startDate->format('Y-m-d H:i:s'),
			'endDate' => $deployment->endDate->format('Y-m-d H:i:s'),
			'type' => $deployment->type,
			'isDevelopment' => $deployment->isDevelopment,
                        'isPrivate' => $deployment->isPrivate,
			'configuration' => array(
				'firewall' => $deployment->firewall,
				'allowedPorts' => $deployment->allowedPorts,
				'advancedFirewall' => $deployment->advancedFirewall,
				'cap' => $deployment->cap,
			),
			'location' => array(
                                'longitude' => $deployment->longitude,
                                'latitude' => $deployment->latitude,
                                'range' => $deployment->range,
				'address' => $deployment->address,
                        ),
			'admins' => array(
				'currentAdmins' => array(),
				'newAdmin' => '',
			),
		);
		foreach ($deployment->admins as $a => $admin) 
		{
			$formValues['admins']['currentAdmins'][$a] = array(
				'id' => $admin->id,
				'username' => $admin->user->username,
				'startDate' => $admin->startDate->format('Y-m-d H:i:s'),
				'endDate' => $admin->endDate->format('Y-m-d H:i:s'),
				'end' => ( $admin->endDate->getTimestamp() > time() ? 0 : 1 ),
			);
		}
		if ($action == 'view')
                {
                        $formValues['isDevelopment'] = ( $formValues['isDevelopment'] ? 'Yes' : 'No');
			$formValues['isPrivate'] = ( $formValues['isPrivate'] ? 'Yes' : 'No');
		        $formValues['endDate'] = ( $formValues['endDate'] == $latest_end_datetime ? '' : $formValues['endDate']);

			$formValues['configuration']['firewall'] = ( $formValues['configuration']['firewall'] ? 'Yes' : 'No');
			$formValues['configuration']['advancedFirewall'] = ( $formValues['configuration']['advancedFirewall'] ? 'Yes' : 'No') ;
			if ($formValues['configuration']['cap'] == 0 ) 
			{
				$formValues['configuration']['cap'] = "Unlimited";
			}
			else
			{
				$formValues['configuration']['cap'] = $formValues['configuration']['cap'] . " MB";
			}
			$formValues['location']['range'] = $formValues['location']['range'] . " metres";
                }

		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
                        'name' => array('title' => 'Name', 'type' => 'input'),
			'boxNumber' => array('title' => 'Latest Box number', 'type' => 'static'),
			'url' => array('title' => 'URL', 'type' => 'input', 'size' => 70),
			'startDate' => array('title' => 'Started', 'type' => 'statichidden'),
			'endDate' => array('title' => 'Ended', 'type' => 'statichidden'),
			'type' => array('title' => 'Type', 'type' => 'select', 'options' => array('home' => 'home', 'campus' => 'campus')),
			'isDevelopment' => array('title' => 'Development', 'type' => 'checkbox'),
			'isPrivate' => array('title' => 'Private', 'type' => 'checkbox'),
			'configuration' => array(
				'title' => 'Configuration', 
				'type' => 'fieldset', 
				'fields' => array(
					'firewall' => array('title' => 'Firewall', 'type' => 'checkbox', 'hint' => 'Allows web, email, VPN, SSH, FTP and VNC only'),
					'allowedPorts' => array('title' => 'Additionally allowed ports', 'type' => 'input', 'size' => 20, 'hint' => 'Comma-separated (e.g. 123,993,8080)'),
					'advancedFirewall' => array('title' => 'Advanced firewall', 'type' => 'checkbox'),
					'cap' => array('title' => 'Usage cap', 'type' => 'input', 'size' => 5, 'hint' => 'MB'),	
				),
			),
			'location' => array(
				'title' => 'Location',
				'type' => 'fieldset',
				'fields' => array(
					'longitude' => array('title' => "Longitude", 'type' => 'input', 'size' => 10),
					'latitude' => array('title' => "Latitude", 'type' => 'input', 'size' => 10),
					'range' => array('title' => 'Range', 'type' => 'input', 'size' => '3', 'hint' => 'metres'),
					'address' => array('title' => 'Address', 'type' => 'textarea'),
				),
			),
			'admins' => array(
				'title' => 'Administrators',
				'type' => 'fieldset',
				'fields' => array(
					'currentAdmins' => array(
						'title' => '',
						'type' => 'table',
						'fields' => array(
							'id' => array('type' => 'hidden'),
							'username' => array('title' => 'Username', 'type' => 'statichidden'),
							'startDate' => array('title' => 'Start Date', 'type' => 'statichidden'),
							'endDate' => array('title' => 'End Date', 'type' => 'statichidden'),
							'endOrRestart' => array('title' => 'End / Restart', 'type' => 'checkbox'),
						),
					),
					'newAdmin' => array('title' => 'New administrator', 'type' => 'autocomplete', 'autocompleteUrl' => Route::url('user_autocomplete'), 'size' => 50),
						
				),
			),
		);
		if ($action == 'view' ) 
		{
			unset($formTemplate['admins']['fields']['newAdmin']);
			unset($formTemplate['admins']['fields']['currentAdmins']['fields']['endOrRestart']);
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$latest_end_datetime = Kohana::$config->load('system.default.admin_system.latest_end_datetime');
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($id);
		$deployment->name = $formValues['name'];
		$deployment->url = $formValues['url'];
		$deployment->type = $formValues['type'];
		$deployment->isDevelopment = ( isset($formValues['isDevelopment']) ? 1 : 0 );
		$deployment->isPrivate = ( isset($formValues['isPrivate']) ? 1 : 0 );
		$deployment->firewall = ( isset($formValues['configuration']['firewall']) ? 1 : 0 );
		$deployment->allowedPorts = $formValues['configuration']['allowedPorts'];
		$deployment->advancedFirewall = ( isset($formValues['configuration']['advancedFirewall']) ? 1 : 0 );
		$deployment->cap = $formValues['configuration']['cap'];
		$deployment->longitude = $formValues['location']['longitude'];
		$deployment->latitude = $formValues['location']['latitude'];
		$deployment->range = $formValues['location']['range'];
		$deployment->address = $formValues['location']['address'];
		if (isset($formValues['admins']['currentAdmins']))
		{
			foreach ($formValues['admins']['currentAdmins'] as $admin)
			{
				if (isset($admin['endOrRestart']))
				{
					$deploymentAdmin = Doctrine::em()->getRepository('Model_DeploymentAdmin')->find($admin['id']);
					if ($admin['endDate'] == $latest_end_datetime)
					{
						$deploymentAdmin->endDate = new \DateTime();
					}
					else
					{
						$deploymentAdmin->endDate = new \DateTime($latest_end_datetime);
					}
					$deploymentAdmin->save();
				}
			}
		}
		if (!empty($formValues['admins']['newAdmin']))
		{
			$deploymentAdmin = Model_DeploymentAdmin::build($deployment->id, $formValues['admins']['newAdmin']);
			$deploymentAdmin->save();
		}
		$deployment->save();
	}
}
	