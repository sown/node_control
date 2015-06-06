<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Backfire_Monitoring extends Package_Config
{
	const package_name = 'sown_openwrt_monitoring';

	public static $supported = array(
		// 'uci_config_sown_core' => array(
		// 	// Entries should be listed in increasing version order
		// 	array(
		// 		'>=' => '0.1.78',
		// 		//'<' => '1.0', // Example of upper bound
		// 		'method' => 'config_sown_core_v0_1_78'
		// 	),
		// 	// array(
		// 	// 	'>=' => '2.0'
		// 	// 	//'<' => '3.0', // Example of upper bound
		// 	// 	'method' => 'settings_initial'
		// 	// ),
		// ),
		'uci_config_snmpd' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'snmpd_v0_1_78'
			),
		),
		'cronjobs' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'cronjobs_v0_1_78'
			),
		),
		'uci_config_softflowd' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_softflowd_v0_1_78'
			),
		),
	);

	public static function cronjobs_v0_1_78(Model_Node $node)
	{
		//require $_SERVER['DOCUMENT_ROOT'] . '/admin/incoming_cronjobs.php';
		//Sown::process_cron_jobs();		
		static::send_shell_script("return 0");
	}

	public static function snmpd_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = Kohana::$config->load('system.default.filename');

		$node_location = '';
		$node_name = $node->name;
		$sown_oid = Kohana::$config->load('system.default.oid');
		
		$config = array(
			/* Boring config */
			'agent' => array(
				array(
					'agentaddress' => 'UDP:161',
				),
			),
			'com2sec' => array(
				'public' => array(
					'secname'   => 'ro',
					'source'    => 'default',
					'community' => 'public',
				),
				'private' => array(
					'secname'   => 'rw',
					'source'    => 'localhost',
					'community' => 'private',
				),
			),
			'group' => array(
				'public_v1' => array(
					'group' => 'public',
					'version' => 'v1',
					'secname' => 'ro',
				),
				'public_v2c' => array(
					'group' => 'public',
					'version' => 'v2c',
					'secname' => 'ro',
				),
				'public_usm' => array(
					'group' => 'public',
					'version' => 'usm',
					'secname' => 'ro',
				),
				'private_v1' => array(
					'group' => 'private',
					'version' => 'v1',
					'secname' => 'rw',
				),
				'private_v2c' => array(
					'group' => 'private',
					'version' => 'v2c',
					'secname' => 'rw',
				),
				'private_usm' => array(
					'group' => 'private',
					'version' => 'usm',
					'secname' => 'rw',
				),
			),
			'view' => array(
				'all' => array(
					'viewname' => 'all',
					'type' => 'included',
					'oid' => '.1',
				),
			),
			'access' => array(
				'public_access' => array(
					'group' => 'public',
					'context' => 'none',
					'version' => 'any',
					'level' => 'noauth',
					'prefix' => 'exact',
					'read' => 'all',
					'write' => 'none',
					'notify' => 'none',
				),
				'private_access' => array(
					'group' => 'private',
					'context' => 'none',
					'version' => 'any',
					'level' => 'noauth',
					'prefix' => 'exact',
					'read' => 'all',
					'write' => 'all',
					'notify' => 'all',
				),
			),
			
			/* SOWN config */
			'system' => array(
				array(
					'sysLocation' => $node_location,
					'sysName'     => $node->hostname,
					'sysDescr'    => $node_name,
					'sysContact'  => 'support@sown.org.uk',
				),
			),
			'exec\' \'1'   => array(
				array(
					'name'=> 'hostap_check',
					'miboid'  => $sown_oid .'.1',
					'prog' => '/usr/bin/hostap_check',
				),
			),
			'exec\' \'2'   => array(
				array(
					'name'=> 'softflowd_check',
					'miboid'  => $sown_oid .'.2',
					'prog' => '/usr/bin/softflowd_check',
				),
			),
			'exec\' \'3'   => array(
				array(
					'name'=> 'changed_config_files',
					'miboid'  => $sown_oid .'.3',
					'prog' => '/bin/opkg list-changed-conffiles',
				),
			),
			'exec\' \'4'   => array(
				array(
					'name'=> 'available_updates',
					'miboid'  => $sown_oid .'.4',
					'prog' => '/bin/opkg list-upgradable',
				),
			),
			'disk' => array(
				array(
					'path' => 'includeAllDisks',
					'threshold' => '10%',
				),
			),
		);

		static::send_uci_config('snmpd', $config);
	}

	public static function config_softflowd_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = Kohana::$config->load('system.default.filename');

		$ifaces = $node->interfaces;
		foreach($ifaces as $iface)
		{
			if(!$iface->offerDhcp)
				continue;
			$config = array(
				'softflowd' => array(
					// TODO create one of these for each interface which offers dhcp.
					array(
						'interface' => $iface->name,
						// 'pcap_file' => '',
						// 'timeout' => '',
						'max_flows' => 8192,
						// TODO this port number is a bit random.
						'host_port' => Kohana::$config->load('system.default.softflow.host').':'.$node->vpnEndpoint->port,
						'pid_file' => '/var/run/softflowd.pid',
						'control_socket' => '/var/run/softflowd.ctl',
						'export_version' => 5,
						// 'hoplimit' => '',
						'tracking_level' => 'full',
						'track_ipv6' => 1,
						'enabled' => 0,
					)
				),
			);
		}
		
		static::send_uci_config('softflowd', $config, $mod);
	}
}
