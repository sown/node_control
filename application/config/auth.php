<?php defined('SYSPATH') or die('No direct access allowed.');

return array(

	'driver'       => 'radius',/* use 'file' or 'radius' */
	'hash_method'  => 'sha256',
	'hash_key'     => '12345',
	'lifetime'     => 1209600,
	'session_type' => Session::$default,
	'session_key'  => 'auth_user',

	// Username/password combinations for the Auth File driver
	'users' => array(
		'admin' => hash_hmac('sha256', 'password', '12345'),
		// 'admin' => 'b3154acf3a344170077d11bdb5fff31532f679a1919e716a02',
	),

);
