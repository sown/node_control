<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'default' => array
	(
		'node_config'	=> array
		(
                        # We need to use the 10.13 address because of the firewall
                        # We can't change the route, because the tunnel needs to come up too
			'url'	=> 'https://auth2.sown.org.uk',
		),
		'admin_system'	=> array
		(
                        'url'   => 'https://sown-auth2.ecs.soron.ac.uk',
			'contact_email' => 'support@sown.org.uk',
			'sender_email' => 'NO-REPLY@sown.org.uk',
			'sender_name' => 'Southampton Open Wireless Network team',
			'email_subject_prefix' => '[sown-accounts]',
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
			'radutmp'		=>	'/var/log/freeradius/sradutmp',
		),
		'rrd_deployment_path'	=> '/srv/radacct-tg/nas-rrds/',
		'routes'	=> '
push "route 10.12.0.0 255.254.0.0"
push "route 152.78.189.82 255.255.255.255"
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
