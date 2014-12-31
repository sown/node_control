<?php defined('SYSPATH') or die('No direct script access.');

class Controller_NodeRequests extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("All Node Requests" => Route::url('node_requests'), "Pending Node Requests" => Route::url('pending_node_requests'));
                $title = 'Node Requests';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Node Requests";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'requester' => 'Requester',
			'requestedDate' => 'Requested',
			'address' => 'Address',
			'location' => 'Location',
			'status' => 'Status',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_NodeRequest')->findBy(array(), array('requestedDate'=>'DESC'));
		$objectType = 'node_request';
                $idField = 'id';
		$content = View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_pending()
        {
		$this->check_login("systemadmin");
                $subtitle = "Pending Node Requests";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
			'requester' => 'Requester',
                        'requestedDate' => 'Requested',
                        'address' => 'Address',
                        'location' => 'Location',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_NodeRequest')->findBy(array('approved' => NULL), array('requestedDate'=>'DESC'));
                $objectType = 'node_request';
                $idField = 'id';
                $content = View::factory('partial/table')
                        ->bind('fields', $fields)
                        ->bind('rows', $rows)
                        ->bind('objectType', $objectType)
                        ->bind('idField', $idField);
                $this->template->content = $content;
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_node_request', array('id' => $this->request->param('id'))));
                }
		$subtitle = "View Node Request";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_node_request', $formTemplate, $formValues, array('editNodeRequest' => 'Edit Node Request'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Node Request";
		View::bind_global('subtitle', $subtitle);
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
				$success = "Successfully updated Node Request";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_node_request', $formTemplate, $formValues, array('updateNodeRequest' => 'Update Node Request'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$nodeRequest = Doctrine::em()->getRepository('Model_NodeRequest')->findOneById($this->request->param('id'));
                if (!is_object($nodeRequest))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Node Request";
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'NodeRequest';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted Node RequestT with ID " . $formValues['id'] .".</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete Node Request with ID " . $formValues['id'] .".</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Node Request with ID " . $formValues['id'] . " was not deleted.</p>";
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
				'message' => "Are you sure you want to delete Node Request with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_node_request', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
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
		return $errors;
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$nodeRequest = Doctrine::em()->getRepository('Model_NodeRequest')->findOneById($id);
                if (!is_object($nodeRequest))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
			'id' => $nodeRequest->id,
                        'name' => $nodeRequest->name,
                        'email' => $nodeRequest->email,
                        'contactNumber' => $nodeRequest->contactNumber,
			'course' => $nodeRequest->course,
			'year' => $nodeRequest->year,
			'houseNumber' => $nodeRequest->houseNumber,
			'street' => $nodeRequest->street,
			'postcode' => $nodeRequest->postcode,
			'facilities' => $nodeRequest->facilities,
                        'requestedDate' => $nodeRequest->requestedDate->format('Y-m-d H:i:s'),
                        'latitude' => $nodeRequest->latitude,
                        'longitude' => $nodeRequest->longitude,
			'approved' => $nodeRequest->approved,
                        'notes' => $nodeRequest->notes,
			'deployment' => ""
		);
		if ($nodeRequest->deploymentId > 0)
		{
			$formValues['deployment'] = $nodeRequest->deployment->name ."(#".$nodeRequest->deployment->node->boxNumber.")";
		}
		elseif ($nodeRequest->deploymentId == -1)
		{
			$formValues['deployment'] = "OLD DEPLOYMENT";
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
                        'name' => array('title' => 'Name', 'type' => 'input'),
                        'email' => array('title' => 'Email', 'type' => 'input'),
                        'contactNumber' => array('title' => 'Contact Number', 'type' => 'input'),
                        'course' => array('title' => 'Course', 'type' => 'input'),
                        'year' => array('title' => 'Year', 'type' => 'input'),
                        'houseNumber' => array('title' => 'House Number', 'type' => 'input'),
                        'street' => array('title' => 'Street', 'type' => 'input'),
                        'postcode' => array('title' => 'Postcode', 'type' => 'input'),
                        'facilities' => array('title' => 'Amentities', 'type' => 'textarea'),
                        'requestedDate' => array('title' => 'Requested On', 'type' => 'static'),
                        'latitude' => array('title' => 'Latitude', 'type' => 'input'),
                        'longitude' => array('title' => 'Longitude', 'type' => 'input'),
                        'approved' => array('title' => 'Status', 'type' => 'select', 'options' => array('' => 'Undecided', '0' => 'Rejected', '1' => 'Accepted', '2' => 'Deployed', '-1' => 'Returned', '-2' => 'Lost')),
                        'notes' => array('title' => 'Notes', 'type' => 'textarea'),
                        'deployment' => array('title' => 'Deployment', 'type' => 'static'),
		);
		if ($action == 'view' ) 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$nodeRequest = Doctrine::em()->getRepository('Model_NodeRequest')->findOneById($id);
		$nodeRequest->name = $formValues['name'];
                $nodeRequest->email = $formValues['email'];
                $nodeRequest->contactNumber = $formValues['contactNumber'];
		$nodeRequest->course = $formValues['course'];
		$nodeRequest->year = $formValues['year'];		
		$nodeRequest->houseNumber = $formValues['houseNumber'];
		$nodeRequest->street = $formValues['street'];
		$nodeRequest->postcode = $formValues['postcode'];
		$nodeRequest->facilities = $formValues['facilities'];
		$nodeRequest->latitude = $formValues['latitude'];
		$nodeRequest->longitude = $formValues['longitude'];
		if ($formValues['approved'] === '')
		{
			$nodeRequest->approved = NULL;
		}
		else 
		{			
			$nodeRequest->approved = $formValues['approved'];
		}
		$nodeRequest->notes = $formValues['notes'];
		$nodeRequest->save();
	}
}
	
