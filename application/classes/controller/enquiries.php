<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Enquiries extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Unresponded Enquiries" => Route::url('unresponded_enquiries'), "All Enquiries" => Route::url('enquiries'), "Create Enquiry Type" => Route::url('create_enquiry_type'), "Enquiry Types" => Route::url('enquiry_types'));
		$title = "Enquiries";
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$fields = array(
                        'id' => 'ID',
                        'dateSent' => 'Date Sent',
                        'type' => 'Type',
                        'from' => 'From',
                        'subject' => 'Subject',
                        'responseSummary' => 'Response',
                        'view' => '',
                        'reply' => '',
                );
		$typeparam = $this->request->param('type');
		if (!empty($typeparam)) 
		{
			$type = Doctrine::em()->getRepository('Model_EnquiryType')->find($this->request->param('type'));	
			if (!is_object($type)) 
			{
				throw new HTTP_Exception_404();
			}
			$rows = Doctrine::em()->getRepository('Model_Enquiry')->findBy(array('type' => $type), array('id' => 'DESC'));
			$subtitle = "All " . SOWN::pluralise($type->title);
	
		}
		else 
		{
                	$subtitle = "All Enquiries";
			$rows = Doctrine::em()->getRepository('Model_Enquiry')->findBy(array(), array('id' => 'DESC'));
		}
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
                $objectType = 'enquiry';
                $idField = 'id';
                $content = View::factory('partial/table')
                        ->bind('fields', $fields)
                        ->bind('rows', $rows)
                        ->bind('objectType', $objectType)
                        ->bind('idField', $idField);
                $this->template->content = $content;
	}
	
	public function action_unresponded()
	{
		$this->check_login("systemadmin");
		$fields = array(
                        'id' => 'ID',
                        'dateSent' => 'Date Sent',
                        'from' => 'From',
                        'type' => 'Type',
                        'subject' => 'Subject',
                        'view' => '',
                        'reply' => '',
                );
		$typeparam = $this->request->param('type');
                if (!empty($typeparam))
                {
                        $type = Doctrine::em()->getRepository('Model_EnquiryType')->find($this->request->param('type'));
                        if (!is_object($type))
                        {
                                throw new HTTP_Exception_404();
                        }
			unset($fields['type']);
			$rows = Model_Enquiry::getUnresponded($type);
			$subtitle = "Unresponded " . SOWN::pluralise($type->title);
			$seeall = "<p style=\"text-align: center;\"><a href=\"" . Route::url('type_enquiries', array('type' => $typeparam)) . "\">See All " . SOWN::pluralise($type->title) . "</a></p>";
                }
                else
                {
			$rows = Model_Enquiry::getUnresponded();
                        $subtitle = "Unresponded Enquiries";
			$seeall = "<p style=\"text-align: center;\"><a href=\"" . Route::url('enquiries') . "\">See All Enquiries</a></p>";
                }
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
                $objectType = 'enquiry';
                $idField = 'id';
                $content = $seeall . View::factory('partial/table')
                        ->bind('fields', $fields)
                        ->bind('rows', $rows)
                        ->bind('objectType', $objectType)
                        ->bind('idField', $idField);
                $this->template->content = $content;
	}

	public function action_view()
	{
	}

	public function action_reply()
        {
        }

	public function action_types()
	{
		$this->check_login("systemadmin");
		$subtitle = "Enquiry Types";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'enquiry_type_title' => 'Title',
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

	public function action_create_type()
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
                        	$success = "Successfully created enquiry type \"{$formValues['title']}\".";
 
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

	public function action_edit_type()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Enquiry Type " . $this->request->param('id');
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
				$this->_update_type($this->request->param('id'), $formValues);
				$success = "Successfully updated enquiry type";
				$formValues = $this->_load_type_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_type_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_type_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_enquiry_type', $formTemplate, $formValues, array('updateEnquiryType' => 'Update Enquiry Type'), $errors, $success);
        }

	public function action_delete_type()
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

	private function _load_type_from_database($id, $action = 'edit')
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

	private function _load_type_form_template($action = 'edit')
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

	private function _update_type($id, $formValues)
	{
		$enquiryType = Doctrine::em()->getRepository('Model_EnquiryType')->findOneById($id);
		$enquiryType->title = $formValues['title'];
		$enquiryType->description = $formValues['description'];
		$enquiryType->email = $formValues['email'];
		$enquiryType->save();
	}
}
	
