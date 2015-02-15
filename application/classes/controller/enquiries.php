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
		$this->check_login("systemadmin");
                if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('reply_enquiry', array('id' => $this->request->param('id'))));
                }
                $subtitle = "View Enquiry";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
                $formValues = $this->_load_from_database($this->request->param('id'), 'view');
                $formTemplate = $this->_load_form_template('view');
                $this->template->content = FormUtils::drawForm('view_enquiry', $formTemplate, $formValues, array('reply' => 'Reply'));
	}

	public function action_reply()
        {
		$this->check_login("systemadmin");
		$id = $this->request->param('id');
                $enquiry = Doctrine::em()->getRepository('Model_Enquiry')->findOneById($id);
                if (!is_object($enquiry))
                {
                        throw new HTTP_Exception_404();
                }
                $subtitle = "Reply To Enquiry";
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
				//print_r($formValues);
				if (!empty($formValues['sendResponse'])) 
				{
					$result = $this->_send_response($enquiry, $formValues);
				}	
				elseif (!empty($formValues['markAsHandled']))
				{
					$result = $this->_mark_as_handled($enquiry, $formValues, false);
				}
				elseif (!empty($formValues['markAsHandledQuietly']))
                                {
					$result = $this->_mark_as_handled($enquiry, $formValues, true);
                                }
				elseif (!empty($formValues['acknowledge']))
                                {
					$result = $this->_acknowledge($enquiry, $formValues);
                                }
				elseif (!empty($formValues['unmarkAsHandled']))
				{	
					$result = $this->_unmark_as_handled($enquiry);
				}
                        }
			if (isset($result['success'])) 
			{
				$success = $result['success'];
				$formValues = $this->_load_from_database($id, 'reply');
			}
			elseif (isset($result['failure']))
			{
				$errors = array($result['failure']);		
			}
                }
                else
                {
                        $formValues = $this->_load_from_database($id, 'reply');
                }
                $formTemplate = $this->_load_form_template('reply');
		$enquiry = Doctrine::em()->getRepository('Model_Enquiry')->findOneById($id);
		$response_summary = $enquiry->responseSummary;
		if (empty($response_summary)) 
		{
			$buttons = array('sendResponse' => 'Send Response', 'markAsHandled' => 'Mark As Handled', 'markAsHandledQuietly' => 'Mark As Handled Quietly', 'acknowledge' => 'Acknowledge');
		}
		else {
			$formTemplate['responsefieldset']['fields']['response']['type'] = 'static';
			$formTemplate['responsefieldset']['fields']['responseSummary']['type'] = 'static';
			$formValues['responsefieldset']['response'] = str_replace("\n", "<br/>", $formValues['responsefieldset']['response']);
			$formValues['responsefieldset']['responseSummary'] = str_replace("\n", "<br/>", $formValues['responsefieldset']['responseSummary']);
			
			$buttons = array('unmarkAsHandled' => 'Unmark As Handled');
		}
		$this->template->content = FormUtils::drawForm('reply_enquiry', $formTemplate, $formValues, $buttons, $errors, $success);
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
			'disabled' => '',
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
				if (!isset($formValues['disabled'])) 
				{
					$formValues['disabled'] = 0;
				}
				$enquiryType = Model_EnquiryType::build($formValues['title'], $formValues['description'], $formValues['enabledMessage'], $formValues['email'], $formValues['disabled'], $formValues['disabledMessage']);
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
				'enabledMessage' => '',
				'disabled' => 0,
				'disabledMessage' => 'Enquiries of this type are not currently available.',
                        );
	
		}
		$formTemplate = array(
                        'title' => array('title' => 'Title', 'type' => 'input', 'size' => 50),
                        'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
                        'email' => array('title' => 'Email', 'type' => 'input', 'size' => 50),
			'enabledMessage' => array('title' => 'Enabled Message', 'type' => 'textarea', 'rows' => 6, 'cols' => 100),
			'disabled' => array('title' => 'Disabled', 'type' => 'checkbox'),
			'disabledMessage' => array('title' => 'Disabled Message', 'type' => 'textarea', 'rows' => 3, 'cols' => 100),
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

	private function _load_from_database($id, $action = 'reply')
        {
                $enquiry = Doctrine::em()->getRepository('Model_Enquiry')->findOneById($id);
                if (!is_object($enquiry))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
                        'id' => $enquiry->id,
			'type' => $enquiry->type->title,
                        'dateSent' => $enquiry->dateSent->format('Y-m-d H:i:s'),
                        'from' => $enquiry->fromName . " &lt;" . $enquiry->fromEmail . "&gt; (IP: " . $enquiry->ipAddress .")",
			'subject' => $enquiry->subject,
			'message' => str_replace("\n", "<br/>", $enquiry->message),
			'response' => $enquiry->response,
			'responseSummary' => $enquiry->responseSummary,
			'acknowledgedUntil' => '', 
                );
		if ($enquiry->acknowledgedUntil !== NULL)
		{
			$formValues['acknowledgedUntil'] = $enquiry->acknowledgedUntil->format('Y-m-d H:i:s');
		}
		if ($action == 'reply')
                {
                        $formValues['responsefieldset'] = array(
                                'from' => $enquiry->type->email,
                                'to' => $enquiry->fromName . " &lt;" . $enquiry->fromEmail . "&gt;",
                                'bcc' => $enquiry->type->email,
			);
			$response_summary = $enquiry->responseSummary;
			if (empty($response_summary)) 
			{
				$formValues['responsefieldset']['response'] = "=== Original Message ===\n> " . str_replace("\n", "\n> ", $enquiry->message);
                                $formValues['responsefieldset']['responseSummary'] = '';
                        }
			else
			{
				$formValues['responsefieldset']['response'] = $enquiry->response;
                                $formValues['responsefieldset']['responseSummary'] = $enquiry->responseSummary;
			}
                        unset($formValues['response']);
			unset($formValues['responseSummary']);
                }

		if (!empty($enquiry->acknowledgedUntil))
		{
			$formValues['acknowledgedUntil'] = $enquiry->acknowledgedUntil->format('Y-m-d H:i:s');
		}
                return $formValues;
        }

        private function _load_form_template($action = 'reply')
        {
                $formTemplate = array(
                        'id' => array('title' => 'ID', 'type' => 'statichidden'),
                        'type' => array('title' => 'Type', 'type' => 'static'),
                        'dateSent' => array('title' => 'Date Sent', 'type' => 'static'),
                        'from' => array('title' => 'From', 'type' => 'static'),
			'subject' => array('title' => 'Subject', 'type' => 'static'),
			'message' => array('title' => 'Message', 'type' => 'static'),
			'response' => array('title' => 'Response', 'type' => 'static'),
			'responseSummary' => array('title' => 'Response&nbsp;Summary', 'type' => 'static'),
			'acknowledgedUntil' => array('title' => 'Acknowledged&nbsp;Until', 'type' => 'static'),
                );
		if ($action == 'reply')
		{
			$formTemplate['responsefieldset'] = array('title' => 'Response', 'type' => 'fieldset', 'fields' => array(
                                'from' => array('title' => 'From', 'type' => 'static'),
                                'to' => array('title' => 'To', 'type' => 'static'),
				'bcc' => array('title' => 'BCC', 'type' => 'static'),
                                'response' => array('title' => '', 'type' => 'textarea', 'rows' => 15, 'cols' => 100),
                                'responseSummary' => array('title' => 'Response<br/>Summary', 'type' => 'textarea', 'rows' => 3, 'cols' => 100),
                        ));
			unset($formTemplate['response']);
			unset($formTemplate['responseSummary']);
		}
                if ($action == 'view')
                {
                        return FormUtils::makeStaticForm($formTemplate);
                }
                return $formTemplate;
        }

	private function _send_response($enquiry, $formValues)
	{
		$response_summary = "Responded to by " . Auth::instance()->get_user() ." at " . date('H:i:s - D j M Y');
		if ($formValues['responsefieldset']['responseSummary'])
		{
			$response_summary .= "\n\n".$formValues['responsefieldset']['responseSummary'];
		}
            	$enquiry->responseSummary = stripslashes(str_replace("\\n","
",$response_summary));
            	$enquiry->response = stripslashes(str_replace("\\n","
",$formValues['responsefieldset']['response']));
		try 
		{
			$enquiry->save();
			mail($enquiry->fromEmail,"Re: [sown-contactus] ".$enquiry->subject,$enquiry->response,"From: SOWN <".$enquiry->type->email."> \nBcc: SOWN <".$enquiry->type->email.">");
			return array('success' => "Successfully sent response");
		} 
		catch (Exception $e) 
		{
                  	return array('failure' => "ERROR: Could not send response - " . $e);
            	}
		return array('failure' => "ERROR: Could not send response.");
	}

	private function _mark_as_handled($enquiry, $formValues, $quiet = false)
        {
		$response_summary = "Marked as handled by " . Auth::instance()->get_user() . " at " . date('H:i:s - D j M Y');
		if ($formValues['responsefieldset']['responseSummary']) 
		{
			$response_summary .= "\n\n".$formValues['responsefieldset']['responseSummary'];
		}
            	$enquiry->responseSummary = str_replace("\\n","
",$response_summary);
		try 
		{
			$enquiry->save();
                  	if (!$quiet) 
			{
				mail($enquiry->type->email,"Re: [sown-contactus] ".$enquiry->subject,$enquiry->responseSummary,"From: SOWN No Reply <NO-REPLY@sown.org.uk>");
			}
                  	return array('success' => "Successfully marked as handled");
            	} 
		catch (Exception $e) 
		{
			return array('failure' => "ERROR: Could not mark as handled - " . $e);
            	}
		return array('failure' => "ERROR: Could not mark as handled.");
        }

	private function _unmark_as_handled($enquiry)
	{
		$enquiry->responseSummary = "";
		$enquiry->response = "";
		try
		{ 
			$enquiry->save();
                  	return array("success" => "Successfully unmarked as handled");
		} 
		catch (Exception $e) 
		{
                	return array("failure" => "ERROR: Could not unmark as handled - " . $e);
		}
		return array("failure" => "ERROR: Could not unmark as handled.");
	}

	private function _acknowledge($enquiry)
	{
		$enquiry->acknowledgedUntil = new \Datetime(date("Y-m-d H:i:s", time()+(7*24*60*60)));
		try 
		{
			$enquiry->save();
			return array("success" => "Successfully acknowledged enquiry for a week.  It will not appear in Nagios IRC/email alerts until then.");
            	} 
		catch (Exception $e) 
		{
                	return array("failure" => "ERROR: Could not acknowledge enquiry - " . $e);
            	}
		return array("failure" => "ERROR: Could not acknowledge enquiry.");		
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
			'enabledMessage' => $enquiryType->enabledMessage,
			'disabled' => $enquiryType->disabled,
			'disabledMessage' => $enquiryType->disabledMessage,
		);
		return $formValues;
	}

	private function _load_type_form_template($action = 'edit')
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
			'title' => array('title' => 'Title', 'type' => 'input', 'size' => 50),
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
			'email' => array('title' => 'Email', 'type' => 'input', 'size' => 50),
			'enabledMessage' => array('title' => 'Enabled Message', 'type' => 'textarea', 'rows' => 6, 'cols' => 100),
			'disabled' => array('title' => 'Disabled', 'type' => 'checkbox'),
			'disabledMessage' => array('title' => 'Disabled Message', 'type' => 'textarea', 'rows' => 3, 'cols' => 100),
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
		$enquiryType->enabledMessage = $formValues['enabledMessage'];
		if (!isset($formValues['disabled'])) 
		{
                	$enquiryType->disabled = 0;
		}
		else
		{
			$enquiryType->disabled = 1;
		}
		$enquiryType->disabledMessage = $formValues['disabledMessage'];
		$enquiryType->save();
	}
}
	
