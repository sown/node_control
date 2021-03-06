<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'default' => array
	(
		'type'       => 'mysql',
		'connection' => array(
			'hostname'   => 'localhost',
			'username'   => 'sown',
			'password'   => 'password',
			// 'persistent' => FALSE,
			'database'   => 'sown_data',
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => Kohana::$environment !== Kohana::PRODUCTION,
	),
	'node_config' => array
	(
		'type'       => 'mysql',
		'connection' => array(
			'hostname'   => 'localhost',
			'username'   => 'sown',
			'password'   => 'password',
			// 'persistent' => FALSE,
			'database'   => 'sown_data',
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => Kohana::$environment !== Kohana::PRODUCTION,
		'ssh_default_password' => 'CHANGEME',

	),
	'radius' => array
	(
		'type'       => 'mysql',
		'connection' => array(
			'hostname'   => 'localhost',
			'username'   => 'sown',
			'password'   => 'password',
			// 'persistent' => FALSE,
			'database'   => 'radius',
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => Kohana::$environment !== Kohana::PRODUCTION,
	),
	'accounts-sown_org_uk' => array
	(
		'type'       => 'mysql',
		'connection' => array(
			'hostname'   => 'localhost',
			'username'   => 'sown',
			'password'   => 'password',
			// 'persistent' => FALSE,
			'database'   => 'radius',
		),
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => Kohana::$environment !== Kohana::PRODUCTION,
	),
);
