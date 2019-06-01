<?php defined('SYSPATH') or die('No direct access allowed.');

class Auth_Radius extends Auth { 

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  remember (not supported)
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

	/**
	 * Checks that the a user credentials can be validated over Radius.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @return  boolean
	 */
	private function check_credentials($username, $password)
	{
		$radius = radius_auth_open();
		$radconf = Kohana::$config->load('systemvar.default.radius');
		radius_add_server($radius, $radconf['host'], $radconf['port'], $radconf['password'], 5, 3);
		radius_create_request($radius, RADIUS_ACCESS_REQUEST);
		radius_put_attr($radius, RADIUS_USER_NAME, $username);
		radius_put_attr($radius, RADIUS_USER_PASSWORD, $password);
		$result = radius_send_request($radius);
		if($result == RADIUS_ACCESS_ACCEPT){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	/**
	 * Get the stored password for a username. (Not supported by this auth driver, obviously).
	 *
	 * @param   mixed   username
	 * @return  string
	 */
	public function password($username)
	{
		return NULL;
	}

	/**
	 * Compare password with original (plain text). Works for current (logged in) user.
	 *
	 * @param   string  password
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

	/** 
	 * Change the authenticated users password, confirming the user with their old password.
	 * 
	 * @param   string  old
	 * @param   string  new
         * @return  boolean
	 */
	public function change_password($old, $new)
	{
		$username = $this->get_user();

		if ($username === FALSE)
		{
			return FALSE;
		}

		if(!RadAcctUtils::IsLocalUser($username))
		{
			return FALSE;
		}
		
		return RadAcctUtils::UpdateUser($username, $new, $old);
	}

        /**
 	 * Is the Auth instance for a local user.
	 *
         * @return  boolean
         */
	public function is_local()
	{
		$username = $this->get_user();

		if ($username === FALSE)
		{
			return FALSE;
		}

		return RadAcctUtils::IsLocalUser($username);
	}

	/**
         * Is the Auth instance for a user who is a deployment admin.
         *
         * @return  boolean
         */ 
	public function is_deploymentadmin()
	{
		$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
		if(is_object($user))
		{
			return count($user->admins) > 0;
		}
		return false;
	}

        /**
	 * Is the Auth instance for a user that is of a certain type.
	 *
	 * @param   string  usertype
	 * @return  boolean
	 */
	public function is($usertype)
	{
		switch($usertype)
		{
			case 'local':
				return $this->is_local();
			case 'deploymentadmin':
				return $this->is_deploymentadmin();
			default:
				return $this->logged_in($usertype);
		}
	}

	/**
	 * Is the Auth instance for a user who is currently logged in
	 *
	 * @param   string  role
	 * @return  boolean
	 */
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
				$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername($username);
				if($user == null || !$user->isSystemAdmin)
				{
					$status = FALSE;
				}
			}
		}
	
		return $status;
	}
}
