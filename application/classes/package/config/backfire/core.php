<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Backfire_Core extends Package_Config
{
	const package_name = 'sown_openwrt_core';
	
	public static $supported = array(
		'uci_config_sown_core' => array(
			// Entries should be listed in increasing version order
			array(
				'>=' => '0.1.78',
				//'<' => '1.0', // Example of upper bound
				'method' => 'config_sown_core_v0_1_78'
			),
			// array(
			// 	'>=' => '2.0'
			// 	//'<' => '3.0', // Example of upper bound
			// 	'method' => 'settings_initial'
			// ),
		),
		'credentials' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'credentials_v0_1_78'
			),
		),
		'uci_config_system' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_system_v0_1_78',
			),	
		),
		'uci_config_sown_firewall' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_sown_firewall_v0_1_78'
			),
		),
		'uci_config_network' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_network_v0_1_78'
			),
		),
		'uci_config_wireless' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_wireless_v0_1_78'
			),
		),
		'uci_config_dhcp' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_dhcp_v0_1_78'
			),
		),
		'uci_config_crontabs' => array(
                        array(
                                '>=' => '0.1.78',
                                'method' => 'config_crontabs_v0_1_78'
                        ),
                ),
		'uci_config_locations' => array(
                        array(
                                '>=' => '0.1.78',
                                'method' => 'config_locations_v0_1_78'
                        ),
		),
	);

	public static function credentials_v0_1_78(Model_Node $node, $version)
	{
		$static_files = Kohana::$config->load('system.default.static_files');
		
		$mod[] = $static_files['authorized_keys'];
		$mod[] = $static_files['passwd'];
		
		static::send_tgz(array(
			'client.crt'      => array(
				'content' => $node->certificate->publicKey,
				'mtime'   => $node->certificate->lastModified->getTimestamp(),
			),
			'client.key'      => array(
				'content' => $node->certificate->privateKey,
				'mtime'   => $node->certificate->lastModified->getTimestamp(),
			),
			'authorized_keys' => $static_files['authorized_keys'],
# SHOULDN'T PASS THIS FILE
# 			'passwd'          => $static_files['passwd'],
		), $mod);
	}

	public static function config_system_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;

		$config = array(
			'system' => array(
				array(
					'hostname' => $node->hostname,
					'timezone' => 'UTC',
				)
			),

			// We want to use NTP not rdate. Set it to a non-existant interface
			'rdate' => array(
				array(
					'interface' => 'disabled',
				),
			),

			// We need to configure ntpd to make it work
			'timeserver' => array(
				'ntp' => array(
					'server' => array('pool.ntp.org'),
					'enable_server' => '0',
				),
			),
		);
		
		static::send_uci_config('sown_core', $config, $mod);
	}


	public static function config_sown_core_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = Kohana::$config->load('system.default.filename');

		$config = array(
			'node' => array(
				array(
					'config_URL' => Kohana::$config->load('system.default.node_config.url').'/package/config/backfire/',
					'hostname'   => $node->hostname,
					'node_name'  => $node->name,
					'id'         => $node->id,
				)
			)
		);
		
		static::send_uci_config('sown_core', $config, $mod);
	}

	public static function config_network_v0_1_78(Model_Node $node)
	{
		$config = array();
		$config['interface']['loopback'] = array(
			'ifname' => 'lo',
			'proto'  => 'static',
			'ipaddr' => '127.0.0.1',
			'netmask'=> '255.0.0.0',
			'ip6addr'=> '::1',	
		);

		$mod[] = __FILE__;
		$mod[] = Kohana::$config->load('system.default.filename');

		foreach ($node->interfaces as $iface)
		{
			$iface_config = array();
			
			$iface_config['ifname'] = $iface->name;
			
			$iface_config['proto'] = $iface->type;
			
			if($iface->type == 'static')
			{
				$v4_net_addr = IPv4_Network_Address::factory($iface->IPv4Addr, $iface->IPv4AddrCidr);
				
				$iface_config['ipaddr'] = $v4_net_addr->get_address();
				$iface_config['netmask'] = $v4_net_addr->get_subnet_mask();
				// TODO get DNS servers for static IPs from the database
				// morse: Currently disabled while I work out how the node should handle DNS
				// $iface_config['dns'] = Kohana::$config->load('system.default.dns.host');
				
				if($iface->IPv6Addr)
				{
					$v6_net_addr = IPv6_Network_Address::factory($iface->IPv6Addr, $iface->IPv6AddrCidr);

					$iface_config['ip6addr'] = $v6_net_addr;
					if ($iface->name == 'tap0')
					{
						$iface_config['ip6gw'] = $v6_net_addr->get_address_in_network(1);
					}
				}
			}
			
			if(false /* TODO Node is a campus node*/ && $iface->name == 'eth0')
			{
				$iface_config['gateway'] = Kohana::$config->load('system.default.gateway');
			}
			
			$config['interface'][$iface->name] = $iface_config;
		}
		
		static::send_uci_config('network', $config, $mod);
	}

	public static function config_sown_firewall_v0_1_78(Model_Node $node)
	{
		// TODO load the config from the database.
		$config = array(
			'feature' => array(
				'port_filter' => array(
					'enabled' => 'false',
					'list' => '80 443 3353 3653 10000 1723 5000 5000 22 389 636 406 143 220 993 110 995 21 465 587 3389 5900 1494 6667 6668 6669 7000 7001',
				),
				'layer7' => array(
					'enabled' => 'false',
				),
				'IP_blacklist' => array(
					'enabled' => 'false',
					'list' => '4.3.3.2',
				),
				'Host_blacklist' => array(
					'enabled' => 'false',
					'list' => 'www.google.co.uk www.facebook.com',
				),
			)
		);
		
		static::send_uci_config('sown_firewall', $config, array(__FILE__));
	}

	public static function config_wireless_v0_1_78(Model_Node $node)
	{
		$config = array();

		$mod[] = __FILE__;
		
		$count = 0;
		$radio_id = array();
		foreach ($node->interfaces as $interface)
		{
			// mac80211 uses this identifier for co-ordination only
			// it looks up the actual device name using the 
			// mac address

			// the 'dev_name' of the radio can be anything, as long
			// as the wifi-iface->device uses the same name
			$dev_name = 'radio'.$interface->id;
			
			$radio_id[$interface->id] = $dev_name;
			
			// 'mac80211' is hard coded, as all our nodes use this
			// for their wireless interfaces
			$config['wifi-device'] = array(
					$dev_name => array(
						'type' => "mac80211",
						'channel' => $interface->networkAdapter->wirelessChannel,
						'macaddr' => $interface->networkAdapter->mac,
					),
				);

			/* Node might not have a deployment ... */
			if($node->currentDeployment !== NULL)
			{
				if($node->currentDeployment->exceedsCap)
				{
					$config['wifi-device'][$dev_name]['disabled'] = 1;
				}
			}
			// Also optional 'hwmode' which can be set to '11g',
			// we can use the database 'type' field here

			$count++;
		}
		
		foreach ($node->interfaces as $interface)
		{
			if($interface->networkAdapter->wirelessChannel == null)
				continue;
			if (!isset($radio_id[$interface->id]))
				continue;

			// the wifi-iface name should not be $interface->name
			// this causes some exotic race in hostapd, which
			// prevents the interface from being started cleanly
			$fake_iface_name = md5($interface->name);
			$config['wifi-iface'][$fake_iface_name] = array(
				'device' => $radio_id[$interface->id],
				'mode' => 'ap',
				'ssid' => $interface->ssid,
				'ifname' => $interface->name,
			);
			
			if($interface->is1x)
			{
				$mod[] = Kohana::$config->load('system.default.filename');
				$config['wifi-iface'][$fake_iface_name]['encryption'] = 'wpa2+aes';
				$config['wifi-iface'][$fake_iface_name]['auth_server'] = Kohana::$config->load('system.default.radius.host');
				$config['wifi-iface'][$fake_iface_name]['acct_server'] = Kohana::$config->load('system.default.radius.host');
				$config['wifi-iface'][$fake_iface_name]['auth_port'] = Kohana::$config->load('system.default.radius.auth_port');
				$config['wifi-iface'][$fake_iface_name]['acct_port'] = Kohana::$config->load('system.default.radius.acct_port');
				$config['wifi-iface'][$fake_iface_name]['key'] = $node->radiusSecret;
				$config['wifi-iface'][$fake_iface_name]['auth_key'] = $config['wifi-iface'][$fake_iface_name]['key'];
				$config['wifi-iface'][$fake_iface_name]['acct_key'] = $config['wifi-iface'][$fake_iface_name]['key'];
				$config['wifi-iface'][$fake_iface_name]['nasid'] = $node->FQDN;
			}
		}
		
		static::send_uci_config('wireless', $config, $mod);
	}

	public static function config_dhcp_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = Kohana::$config->load('system.default.filename');
		
		$config = array(
			'dnsmasq' => array(
				array(
					'leasefile'     => '/var/state/dhcp.leases',
					'domain'        => Kohana::$config->load('system.default.domain'),
					'authoritative' => 1,
					'addnhosts'	=> array('/tmp/sown-banned-hosts'),
					 # the default version reports data to syslog
					'dhcp_script'   => '/usr/sbin/dhcp_event',
				),
			),
		);
		
		foreach ($node->interfaces as $iface)
		{
			$if_config = array();
			$if_config['interface'] = $iface->name;
			
			if ($iface->offerDhcp)
			{
				$v4_net_addr = IP_Network_Address::factory($iface->IPv4Addr, $iface->IPv4AddrCidr);

				$if_config['start'] = '10';
				$if_config['limit'] = $v4_net_addr->get_network_address_count() - 20;
				$if_config['leasetime'] = '1h';
				$if_config['dhcp_option'] = '42,'.Kohana::$config->load('system.default.ntp.host');
			}
			else
			{
				$if_config['ignore'] = 1;
			}
			
			$config['dhcp'][$iface->name] = $if_config;
		}

		static::send_uci_config('dhcp', $config, $mod);
	}

	public static function config_crontabs_v0_1_78(Model_Node $node) 
	{
		$config = array(
                        'feature' => array(
                                'cron_check' => array(
                                        'enabled' => 'true',
                                        'command' => '5 * * * * /usr/sbin/cron_check',
                                ),
				'tunnel' => array(
					'enabled' => 'true',
					'command' => '*/5 * * * * /usr/sbin/maintain_sown_tunnel > /dev/null',
				),
				'update_sown_config' => array(
					'enabled' => 'true',
					'command' => '0 * * * * /usr/sbin/update_sown_config',
				),		
			),
                );

		$mod[] = __FILE__;

		static::send_uci_config('crontabs', $config, $mod);
	}


	public static function config_locations_v0_1_78(Model_Node $node) 
	{
		$config = array(
                        'feature' => array(
                                'sown_native' => array(
                                        'name' => 'sown_native',
                                        'macs' => '00:15:17:2f:0a:7a 00:1e:c9:b4:87:39 00:21:f2:24:21:10',
                                        'auth_type' => 'bridge',
                                        'client_type' => 'bridge',
				),

                                'sown_home' => array(
                                        'name' => 'sown_home',
                                        'macs' => '00:30:48:bf:e0:19',
                                        'auth_type' => 'tunnel',
                                        'client_type' => 'nat',
				),


                                'tunnelled_auth' => array(
                                        'name' => 'tunnelled_auth',
                                        'macs' => '00:30:48:bf:e0:1X',
                                        'auth_type' => 'tunnel',
                                        'client_type' => 'nat',
				),
			),
                );

		$mod[] = __FILE__;

		static::send_uci_config('locations', $config, $mod);
	}

}
