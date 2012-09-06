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
		$view = View::Factory("template");
		$view->title = "Forgot Password";
		$view->content = View::Factory("pages/forgot_password");
		echo (string) $view->render();
	}
}
