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
		'clients_list' => array(
			array(
				'>=' => '0',
				'method' => 'clients_list',
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
#		$passwd_file = '/srv/www/auth/config/data/etc/passwd';
		
		$last_mod = max($node->client_certificate->last_modified, filemtime($authorized_keys), filemtime($passwd_file));
		
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
				'content' => PKI::PEM_encode_certificate($node->client_certificate->public_key),
				'mtime'   => $node->client_certificate->last_modified,
			),
			'client.key'      => array(
				'content' => PKI::PEM_encode_key($node->client_certificate->private_key),
				'mtime'   => $node->client_certificate->last_modified,
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
		$last_mod = max(filemtime(__FILE__), $node->last_modified);
		$deployment = $node->getCurrentDeployment();

		if ($deployment !== NULL)
		{
			$node_name = $deployment->name;
			$last_mod = max($last_mod, $deployment->last_modified);
		}
		else
		{
			$node_name = $node->hostname;
		}
		
		$config = array(
			'node' => array(
				array(
					'config_URL' => 'https://sown-auth.ecs.soton.ac.uk/pkg/config/backfire/',
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

		$interfaces = array();
		foreach ($node->physical_interfaces as $iface)
			$interfaces[] = $iface;
		foreach ($node->wireless_interfaces as $iface)
			$interfaces[] = $iface;

		$last_mod = filemtime(__FILE__);
		foreach ($interfaces as $iface)
		{
			$iface_config = array();
			
			$iface_config['ifname'] = $iface->name;
			
			$iface_config['proto'] = $iface->mode;
			
			if ($iface->mode == 'static')
			{
				$v4_net_addr = IPv4_Network_Address::factory($iface->ipv4_address, $iface->ipv4_subnet);
				
				$iface_config['ipaddr'] = $v4_net_addr->get_address();
				// TODO should tap0 be given a subnet mask?
				$iface_config['netmask'] = $v4_net_addr->get_subnet_mask();
				// TODO get DNS servers for static IPs from the database
				$iface_config['dns'] = '10.13.0.254';
				
				if($iface->ipv6_address)
				{
					$v6_net_addr = IPv6_Network_Address::factory($iface->ipv6_address, $iface->ipv6_subnet);

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
			
			$last_mod = max($last_mod, $iface->last_modified);
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
		foreach ($node->radios as $radio)
		{
			if ($radio->type == 'atheros')
			{
				// Madwifi requires this to match the actual device name
				$dev_name = 'wifi'.$count;
			}
			else
			{
				// mac80211 uses this identifier for co-ordination only
				// it looks up the actual device name using the mac address
				// TODO test this is ok
				$dev_name = 'radio'.$radio->id;
			}
			
			$radio_id[$radio->id] = $dev_name;
			
			$config['wifi-device'] = array(
					$dev_name => array(
						'type' => $radio->type,
						'channel' => $radio->channel,
						'macaddr' => $radio->mac_address,
					),
				);
				
			$last_mod = max($last_mod, $radio->last_modified);
			$count++;
		}
		
		foreach ($node->wireless_interfaces as $interface)
		{
			if (!isset($radio_id[$interface->radio->id]))
				continue;
			
			$config['wifi-iface'][$interface->name] = array(
				'device' => $radio_id[$interface->radio->id],
				'mode' => 'ap',
				'ssid' => $interface->ssid,
				'encryption' => $interface->encryption,
				'ifname' => $interface->name,
			);
			
			if($interface->encryption == 'wpa2+aes')
			{
				$config['wifi-iface'][$interface->name]['server'] = '10.13.0.252';
				$config['wifi-iface'][$interface->name]['port'] = 1812;
				$config['wifi-iface'][$interface->name]['key'] = 'accidentswillhappen';
				foreach(array('server', 'port', 'key') as $x)
				{
					$config['wifi-iface'][$interface->name]['auth_'.$x] = $config['wifi-iface'][$interface->name][$x];
					$config['wifi-iface'][$interface->name]['acct_'.$x] = $config['wifi-iface'][$interface->name][$x];
				}
				$config['wifi-iface'][$interface->name]['nasid'] = $node->getFQDN();
			}
			$last_mod = max($last_mod, $interface->last_modified);
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
					'dhcp_script'   => '/usr/sbin/dhcp_event',
				),
			),
		);
		
		$interfaces = array();
		foreach ($node->physical_interfaces as $iface)
			$interfaces[] = $iface;
		foreach ($node->wireless_interfaces as $iface)
			$interfaces[] = $iface;

		foreach ($interfaces as $iface)
		{
			$if_config = array();
			$if_config['interface'] = $iface->name;
			
			if ($iface->offer_dhcp)
			{
				$v4_net_addr = IP_Network_Address::factory($iface->ipv4_address, $iface->ipv4_subnet);
				$if_config['start'] = $v4_net_addr->get_address_in_network(0);
				$if_config['limit'] = $v4_net_addr->get_address_in_network(-2);
				$if_config['leasetime'] = '1h';
				$if_config['dhcp_option'] = '42,193.62.22.74';
			}
			else
			{
				$if_config['ignore'] = 1;
			}
			
			$config['dhcp'][$iface->name] = $if_config;
			$last_mod = max($last_mod, $iface->last_modified);
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
					'host_port' => '152.78.189.84:'.$node->vpn_server->port,
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
		$last_mod = max(filemtime(__FILE__), $node->last_modified, $node->vpn_server->last_modified);
		
		static::send_uci_config('softflowd', $config);
	}
	
	public static function clients_list(Model_Node $node)
	{
		$users = Jelly::query('user')
			// logged in recently, or locked to this node
			->and_where_open()
				->where('last_logged_in', '>=', $node->getUpdatePoint())
				->or_where('node_lock', '=', $node->old_id)
			->and_where_close()
			// Not node-locked, or locked to this node.
			->and_where_open()
				->where('only_node_id', '=', 0)
				->or_where('only_node_id', '=', $node->old_id)
			->and_where_close()
			// Not logged-out
			->where('logged_out', '=', 'no')
			// and not over bandwidth on this node.
			->test_over_bandwidth($node, false)
			->select_column(':primary_key');
			
		$records = Jelly::query('UserMac')
			->where('user' , 'IN', $users)
			->with('user')
			->select();
		
		// TODO get the groups for the users and the node and output chains accordingly
		foreach ($records as $rec) {
			if (empty($rec->mac_address))
				continue;
			
			echo $rec->mac_address ."\t\n";
		}
	}
}
