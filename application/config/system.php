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
			'host'	=> '10.13.0.239',
			'auth_port'	=> 1812,
			'acct_port'	=> 1813,
		),
		'softflow'	=> array
		(
			'host'	=> '10.13.0.239',
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
		'rrd_deployment_path'	=> '/srv/radacct-tg/nas-rrds/',
		'routes'	=> '
push "route 10.12.0.0 255.254.0.0"
push "route 152.78.189.82 255.255.255.255"
push "route 152.78.189.90 255.255.255.255"
',
		'filename'	=> __FILE__,
		'check'		=> array
		(
			'limit'		=> array
			(
				'RadiusDatabaseSize'	=> array
				(
					'default'	=> array
					(
						'warning'	=> 5000,
						'critical'	=> 10000,
					),
					'radpostauth'	=> array
					(
						'warning'	=> 500000,
						'critical'	=> 1000000,
					),
				),
			),
		),
	),
);
