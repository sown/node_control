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
		)
	);

	public static function cronjobs_v0_1_78(Model_Node $node)
	{
		require $_SERVER['DOCUMENT_ROOT'] . '/admin/incoming_cronjobs.php';
		
		static::send_shell_script("return 0");
	}

	public static function snmpd_v0_1_78(Model_Node $node)
	{
		$node_location = '';
		$deployment = $node->getCurrentDeployment();
		$node_name = ($deployment != null ? $deployment->name : $node->hostname);
		$sown_oid = '.1.3.6.1.4.1.12275.5032';
		
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
			'exec'   => array(
				array(
					'execname'=> 'hostap_check',
					'miboid'  => $sown_oid .'.1',
					'prog' => '/usr/bin/hostap_check',
				),
				array(
					'execname' => 'boot_time',
					'miboid'  => $sown_oid .'.2',
					'prog' =>'/usr/bin/booted_check',
				),
			),
			'pass' => array(
				array(
					'miboid'  => $sown_oid .'.3',
					'prog' =>'/usr/bin/snmp-in',
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
}
