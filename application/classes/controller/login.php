<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Controller_AbstractAdmin
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
		
		$view->content = $content;
		echo (string) $view->render();
	}
	
        public function action_change_password()
        {
                $this->check_login();
                $view = View::Factory("template");
                $view->title = "Change Password";
                $sidebar = View::factory('partial/sidebar');
                $view->sidebar = $sidebar;

                if(!Auth::instance()->is_local())
                {
                        $view->content = "Sorry, but your account password cannot be changed via our system.";
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
                                if($password1 != $password2)
                                {
                                        $content->info['error'][] = "New passwords do not match";
                                }
                                else
                                {
                                        if(!Auth::instance()->change_password($oldpassword, $password1))
                                        {
                                                $content->info['error'][] = "Failed to update password";
                                        }
                                        else
                                        {
                                                $content->info['notice'][] = "Password updated successfully";
                                        }
                                }
                        }
                        $view->content = $content;
                }
                echo (string) $view->render();
        }

        public function action_reset_password()
        {
                $view = View::Factory("template");
                $view->title = "Reset Password";
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
                                        if($password1 != $password2)
                                        {
                                                $content->info['error'][] = "New passwords do not match";
                                        }
                                        else
                                        {
                                                if(!RadAcctUtils::ResetPassword($user->username, $password1))
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
                $view->content = $content;
                echo (string) $view->render();
        }

}
