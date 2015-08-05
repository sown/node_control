<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	'default' => array
	(
		'name' => 'SOWN',
		'long_name' => 'Southampton Open Wireless Network',
		'node_config'	=> array
		(
                        # We need to use the 10.13 address because of the firewall
                        # We can't change the route, because the tunnel needs to come up too
			'url'	=> 'https://auth2.sown.org.uk',
		),
		'admin_system'	=> array
		(
			'site_name' => 'SOWN Admin System',
                        'url'   => 'https://sown-auth2.ecs.soton.ac.uk',
			'domain' => 'sown.org.uk',
			'contact_email' => 'support@sown.org.uk',
			'sender_email' => 'NO-REPLY@sown.org.uk',
			'sender_name' => 'Southampton Open Wireless Network team',
			'email_subject_prefix' => '[sown-accounts]',
                        'latest_end_datetime' => '2037-12-31 23:59:59',
			'valid_external_domains' => array('ecs.soton.ac.uk', 'soton.ac.uk'),
			'valid_query_ips' => array('152.78.189.252', '152.78.189.39', '10.13.0.250'), // skoll.ecs.soton.ac.uk, sown-dev.ecs.soton.ac.uk, dev.sown.org.uk
		),
		'adapter_types' => array
		(
			'a' => '802.11a',
			'b' => '802.11b',
			'g' => '802.11g',
			'n' => '802.11n',
			'100M' => '100Mb/s',
			'1G' => '1Gb/s',
		),
		'dns'		=> array
		(
			'host'	=> '10.13.0.239',
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
			'lastradusers'		=>	'/tmp/lastradiususers.csv',
		),
		'reported_server_attributes' => array(
			'hardDrive',
			'kernel',
			'memory',
			'networkPorts',
			'os',
			'processor',		
		),
		'rrd' 		=> array
		(
			'deployment_path'	=> 	'/srv/radacct-tg-new/nas-rrds/',
			'client_path' 		=> 	'/srv/radacct-tg-new/sta-rrds/',
		),
		'routes'	=> '
push "route 10.12.0.0 255.254.0.0"
push "route 152.78.189.82 255.255.255.255"
',
		'vlan'		=> array
		(
			'local' => 'SOWN',
			'vpn' => 'ECS DMZ',
			'internal' => array('SOWN'),
			'external' => array('ECS DMZ'),
		),
		'filename'	=> __FILE__,
		'check'		=> array
		(
			'limit'		=> array
			(
				'RadiusDatabaseSize'	=> array
				(
					'default'	=> array
					(
						'warning'	=> 160000,
						'critical'	=> 320000,
					),
					'radpostauth'	=> array
					(
						'warning'	=> 4000000,
						'critical'	=> 8000000,
					),
				),
			),
		),
	),
);
