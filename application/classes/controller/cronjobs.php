<?php defined('SYSPATH') or die('No direct script access.');

class Controller_CronJobs extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Cron Job" => Route::url('create_cron_job'), "Enabled Cron Jobs" => Route::url('cron_jobs_enabled'), "All Cron Jobs" => Route::url('cron_jobs'),);
		$title = 'Cron Jobs';
		View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Cron Jobs";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'creator' => 'Creator',
			'username' => 'Username',
			'server' => 'Server',
			'description' => 'description',
			'disabled' => '',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_CronJob')->findAll();
		$objectType = 'cron_job';
                $idField = 'id';
		$content = View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_enabled()
	{
		$this->check_login("systemadmin");
                $subtitle = "Enabled Cron Jobs";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
                        'creator' => 'Creator',
                        'username' => 'Username',
                        'server' => 'Server',
                        'description' => 'description',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
                $rows = Doctrine::em()->getRepository('Model_CronJob')->findByDisabled(0);
                $objectType = 'cron_job';
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
		$subtitle = "Create Cron Job";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			if (!isset($formValues['disabled']))
                        {
                                $formValues['disabled'] = 0;
                        }
			if (!isset($formValues['required']))
			{
				$formValues['required'] = 0;
			}
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$cronJob = Model_CronJob::build($formValues['description'], $formValues['username'], $formValues['server'], $formValues['creator'], $formValues['command'], $formValues['disabled'], $formValues['required'], $formValues['misc']);
				$cronJob->save();
				$url = Route::url('view_cron_job', array('id' => $cronJob->id));
                        	$success = "Successfully created Cron Job with ID: <a href=\"$url\">" . $cronJob->id . "</a>.";
 
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array(
				'description' => '',
	                        'username' => '',
        	                'server' => '',
                	        'creator' => '',
                        	'command' => '',
	                        'disabled' => 0,
        	                'required' => 1,
                	        'misc' => '',
			);
		}
		$formTemplate = array(
			'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
                        'username' => array('title' => 'Username', 'type' => 'input', 'size' => 20),
                        'server' => array('title' => 'Server', 'type' => 'input'),
                        'creator' => array('title' => 'Creator', 'type' => 'input', 'size' => 20),
                        'command' => array('title' => 'Command', 'type' => 'input', 'size' => 100),
                        'disabled' => array('title' => 'Disabled', 'type' => 'checkbox'),
                        'required' => array('title' => 'Required', 'type' => 'checkbox'),
                        'misc' => array('title' => 'Misc', 'type' => 'textarea'),
		);
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_cron_job', $formTemplate, $formValues, array('createObject' => 'Create Cron Job'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_cron_job', array('id' => $this->request->param('id'))));
                }
		$subtitle = "View Cron Job " . $this->request->param('id') ;
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_cron_job', $formTemplate, $formValues, array('editCronJob' => 'Edit Cron Job'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Cron Job " . $this->request->param('id');
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
				$success = "Successfully updated Cron Job";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_cron_job', $formTemplate, $formValues, array('updateCronJob' => 'Update Cron Job'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$object = Doctrine::em()->getRepository('Model_CronJob')->findOneById($this->request->param('id'));
                if (!is_object($object))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Cron Job " . $this->request->param('id');
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'CronJob';
		                if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted Cron Job with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('cron_jobs')."\">All Cron Jobs</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete Cron Job with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('cron_jobs')."\">All Cron Jobs</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Cron Job with ID " . $formValues['id'] . " was not deleted.  Go back to <a href=\"".Route::url('cron_jobs')."\">All Cron Jobs</a>.</p>";
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
				'message' => "Are you sure you want to delete Cron Job with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_cron_job', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
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
		$cronJob = Doctrine::em()->getRepository('Model_CronJob')->findOneById($id);
                if (!is_object($cronJob))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
			'id' => $cronJob->id,
                        'description' => $cronJob->description,
                        'username' => $cronJob->username,
                        'server' => $cronJob->server,
                        'creator' => $cronJob->creator,
                        'command' => $cronJob->command,
                        'disabled' => $cronJob->disabled,
                        'required' => $cronJob->required,
                        'misc' => $cronJob->misc,
                        'createdAt' => $cronJob->createdAt->format('Y-m-d H:i:s'),
                        'updatedAt' => $cronJob->updatedAt->format('Y-m-d H:i:s'),

		);
		if ($action == 'view')
                {
			$formValues['disabled'] = ( $formValues['disabled'] ? 'Yes' : 'No');
                        $formValues['required'] = ( $formValues['required'] ? 'Yes' : 'No');
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
                        'id' =>  array('type' => 'hidden'),
                        'description' => array('title' => 'Description', 'type' => 'input', 'size' => 100),
                        'username' => array('title' => 'Username', 'type' => 'input', 'size' => 20),
                        'server' => array('title' => 'Server', 'type' => 'input'),
                        'creator' => array('title' => 'Creator', 'type' => 'input', 'size' => 20),
                        'command' => array('title' => 'Command', 'type' => 'input', 'size' => 100),
                        'disabled' => array('title' => 'Disabled', 'type' => 'checkbox'),
                        'required' => array('title' => 'Required', 'type' => 'checkbox'),
                        'misc' => array('title' => 'Misc', 'type' => 'textarea'),
                        'createdAt' => array('title' => 'Created At', 'type' => 'static'),
			'updatedAt' => array('title' => 'Updated At', 'type' => 'static'),
                );
                if ($action == 'view')
                {
                        $formTemplate = FormUtils::makeStaticForm($formTemplate);
                }
                return $formTemplate;

	}

	private function _update($id, $formValues)
	{
		$object = Doctrine::em()->getRepository('Model_CronJob')->findOneById($id);
		$object->save();
	}
}
	