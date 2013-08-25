<?php defined('SYSPATH') or die('No direct script access.');

class Controller_ObjectTemplate extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array();
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$title = "[OBJECT] List";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_[OBJECT]')->findAll();
		$objectType = '[OBJECT]';
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
		$title = "Create [OBJECT]";
		View::bind_global('title', $title);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$object = Model_Object::build();
				$object->save();
				$url = Route::url('view_[OBJECT]', array('id' => $object->id));
                        	$success = "Successfully created object with ID: <a href=\"$url\">" . $object->id . "</a>.";
 
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
		$this->template->content = FormUtils::drawForm('create_[OBJECT]', $formTemplate, $formValues, array('createObject' => 'Create [OBJECT]'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_[OBJECT]', array('id' => $this->request->param('id'))));
                }
		$title = "View [OBJECT]";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_[OBJECT]', $formTemplate, $formValues, array('editObject' => 'Edit [OBJECT]'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$title = "Edit [OBJECT]";
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
				$success = "Successfully updated [OBJECT]";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_[OBJECT]', $formTemplate, $formValues, array('updateObject' => 'Update [OBJECT]'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$object = Doctrine::em()->getRepository('Model_[OBJECT]')->findOneById($this->request->param('id'));
                if (!is_object($object))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$title = "Delete [OBJECT]";
		View::bind_global('title', $title);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
	                        if (Model_Builder::destroy_object($formValues['id']))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted [OBJECT] with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('objects')."\">[OBJECT] list</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete [OBJECT] with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('objects')."\">[OBJECT] list</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">[OBJECT] with ID " . $formValues['id'] . " was not deleted.  Go back to <a href=\"".Route::url('objects')."\">[OBJECT] list</a>.</p>";
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
				'message' => "Are you sure you want to delete [OBJECT] with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_[OBJECT]', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
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
		$object = Doctrine::em()->getRepository('Model_[OBJECT]')->findOneById($id);
                if (!is_object($object))
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
		$object = Doctrine::em()->getRepository('Model_[OBJECT]')->findOneById($id);
		$object->save();
	}
}
	
