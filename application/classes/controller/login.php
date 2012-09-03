<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Controller
{
	public function action_login_page()
	{
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
				$msg = "Login Failed";
		}
		elseif (Auth::instance()->logged_in()) 
		{
			$this->request->redirect(Route::url('home'));	
		}
	
		echo "<html>";
		echo "<head>";
		echo "</head>";
		echo "<body>";
		if (!empty($msg))
			echo "<p><b>$msg</b></p>";
		echo "<form method='POST'>";
		echo "<table>";
		echo "<tr><td>Username:</td><td><input name='username' /></td></tr>";
		echo "<tr><td>Password:</td><td><input name='password' type='password' /></td></tr>";
		echo "<tr><td /><td><input type='submit' value='Login' /></td></tr>";
		echo "</table>";
		echo "</form>";
		echo "</body>";
		echo "</html>";
	
	}
	
	public function action_logout(){
		$success = Auth::instance()->logout();
		if ($success)
			$this->request->redirect(Route::url('login'));
	}
}
