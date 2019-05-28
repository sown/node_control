<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Controller_AbstractAdmin
{
	public function before()
	{
		if ($this->request->action() == "login_page")
			$this->template = "pages/login";
                parent::before();
	}

	public function action_login_page()
	{
		if ($this->request->method() == 'POST')
		{
			$post = $this->request->post();
			$post['username'] .= (strpos($post['username'], '@') ? "" : "@" . Kohana::$config->load('system.default.domain'));
			$post['username'] = str_replace(' ', '', $post['username']);
			$success = Auth::instance()->login($post['username'], $post['password']);
			if (!empty($_SERVER['HTTPS'])) $uri = "https";
			else $uri =  "http";
			$uri .= "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
			if($success)
			{
				$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername($post['username']);
				if (!is_object($user)){
					$user = Model_User::build($post['username'], "", $post['username']);
					$user->save();
				}
				$loginstat = Model_LoginStatistic::build($_SERVER['REMOTE_ADDR'], $post['username'], "Granted", $_SERVER['HTTP_USER_AGENT'], "URI=$uri");
				$loginstat->save();
				if ($this->request->query('url'))	
					$this->request->redirect($this->request->query('url'));
				else
					$this->request->redirect(Route::url('home'));
			}
			else
			{	
				$this->template->message = "Login Failed";
				$loginstat = Model_LoginStatistic::build($_SERVER['REMOTE_ADDR'], $post['username'], "Denied", $_SERVER['HTTP_USER_AGENT'], "URI=$uri | Login Failed: Username/Password combination could not be authenticated.");
                                $loginstat->save();
			}
		}
		elseif (Auth::instance()->logged_in()) 
		{
			$this->request->redirect(Route::url('home'));	
		}
	}			
	
	public function action_logout()
	{
		$success = Auth::instance()->logout();
		if ($success)
			$this->request->redirect(Route::url('login'));
	}

}
