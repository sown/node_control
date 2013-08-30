<?php defined('SYSPATH') or die('No direct script access.');

class Controller_EnquiryTypes extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Enquiry Type" => Route::url('create_enquiry_type'), "Enquiry Types" => Route::url('enquiry_types'));
		$title = "Enquiry Types";
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Enquiry Types";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'title' => 'Title',
			'description' => 'Description',
			'email' => 'Email',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_EnquiryType')->findAll();
		$objectType = 'enquiry_type';
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
		$subtitle = "Create Enquiry Type";
		View::bind_global('subtitle', $subtitle);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$enquiryType = Model_EnquiryType::build($formValues['title'], $formValues['description'], $formValues['email']);
				$enquiryType->save();
				$url = Route::url('enquiry_types');
                        	$success = "Successfully created enquiry type \"{$formValues['title']}\".  Back to <a href=\"$url\">Enquiry Types List</a>.";
 
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array(
				'title' => '',
                                'description' => '',
                                'email' => 'committee@sown.org.uk',
                        );
	
		}
		$formTemplate = array(
                        'title' => array('title' => 'Title', 'type' => 'input'),
                        'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
                        'email' => array('title' => 'Email', 'type' => 'input'),
                );
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_enquiry_type', $formTemplate, $formValues, array('createEnquiryType' => 'Create Enquiry Type'), $errors, $success);
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$title = "Edit Enquiry Type " . $this->request->param('id');
		View::bind_global('title', $title);
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
				$success = "Successfully updated enquiry type";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_enquiry_type', $formTemplate, $formValues, array('updateEnquiryType' => 'Update Enquiry Type'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$enquiryType = Doctrine::em()->getRepository('Model_EnquiryType')->findOneById($this->request->param('id'));
		$enquiryTypeTitle = $enquiryType->title;
                if (!is_object($enquiryType))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Enquiry Type " . $this->request->param('id') . " (" . $enquiryTypeTitle . ")";
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'EnquiryType';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted enquiry type '" . $enquiryTypeTitle ."'.</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete enquiry type '" . $enquiryTypeTitle ."'.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Enquiry Type with ID " . $enquiryTypeTitle . " was not deleted.</p>";
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
				'message' => "Are you sure you want to delete enquiry type '" . $enquiryTypeTitle . "'?",
			);
			$this->template->content = FormUtils::drawForm('delete_enquiry_type', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
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
		$enquiryType = Doctrine::em()->getRepository('Model_EnquiryType')->findOneById($id);
                if (!is_object($enquiryType))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
			'id' =>	$enquiryType->id,
			'title' => $enquiryType->title,
			'description' => $enquiryType->description,
			'email' => $enquiryType->email,
		);
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
			'title' => array('title' => 'Title', 'type' => 'input'),
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
			'email' => array('title' => 'Email', 'type' => 'input'),
		);
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$enquiryType = Doctrine::em()->getRepository('Model_EnquiryType')->findOneById($id);
		$enquiryType->title = $formValues['title'];
		$enquiryType->description = $formValues['description'];
		$enquiryType->email = $formValues['email'];
		$enquiryType->save();
	}
}
	
