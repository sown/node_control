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
			'startDate' => 'Start Date',
			'endDate' => 'End Date',
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
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$deployment = Model_Builder::create_deployment();
				$url = Route::url("view_deployment", array($deployment, array($deployment->id)));
                        	$success = "Successfully created Deployment with name: <a href=\"$url\">" . $deployment->name . "</a>.";
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array();
			
		}
		$formTemplate = array();
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm($formTemplate, $formValues, array('createDeployment' => 'Create Deployment'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		$title = "View Deployment";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm($formTemplate, $formValues, NULL);
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$title = "Edit Deployment";
		View::bind_global('title', $title);
                $this->template->sidebar = View::factory('partial/sidebar');
		$errors = array();
                $success = "";
		if ($this->request->method() == 'POST')
                {
                        $formValues = FormUtils::parseForm($this->request->post());
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
                $this->template->content = FormUtils::drawForm($formTemplate, $formValues, array('updateDeployment' => 'Update Deployment'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
                $success = "";
		$title = "Delete Deployment";
		View::bind_global('title', $title);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
	                        if (Model_Builder::destroy_deployment($formValues['id']))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted deployment with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete deployment with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Deployment with ID " . $formValues['id'] . " was not deleted.  Go back to <a href=\"".Route::url('deployments')."\">deployments list</a>.</p>";
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
				'message' => "Are you sure you want to delete deployment with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm($formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
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
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->findOneById($id);
                $formValues = array();
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array();
		if ($action == 'view' ) 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->findOneById(id);
		$deployment->save();
	}
}
	
