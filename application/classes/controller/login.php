<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Controller
{
	public function action_login_page()
	{
		$view = View::Factory("pages/login");
	
		if ($this->request->method() == 'POST')
		{
			$post = $this->request->post();
			$success = Auth::instance()->login($post['username'], $post['password']);

			if($success)
			{
				if ($this->request->query('url'))	
					$this->request->redirect($this->request->query('url'));
				else
					$this->request->redirect(Route::url('home'));
			}
			else
				$view->message = "Login Failed";
		}
		elseif (Auth::instance()->logged_in()) 
		{
			$this->request->redirect(Route::url('home'));	
		}
			
		echo (string) $view->render();
	}
	
	public function action_logout()
	{
		$success = Auth::instance()->logout();
		if ($success)
			$this->request->redirect(Route::url('login'));
	}

	public function action_forgot_password()
	{
		$domain = Kohana::$config->load('system.default.domain');

		$view = View::Factory("template");
                $view->title = "Forgot Password";
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
					if (strpos($tempuser->username, "@sown.org.uk") > 0)
					{
						$user = $tempuser;
						break;
					}
				}
			}

			if (!empty($user))
			{
				$admin_system_url = Kohana::$config->load('system.default.admin_system.url');
				$sender_name = Kohana::$config->load('system.default.admin_system.sender_name');
				$user->resetPasswordHash = md5($user->username.date('U').rand());				
				$user->save(); //This isn't saving the password hash
				$email_body = "Hi " . $user->username . ",\n\nSomeone has requested a password reset for your account.  Click the following link to reset you password:\n\n" . $admin_system_url . "/reset_password?hash=" . $user->resetPasswordHash . "\n\nTo cancel this password reset click the following link:\n\n" . $admin_system_url . "/cancel_password_reset?hash=" . $user->resetPasswordHash . "\n\nRegards\n\n" . $sender_name . "\n" . Kohana::$config->load('system.default.admin_system.contact_email');
				mail($user->email, Kohana::$config->load('system.default.admin_system.email_subject_prefix') . " Password reset", $email_body, "From: $sender_name <" . Kohana::$config->load('system.default.admin_system.sender_email') . ">");
				$content->info['message'][] = "An email has been sent to you with a reset password URL";
			}
			else 
				$content->info['error'][] = "Username / Email address does not belong to a @". $domain ." user";
		}
		
		$view->content = $content;
		echo (string) $view->render();
	}
}
