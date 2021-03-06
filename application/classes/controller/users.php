<?php defined('SYSPATH') or die('No direct script access.');

use Doctrine\ORM\EntityNotFoundException;

class Controller_Users extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array('Create User' => Route::url('create_user'), 'Create External User' => Route::url('create_external_user'), 'User List' => Route::url('users'));
		parent::before();
	}

	public function action_autocomplete()
	{
		$this->auto_render = FALSE;
		$this->check_login("systemadmin");
		$userString = $this->request->query('text');
		$qb = Doctrine::em()->getRepository('Model_User')->createQueryBuilder('u');
                $qb->where('u.username LIKE :userString');
                $qb->orderBy('u.username', 'ASC');
                $qb->setParameter(':userString', "$userString%");
                $users = $qb->getQuery()->getResult();
                $options = array();
                foreach ($users as $u => $user)
                {
                	$options['items'][] = array("id" => $user->id, "label" => $user->username);
                }
		echo json_encode($options);
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$title = "User List";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'username' => 'Username',
			'name' => 'Name',
			'email' => 'Email',
			'isSystemAdmin' => 'Admin',
			'numAccounts' => 'No. Accounts',
			'latestNote' => 'Latest Note',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_User')->findAll();
		foreach ($rows as $r => $row)
		{
			$rows[$r]->isSystemAdmin = ( $row->isSystemAdmin ? 'Yes' : 'No') ;
		}
		
		$objectType = 'user';
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
		$title = "Create User";
		View::bind_global('title', $title);
		$errors = array();
		$success = "";
		$domain = Kohana::$config->load('system.default.domain');
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$usernameOnly = $formValues['username'];
			$formValues['username'] .= "@$domain";
			$validation = Validation::factory($formValues)
				->rule('username', 'not_empty')
				->rule('username', 'email')
				->rule('username', 'Model_User::uniqueUsername', array(':value'))
				->rule('username', 'RadAcctUtils::UserNotExists', array(':value'))
				->rule('name', 'not_empty')
				->rule('email', 'not_empty')
				->rule('email', 'email')
				->rule('email', 'Model_User::uniqueEmail', array(':value'));
			if (!empty($formValues['password']) || !empty($formValues['confirmPassword'])) 
			{
				$validation->rule('password', 'not_empty')
					->rule('password', 'min_length', array(':value', '8'))
					->rule('confirmPassword', 'matches', array(':validation', 'confirmPassword', 'password'))
					->rule('password','Model_User::uncommonPassword', array(':value'));
			}
			if ($validation->check())
        		{
				if (empty($formValues['password']))
				{
					$formValues['password'] = RadAcctUtils::generateRandomString();
				}
				if (RadAcctUtils::AddUser($formValues['username'], $formValues['password']))
				{
					$user = Model_User::build($formValues['username'], $formValues['name'], $formValues['email']);
					$user->save();
					if (!empty($user->id))
					{
						RadAcctUtils::DeleteUser($formValues['username']);
						$success = "Failed to create user with username: {$formvalues['username']}";
					}
				}
				$url = Route::url('view_user', array('id' => $user->id));
				if (empty($formValues['confirmPassword']))
				{
					$success = "Successfully created user with username '<a href=\"$url\">" . $user->username . "</a>' and password '{$formValues['password']}'.";
				}
				else 
				{
	                      		$success = "Successfully created user with username '<a href=\"$url\">" . $user->username . "</a>'."; 
				}
        		}
			else 
			{
				$errors = $validation->errors();
			}
			$formValues['username'] = $usernameOnly;
                }
		else
		{
			$formValues = array(
				'username' => '',
				'name' => '',
				'email' => '',
				'password' => '',
				'confirmPassword' => '',
			);	
		}
		$formTemplate = array(
			'username' => array('title' => 'Username', 'type' => 'input', 'hint' => "@$domain"),
			'name' => array('title' => 'Full Name', 'type' => 'input'),
			'email' => array('title' => 'Email', 'type' => 'input'),
			'password' => array('title' => 'Password', 'type' => 'password'),
			'confirmPassword' => array('title' => 'Confirm password', 'type' => 'password'),
		);
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('User', $formTemplate, $formValues, array('createUser' => 'Create User'), $errors, $success);
	}

	public function action_create_external()
	{
		$this->check_login("systemadmin");
                $title = "Create External User";
                View::bind_global('title', $title);
                $errors = array();
                $success = "";
                if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
                        $validation = Validation::factory($formValues)
                                ->rule('username', 'not_empty')
                                ->rule('username', 'email')
				->rule('username', 'Model_User::validExternalDomain', array(':value'))
                                ->rule('username', 'Model_User::uniqueUsername', array(':value'))
				->rule('username', 'Model_User::uniqueEmail', array(':value'))
				->rule('name', 'not_empty');
                        if ($validation->check())
                        {
                                $user = Model_User::build($formValues['username'], $formValues['name'], $formValues['username']);
                                $user->save();
                                $url = Route::url('view_user', array('id' => $user->id));
                                $success = "Successfully created user with username '<a href=\"$url\">" . $user->username . "</a>'.";
                        }
                        else
                        {
                                $errors = $validation->errors();
                        }
                }
                else
                {
                        $formValues = array(
                                'username' => '',
				'name' => '',
                        );
                }
                $formTemplate = array(
                        'username' => array('title' => 'Username', 'type' => 'input', 'hint' => 'Valid domains: '.implode(", ", Kohana::$config->load('system.default.admin_system.valid_external_domains'))),
			'name' => array('title' => 'Full Name', 'type' => 'input'),
                );

                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
                $this->template->content = FormUtils::drawForm('User', $formTemplate, $formValues, array('createUser' => 'Create User'), $errors, $success);
        }

	public function action_reset_password()
        {
                $this->template->title = "Reset Password";
                $content = View::factory('pages/reset_password');
                $content->username = Auth::instance()->get_user();
                $content->info = array();
                $content->show_form = true;

                $user = NULL;
                $reset_password_hash = $this->request->param('hash');
                if (!empty($reset_password_hash))
                        $user = Doctrine::em()->getRepository('Model_User')->findOneByResetPasswordHash($reset_password_hash);

                if (!empty($user))
                {
                        if ($user->resetPasswordTime !== NULL && $user->resetPasswordTime->getTimestamp()+86400 > time())
                        {
                                $content->username = $user->username;
                                if($this->request->method() == "POST")
                                {
                                        $password1 = $this->request->post('password1');
                                        $password2 = $this->request->post('password2');
                                        if (strlen($password1) < 8)
                                        {
                                                $content->info['error'][] = "Password is not at least 8 characters";
                                        }
					elseif($password1 != $password2)
                                        {
                                                $content->info['error'][] = "New passwords do not match";
                                        }
                                        else
                                        {	
						if (!Model_User::uncommonPassword($password1))
						{
							$content->info['error'][] = "Password is too common and may be easy to guess";
						}
						elseif(!RadAcctUtils::ResetPassword($user->username, $password1))
        	                                {
                	                                $content->info['error'][] = "Failed to update password";
                        	                }
                                	        else
                                        	{
                                                        $user->resetPasswordHash = "";
	                                                $user->resetPasswordTime = NULL;
        	                                        $user->save();
                	                                $content->info['notice'][] = "Password updated successfully.  <a href='/'>Click here</a> to login.";
                        	                        $content->show_form = false;
						}
                                        }
                                }
                        }
                        else
                        {
                                $user->resetPasswordHash = "";
                                $user->resetPasswordTime = NULL;
                                $user->save();
                                $content->info['error'][] = "Reset password URL has expired";
                                $content->show_form = false;
                        }

                }
                else
                {
                        $content->info['error'][] = "User account cannot be found for reset password hash";
                        $content->show_form = false;
                }
                $this->template->content = $content;
        }

	public function action_change_password()
        {
                $this->check_login();
                $this->template->title = "Change Password";
                $this->template->sidebar = View::factory('partial/sidebar');

                if(!Auth::instance()->is_local())
                {
                        $this->template->content = "<p style=\"text-align: center; font-weight: bold; font-size: 1em;\">Sorry, but your account password cannot be changed via our system.</p>";
                }
                else
                {
                        $content = View::factory('pages/change_password');
                        $content->username = Auth::instance()->get_user();
                        $content->info = array();
                        if($this->request->method() == "POST")
                        {
                                $oldpassword = $this->request->post('oldpassword');
                                $password1 = $this->request->post('password1');
                                $password2 = $this->request->post('password2');
				if (strlen($password1) < 8)
                                {
                                        $content->info['error'][] = "Password is not at least 8 characters";
                                }
                                elseif($password1 != $password2)
                                {
                                        $content->info['error'][] = "New passwords do not match";
                                }
                                else
                                {
					if (!Model_User::uncommonPassword($password1))
                                        {
                                                $content->info['error'][] = "Password is too common and may be easy to guess";
                                        }
					elseif(!Auth::instance()->change_password($oldpassword, $password1))
                                      	{
                                                $content->info['error'][] = "Failed to update password";
	                                }
        	                        else
                	                {
                        	                $content->info['notice'][] = "Password updated successfully";
					}
                                }
                        }
                        $this->template->content = $content;
                }
        }

	public function action_forgot_password()
        {
                $domain = Kohana::$config->load('system.default.domain');

                $this->template->title = "Forgot Password";
                $content =  View::Factory("pages/forgot_password");
                $content->info = array();

                if ($this->request->method() == 'POST')
                {
                        $user = null;
                        $post = $this->request->post();
                        $username_email = strtolower($post['username_email']);
                        if (preg_match("/^[a-zA-Z0-9\-_.]+@" . $domain . "$/", $post['username_email']))
                        {
                                $user = Doctrine::em()->getRepository('Model_User')->findOneByUsername($username_email);
                        }
                        else if (preg_match("/^[a-zA-Z0-9\-_.]+$/", $post['username_email']))
                        {
                                $user = Doctrine::em()->getRepository('Model_User')->findOneByUsername($username_email . "@" . $domain);
                        }
                        else if (preg_match("/^[a-zA-Z0-9\-_.]+@[a-zA-Z0-9\-_.]+$/", $username_email))
                        {
                                $users = Doctrine::em()->getRepository('Model_User')->findByEmail($username_email);
                                foreach ($users as $tempuser){
                                        if (strpos($tempuser->username, "@" . $domain) > 0)
                                        {
                                                $user = $tempuser;
                                                break;
                                        }
                                }
                        }
			if (!empty($user))
                        {
                                $admin_system_url = Kohana::$config->load('systemvar.default.admin_system.url');
                                $sender_name = Kohana::$config->load('system.default.admin_system.sender_name');
                                $user->resetPasswordHash = md5($user->username . date('U') . rand());
                                $user->resetPasswordTime = new \DateTime();
                                $user->save();
                                $email_body = "Hi " . $user->username . ",\n\nSomeone has requested a password reset for your account.  If this was not you, just ignore this email and the request will expire in 24 hours.  Otherwise, click the following link by " . date('H:i', time()) . " tomorrow to reset your password:\n\n" . $admin_system_url . "/reset_password/" . $user->resetPasswordHash . "\n\nRegards\n\n$sender_name\n" . Kohana::$config->load('system.default.admin_system.contact_email');
                                mail($user->email, Kohana::$config->load('system.default.admin_system.email_subject_prefix') . " Password reset", $email_body, "From: $sender_name <" . Kohana::$config->load('system.default.admin_system.sender_email') . ">");
                                $content->info['notice'][] = "An email has been sent to you with a reset password URL";
                        }
                        else
                                $content->info['error'][] = "Username / Email address does not belong to a @". $domain ." user";
                }

                $this->template->content = $content;
        }

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_user', array('id' => $this->request->param('id'))));
                }
		$title = "View User";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$domain = Kohana::$config->load('system.default.domain');
		if (!preg_match('/'.$domain.'$/', $formValues['username']))
		{
			unset($formTemplate['accounts']);
		}
		$notesFormValues = Controller_Notes::load_from_database('User', $formValues['id'], 'view');
                $notesFormTemplate = Controller_Notes::load_form_template('view');
		$this->template->content = FormUtils::drawForm('User', $formTemplate, $formValues, array('editUser' => 'Edit User')) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null);
	}

	public function action_view_details_json()
	{
		$referer = $this->request->referrer();
		$site = Doctrine::em()->getRepository('Model_Site')->findOneByUrl($referer);
		if (!is_object($site))
		{
			$this->check_ip($_SERVER['REMOTE_ADDR']);
		}
		else 
		{
			$ipAddrs = explode(",", $site->ipAddrs);
			if (!in_array($_SERVER['REMOTE_ADDR'], $ipAddrs))
			{
				throw new HTTP_Exception_403("You do not have permission to access this resource.");
			}
		}
		$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername($this->request->param('username') . "@" . Kohana::$config->load('system.default.domain'));
		if (!is_object($user))
		{
			throw new HTTP_Exception_404("No user with this username.");
                }	
		if (is_object($site))
                {
			$account = Doctrine::em()->getRepository('Model_UserAccount')->findOneBy(array("user" => $user, "site" => $site));
			if (is_object($account))
			{
				$details = array(
					'username' => $user->username,
	                	       	'name' => $user->name,
        	                	'email' => $user->email,
					'siteUsername' => $account->username,
					'sitePermissions' => $account->permissions
				);
			}
			else {
				throw new HTTP_Exception_404("User has no account for site specified.");
			}
		}
		else
		{
			$details = array(
				'username' => $user->username,
				'name' => $user->name,
				'email' => $user->email,
				'isSystemAdmin' => $user->isSystemAdmin,
			);
                $site = null;
		}
		echo json_encode($details);
		exit(0);
	}

	public function action_site_users_list()
	{
		$referer = $this->request->referrer();
		$site = Doctrine::em()->getRepository('Model_Site')->findOneByUrl($referer);
                if ($site == null || !in_array($_SERVER['REMOTE_ADDR'], explode(",", $site->ipAddrs)))
		{
			throw new HTTP_Exception_403('Your do not have permission to access this page.');
		}
		$accounts = Doctrine::em()->getRepository('Model_UserAccount')->findBySite($site);
		$userlist = array();
		$domain = "@" . Kohana::$config->load('system.default.domain');
		foreach ($accounts as $account)
		{
			$username = $account->user->username;
			if (preg_match('/' . $domain . '$/', $username))
			{
				$userlist[] = str_replace($domain, "", $username);
			}
		}
		echo implode(" ", $userlist);
		exit(0);
	}
	
	public function action_edit()
        {
                $this->check_login("systemadmin");
		$title = "Edit User";
		View::bind_global('title', $title);
		$jsFiles = array('jquery.js');
                View::bind_global('jsFiles', $jsFiles);
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
				$success = "Successfully updated user";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
		if (!RadAcctUtils::IsLocalUser($formValues['username']))
		{
			$formTemplate['email']['type'] = 'statichidden';
		}
		$domain = Kohana::$config->load('system.default.domain');
                if (!preg_match('/'.$domain.'$/', $formValues['username']))
                {
                        unset($formTemplate['accounts']);
                }
		$notesFormValues = Controller_Notes::load_from_database('User', $formValues['id'], 'edit');
                $notesFormTemplate = Controller_Notes::load_form_template('edit');
                $this->template->content = FormUtils::drawForm('User', $formTemplate, $formValues, array('updateUser' => 'Update User'), $errors, $success) .  FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null) . Controller_Notes::generate_form_javascript();
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$user = Doctrine::em()->getRepository('Model_User')->find($this->request->param('id'));
		if (!is_object($user))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$title = "Delete User";
		View::bind_global('title', $title);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
                        $username = $user->username;
		
                        if (!empty($formValues['yes']))
                        {
	                        if (sizeof($user->admins) == 0)
				{
					if (RadAcctUtils::IsLocalUser($username))
					{
						if (RadAcctUtils::DeleteUser($username))
						{
							$user->delete();
							$deleted = TRUE;
						}
					}
					else {
						$user->delete();
						$deleted = TRUE;
                  			}
				}
				if (!empty($deleted))
				{
			              	$this->template->content = "      <p class=\"success\">Successfully deleted user with username $username.  Go back to <a href=\"".Route::url('users')."\">user list</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete user with username $username.  Go back to <a href=\"".Route::url('users')."\">user list</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">User with username $username was not deleted.  Go back to <a href=\"".Route::url('users')."\">user list</a>.</p>";
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
				'message' => "Are you sure you want to delete user with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('User', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->sidebar = View::factory('partial/sidebar');
	}
	
	private function _validate($formValues) 
	{
		$errors = array();
		$validation = Validation::factory($formValues);
		if ($formValues['email'])
		{
			$validation->rule('email', 'email')
                                ->rule('email', 'email')
                                ->rule('email', 'Model_User::uniqueEmail', array(':value', $formValues['id']));
		}
                if (!$validation->check())
                {
			$errors = $validation->errors();
                }
		return $errors;
	}

	private function _load_from_database($id, $action = 'edit')
	{  
		$user = Doctrine::em()->getRepository('Model_User')->findOneById($id);
                if (!is_object($user))
		{
			throw new HTTP_Exception_404();
		}
                $formValues = array(
			'id' => $user->id,
			'username' => $user->username,
			'name' => $user->name,
			'email' => $user->email,
			'isSystemAdmin' => $user->isSystemAdmin,
			'accounts' => array(
				'currentAccounts' => array(),
			),
		);

		$accountFields = array('id', 'site', 'username', 'permissions');
		$a = 0;
		$usr_acc_ids = array();
		$user_accounts = array();
		// Fixes bug where duplicate interfaces appear when a new interface is added.
		foreach ($user->accounts as $a => $account)
                {
                        if (!in_array($account->id, $usr_acc_ids))
                        {
                                $user_accounts[] = $account;
                                $usr_acc_ids[] = $account->id;
                        }
                }
                foreach ($user_accounts as $a => $account)
                {
			foreach ($accountFields as $af)
			{
				if ($af == "site")
        	                {
                	        	$formValues['accounts']['currentAccounts'][$a][$af] = $account->$af->id;
                                }
                                else
	                        {
        	                	$formValues['accounts']['currentAccounts'][$a][$af] = $account->$af;
                	        }
	                }
        	        if ($action == 'view')
                	{
                                $formValues['accounts']['currentAccounts'][$a]['site'] = $account->site->name;
                       	}
                }

		if ($action == 'edit')
                {
                        foreach ($accountFields as $f => $field)
                        {
                                $formValues['accounts']['currentAccounts'][$a+1][$f] = '';
                        }
                }

		if ($action == 'view') 
		{
			$formValues['isSystemAdmin'] = ($formValues['isSystemAdmin'] ? 'Yes' : 'No');
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
			'id' => array('type' => 'hidden'),
			'username' => array('title' => 'Username', 'type' => 'statichidden'),
			'name' => array('title' => 'Full Name', 'type' => 'input'),
			'email' => array('title' => 'Email', 'type' => 'input'),
			'isSystemAdmin' => array('title' => 'System admin', 'type' => 'checkbox'),
			'accounts' => array(
                                'title' => 'Accounts',
                                'type' => 'fieldset',
                                'fields' => array(
                                        'currentAccounts' => array(
                                                'title' => '',
                                                'type' => 'table',
                                                'fields' => array(
                                                        'id' => array('type' => 'hidden'),
                                                        'site' => array('title' => 'Site', 'type' => 'select', 'options' => array_merge(array('0' => ''), Model_Site::getSiteNames())),
                                                        'username' => array('title' => 'Username', 'type' => 'input', 'size' => 20),
                                                        'permissions' => array('title' => 'Permissions', 'type' => 'input', 'size' => 50),
                                                ),
                                        ),
                                ),
                        ),
		);

		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$user = Doctrine::em()->getRepository('Model_User')->findOneById($id);
		if (!empty($formValues['name']))
                {
                        $user->name = $formValues['name'];
                }
		if (!empty($formValues['email']))
		{
			$user->email = $formValues['email'];
		}
		if (!isset($formValues['isSystemAdmin']))
                {
        		$formValues['isSystemAdmin'] = 0;
                }
		$user->isSystemAdmin = $formValues['isSystemAdmin'];
		$user->save();
		if (!empty($formValues['accounts']))
		{
			foreach ($formValues['accounts']['currentAccounts'] as $a => $accountValues)
        	        {
                	        if (empty($accountValues['site']))
                        	{
                                	if (!empty($accountValues['id']))
	                                {
        	                                $account = Doctrine::em()->getRepository('Model_UserAccount')->find($accountValues['id']);
                	                        $account->delete();
                        	        }
	                        }
        	                else
                	        {
					$site = Doctrine::em()->getRepository('Model_Site')->find($accountValues['site']);
                                	if (empty($accountValues['id'])) 
					{
        	                                $user->accounts->add(Model_UserAccount::build(
							$user,
                        	                        $site,
							$accountValues['username'],
                                        	        $accountValues['permissions']
	                                        ));
        	                        }
                	                else
                        	        {
                                	        $account = Doctrine::em()->getRepository('Model_UserAccount')->find($accountValues['id']);
                                        	$account->site = $site;
	                                        $account->username = $accountValues['username'];
						$account->permissions = $accountValues['permissions'];
                	                        $account->save();
                        	        }
                        	}
			}
                }

	}
}
	
