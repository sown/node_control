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
		$authorized_keys = '/srv/www/auth/config/authorized_keys';
# SHOULDN'T PASS THIS FILE
		$passwd_file = '/srv/www/auth/config/passwd';
		
		$last_mod = max($node->certificate->lastModified->getTimestamp(), filemtime($authorized_keys), filemtime($passwd_file));
		
		if ($since = strtotime(Request::$current->headers('if-modified-since')))
		{
			if ($since >= $last_mod)
			{
				// No need to send data
				$r->status(304);
				// This request is finished
				return;
			}
		}

		static::send_tgz(array(
			'client.crt'      => array(
				'content' => $node->certificate->publicKey,
				'mtime'   => $node->certificate->lastModified->getTimestamp(),
			),
			'client.key'      => array(
				'content' => $node->certificate->privateKey,
				'mtime'   => $node->certificate->lastModified->getTimestamp(),
			),
			'authorized_keys' => $authorized_keys,
#			'passwd'          => $passwd_file,
		));
	}

	public static function config_system_v0_1_78(Model_Node $node)
	{
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
		
		static::send_uci_config('sown_core', $config);
	}


	public static function config_sown_core_v0_1_78(Model_Node $node)
	{
		$last_mod = max(filemtime(__FILE__), $node->lastModified->getTimestamp());
		$deployment = $node->currentDeployment;

		if ($deployment !== NULL)
		{
			$node_name = $deployment->name;
			$last_mod = max($last_mod, $deployment->lastModified->getTimestamp());
		}
		else
		{
			$node_name = $node->hostname;
		}
		
		$config = array(
			'node' => array(
				array(
					'config_URL' => 'https://sown-auth2.ecs.soton.ac.uk/package/config/backfire/',
					'hostname'   => $node->hostname,
					'node_name'  => $node_name,
					'id'         => $node->id,
				)
			)
		);
		
		static::send_uci_config('sown_core', $config, $last_mod);
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

		$last_mod = filemtime(__FILE__);
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
				$iface_config['dns'] = '10.13.0.254';
				
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
				$iface_config['gateway'] = '10.13.0.254';
			}
			
			$config['interface'][$iface->name] = $iface_config;
			
			$last_mod = max($last_mod, $iface->lastModified->getTimestamp());
		}
		
		static::send_uci_config('network', $config, $last_mod);
	}

	public static function config_sown_firewall_v0_1_78(Model_Node $node)
	{
		// TODO load the config from the database.
		$config = array(
			'feature' => array(
				'port_filter' => array(
					'enabled' => 'false',
				),
				'layer7' => array(
					'enabled' => 'false',
				),
				'IP_blacklist' => array(
					'enabled' => 'false',
				),
			)
		);
		
		static::send_uci_config('sown_firewall', $config, filemtime(__FILE__));
	}

	public static function config_wireless_v0_1_78(Model_Node $node)
	{
		$config = array();

		$last_mod = filemtime(__FILE__);
		
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

			$last_mod = max($last_mod, $interface->lastModified->getTimestamp());
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
				$config['wifi-iface'][$interface->name]['encryption'] = 'wpa2+aes';
				$config['wifi-iface'][$interface->name]['server'] = '10.13.0.252';
				$config['wifi-iface'][$interface->name]['port'] = 1812;
				$config['wifi-iface'][$interface->name]['key'] = 'accidentswillhappen';
				foreach(array('server', 'port', 'key') as $x)
				{
					$config['wifi-iface'][$interface->name]['auth_'.$x] = $config['wifi-iface'][$interface->name][$x];
					$config['wifi-iface'][$interface->name]['acct_'.$x] = $config['wifi-iface'][$interface->name][$x];
				}
				$config['wifi-iface'][$interface->name]['nasid'] = $node->FQDN;
			}
			$last_mod = max($last_mod, $interface->lastModified->getTimestamp());
		}
		
		static::send_uci_config('wireless', $config, $last_mod);
	}

	public static function config_dhcp_v0_1_78(Model_Node $node)
	{
		$last_mod = filemtime(__FILE__);
		
		$config = array(
			'dnsmasq' => array(
				array(
					'leasefile'     => '/var/state/dhcp.leases',
					'domain'        => 'sown.org.uk',
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
				$if_config['dhcp_option'] = '42,193.62.22.74';
			}
			else
			{
				$if_config['ignore'] = 1;
			}
			
			$config['dhcp'][$iface->name] = $if_config;
			$last_mod = max($last_mod, $iface->lastModified->getTimestamp());
		}

		static::send_uci_config('dhcp', $config, $last_mod);
	}

	public static function config_softflowd_v0_1_78(Model_Node $node)
	{
		$config = array(
			'softflowd' => array(
				// TODO create one of these for each interface which offers dhcp.
				array(
					// TODO This interface name is hardcoded
					'interface' => 'wifi0',
					// 'pcap_file' => '',
					// 'timeout' => '',
					'max_flows' => 8192,
					// TODO this port number is a bit random.
					'host_port' => '152.78.189.84:'.$node->vpnEndpoint->port,
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
		// TODO update this once the above config generation is fixed
		$last_mod = max(filemtime(__FILE__), $node->lastModified->getTimestamp(), $node->vpnEndpoint->lastModified->getTimestamp());
		
		static::send_uci_config('softflowd', $config);
	}
}
