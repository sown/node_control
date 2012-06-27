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
		'uci_config_softflowd' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_softflowd_v0_1_78'
			),
		),
	);

	public static function credentials_v0_1_78(Model_Node $node, $version)
	{
		$static_files = Kohana::$config->load('system.default.static_files');
		
		$mod[] = $node->certificate;
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
		$mod[] = $node;

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
		);
		
		static::send_uci_config('sown_core', $config, $mod);
	}


	public static function config_sown_core_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = $node;
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
				$iface_config['dns'] = Kohana::$config->load('system.default.dns.host');
				
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
			
			$mod[] = $iface;
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
			// Also optional 'hwmode' which can be set to '11g',
			// we can use the database 'type' field here

			$mod[] = $interface;
			$mod[] = $interface->networkAdapter;
			$count++;
		}
		
		foreach ($node->interfaces as $interface)
		{
			if($interface->networkAdapter->wirelessChannel == null)
				continue;
			if (!isset($radio_id[$interface->id]))
				continue;

			$config['wifi-iface'][$interface->name] = array(
				'device' => $radio_id[$interface->id],
				'mode' => 'ap',
				'ssid' => $interface->ssid,
				'ifname' => $interface->name,
			);
			
			if($interface->is1x)
			{
				$mod[] = Kohana::$config->load('system.default.filename');
				$config['wifi-iface'][$interface->name]['encryption'] = 'wpa2+aes';
				$config['wifi-iface'][$interface->name]['server'] = Kohana::$config('system.default.radius.host');
				$config['wifi-iface'][$interface->name]['port'] = Kohana::$config('system.default.radius.port');
				$config['wifi-iface'][$interface->name]['key'] = $interface->radiusSecret;
				foreach(array('server', 'port', 'key') as $x)
				{
					$config['wifi-iface'][$interface->name]['auth_'.$x] = $config['wifi-iface'][$interface->name][$x];
					$config['wifi-iface'][$interface->name]['acct_'.$x] = $config['wifi-iface'][$interface->name][$x];
				}
				$config['wifi-iface'][$interface->name]['nasid'] = $node->FQDN;
			}
			$mod[] = $interface;
			$mod[] = $interface->networkAdapter;
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
					# TODO we don't need this yet
					#'dhcp_script'   => '/usr/sbin/dhcp_event',
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
			$mod[] = $iface;
		}

		static::send_uci_config('dhcp', $config, $mod);
	}

	public static function config_softflowd_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;
		$mod[] = $node;
		$mod[] = $node->vpnEndpoint;
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
						'enabled' => 1,
					)
				),
			);
			$mod[] = $iface;
		}
		
		static::send_uci_config('softflowd', $config, $mod);
	}
}
