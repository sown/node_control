<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Backfire_Tunnel extends Package_Config
{
	const package_name = "sown_openwrt_tunnel";

	public static $supported = array(
		'uci_config_openvpn' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_openvpn_v0_1_78'
			),
		),
	);

	public static function config_openvpn_v0_1_78(Model_Node $node)
	{
		$mod[] = __FILE__;

		$config = array(
			'openvpn' => array(
				'sown_tunnel' => array(
					'enable' => 1,
					'client' => 1,
					
					'remote' => array(
						// Connect to the server by DNS name
						$node->vpnEndpoint->vpnServer->name .' '. $node->vpnEndpoint->port,
						// IP address failover incase of DNS lookup failure
						$node->vpnEndpoint->vpnServer->externalIPv4 .' '. $node->vpnEndpoint->port,
					),
					'proto' =>  $node->vpnEndpoint->protocol,
					
					// The server uses tap tunnels, so must you
					'dev' => 'tap',
					
					// Always try to reconnect
					'resolv_retry' => 'infinite',
					
					// No need to bind to a specific port
					'nobind' => 1,
					
					// Locations of SSL files
					'ca'   => '/etc/sown/ca.crt',
					'cert' => '/etc/sown/client.crt',
					'key'  => '/etc/sown/client.key',
					
					// Downgrade priveleges after initialization
					'user' => 'nobody',
					'group' => 'nogroup',
					
					// preserve keys, because they drain entropy to negotiate
					'persist_key' => 1,

					// we need to not persist-tun, so that the tunnel_down script is called sooner, rather then later
					'persist_tun' => 0,
					
					// Turn on some stuff for logging
					'verb' => 3,
					
					'script_security' => 2,
					
					'up'   => '/etc/sown/events/tunnel_up',
					'down' => '/etc/sown/events/tunnel_down'
				),
			)
		);

		// 2015/5/2 morse: this is temporary
		if ($node->certificate->ca == "node_control_2015.crt") {
			$config['openvpn']['sown_tunnel']['ca']="/etc/sown/".$node->certificate->ca;
		}


		static::send_uci_config('openvpn', $config, $mod);
	}
}
