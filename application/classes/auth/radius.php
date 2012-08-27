<?php defined('SYSPATH') or die('No direct access allowed.');

class Auth_Radius extends Auth { 

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable autologin (not supported)
	 * @return  boolean
	 */
	protected function _login($username, $password, $remember)
	{
		if($this->check_credentials($username, $password))
		{
			// Complete the login
			return $this->complete_login($username);
		}

		// Login failed
		return FALSE;
	}

	private function check_credentials($username, $password)
	{
		$ssid = 'web-login';
		$anon_username = $this->create_anonymous_username($username);
		$phase2 = 'auth=MSCHAPV2';
		$phase1 = 'PEAP';

		if (eapol_test($ssid, $username, $anon_username, $password, $phase2, $phase1) === TRUE)
		{
			return TRUE;
		}

		return FALSE;
	}

	private function create_anonymous_username($username)
	{
		$usernameparts = explode('@', $username, 2);
		if(count($usernameparts) > 1)
		{
			return 'anonymous@'.$usernameparts[1];
		}
		else
		{
			return 'anonymous';
		}
	}

	/**
	 * Get the stored password for a username. (Not supported by this auth driver, obviously.)
	 *
	 * @param   mixed   username
	 * @return  string
	 */
	public function password($username)
	{
		return NULL;
	}

	/**
	 * Compare password with original (plain text). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$username = $this->get_user();

		if ($username === FALSE)
		{
			return FALSE;
		}

		return $this->check_credentials($username, $password);
	}

	public function logged_in($role = NULL)
	{
		$status = FALSE;
 
		// Get the user from the session
		$username = $this->get_user();
 
		if (!is_null($username))
		{
			// Everything is okay so far
			$status = TRUE;

			if($role == 'systemadmin')
			{
				$user = Doctrine::em()->getRepository('Model_User')->findOneByEmail($username);
				if($user == null || !$user->isSystemAdmin)
				{
					$status = FALSE;
				}
			}
		}
	
		return $status;
	}
}
