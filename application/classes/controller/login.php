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
			$success = Auth::instance()->login($post['username'], $post['password']);

			if($success)
			{
				$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername($post['username']);
				if (!is_object($user)){
					$user = Model_User::build($post['username'], "", $post['username']);
					$user->save();
				}
				if ($this->request->query('url'))	
					$this->request->redirect($this->request->query('url'));
				else
					$this->request->redirect(Route::url('home'));
			}
			else
				$this->template->message = "Login Failed";
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
