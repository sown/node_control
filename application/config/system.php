<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'default' => array
	(
		'node_config'	=> array
		(
			'url'	=> 'https://sown-auth2.ecs.soton.ac.uk',
		),
		'dns'		=> array
		(
			'host'	=> '10.13.0.254',
		),
		'radius'	=> array
		(
			'host'	=> '10.13.0.252',
			'port'	=> 1812,
		),
		'softflow'	=> array
		(
			'host'	=> '152.78.189.84',
		),
		'ntp'		=> array
		(
			'host'	=> '193.62.22.74',
		),
		'gateway'	=> '10.13.0.254',
		'domain'	=> 'sown.org.uk',
		'oid'		=> '.1.3.6.1.4.1.12275.5032',
		'static_files'	=> array
		(
			'authorized_keys'	=>	'/srv/www/static_files/authorized_keys',
			'passwd'		=>	'/srv/www/static_files/passwd',
		),
		'filename'	=> __FILE__,
	),
);
