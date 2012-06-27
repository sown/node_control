<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Controller
{
	public function action_login_page()
	{
		$post = $this->request->post();
		$success = Auth::instance()->login($post['username'], $post['password']);
		
		if($success)
		{
			$this->request->redirect($_GET['url']);
		}
		else
		{
			echo "<html>";
			echo "<head>";
			echo "</head>";
			echo "<body>";
			echo "<form method='POST'>";
			echo "<table>";
			echo "<tr><td>Username:</td><td><input name='username' /></td></tr>";
			echo "<tr><td>Password:</td><td><input name='password' type='password' /></td></tr>";
			echo "<tr><td /><td><input type='submit' /></td></tr>";
			echo "</table>";
			echo "</form>";
			echo "</body>";
			echo "</html>";
		}
	}
}
