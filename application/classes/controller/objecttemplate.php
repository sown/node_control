<?php defined('SYSPATH') or die('No direct script access.');

class Controller_ObjectTemplate extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create OBJECT" => Route::url('create_OBJECT'), "All OBJECTS" => Route::url('OBJECTS'));
                $title = 'OBJECTS';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All OBJECTS";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_OBJECT')->findAll();
		$objectType = 'OBJECT';
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
		$subtitle = "Create OBJECT";
		View::bind_global('subtitle', $subtitle);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$OBJECT = Model_OBJECT::build();
				$OBJECT->save();
				$url = Route::url('view_OBJECT', array('id' => $OBJECT->id));
                        	$success = "Successfully created OBJECT with ID: <a href=\"$url\">" . $OBJECT->id . "</a>.";
 
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
		$this->template->content = FormUtils::drawForm('create_OBJECT', $formTemplate, $formValues, array('createOBJECT' => 'Create OBJECT'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_OBJECT', array('id' => $this->request->param('id'))));
                }
		$subtitle = "View OBJECT";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_OBJECT', $formTemplate, $formValues, array('editOBJECT' => 'Edit OBJECT'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit OBJECT";
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
				$success = "Successfully updated OBJECT";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_OBJECT', $formTemplate, $formValues, array('updateOBJECT' => 'Update OBJECT'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$OBJECT = Doctrine::em()->getRepository('Model_OBJECT')->findOneById($this->request->param('id'));
                if (!is_object($OBJECT))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete OBJECT";
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'OBJECT';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted OBJECT with ID " . $formValues['id'] .".</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete OBJECT with ID " . $formValues['id'] .".</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">OBJECT with ID " . $formValues['id'] . " was not deleted.</p>";
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
				'message' => "Are you sure you want to delete OBJECT with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_OBJECT', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
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
		$OBJECT = Doctrine::em()->getRepository('Model_OBJECT')->findOneById($id);
                if (!is_object($OBJECT))
                {
                        throw new HTTP_Exception_404();
                }
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
		$OBJECT = Doctrine::em()->getRepository('Model_OBJECT')->findOneById($id);
		$OBJECT->save();
	}
}
	
