<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Lucid_Tunnel extends Package_Config
{
	const package_name = "sown_openwrt_tunnel";

	public static $supported = array(
		'config_openvpn' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_openvpn_v0_1_78'
			),
		),
		'config_client_routes' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_client_routes_v0_1_78'
			),
		),
	);

	public static function config_openvpn_v0_1_78(Model_Node $node)
	{
		$ep = $node->vpnEndpoint;
		$dns_host = Kohana::$config->load('system.default.dns.host');
		$routes = trim(Kohana::$config->load('system.default.routes'));

$conf = <<< EOB
# Comments are preceded with '#' or ';'

# Accept Connections on this port.
port {$ep->port}

# sown-vpn is correctly configured to use udp
proto {$ep->protocol}

# sown-vpn uses tap tunnels
dev tap{$ep->id}

# Locations of SSL files
ca /etc/openvpn/package_managment/ca.crt
cert /etc/openvpn/package_managment/vpnserver.crt
key /etc/openvpn/package_managment/vpnserver.key

# Diffie Hellman Parameters
dh /etc/openvpn/dh1024.pem

# Use this subnet for this client
server {$ep->IPv4->get_network_address()} {$ep->IPv4->get_subnet_mask()}

# Push these routes to the client
{$routes}

# Tell the client it must tell us when it is
# disconnecting. This prevents time-out errors
# and means routes come down at the right time
# This only works with udp.
;explicit-exit-notify

# Push these configurations to the client
push "dhcp-option DNS {$dns_host}"

# Allow clients to see each other
# This is useless in sown-vpns configuration
client-to-client

# Send keep-alives
keepalive 10 120

# Maximum number of clients for this server

# Downgrade priveleges after initialization
user openvpn
group openvpn

# Preserve as much as possible between restarts
persist-key
persist-tun

# Keep per-server log files
log /var/log/openvpn/server{$ep->id}.log
status /var/log/openvpn/server{$ep->id}-status.log

# Set logging verbosity to 3
verb 3

script-security 3 system
client-connect "/usr/bin/sudo /etc/openvpn/client-routes/client{$node->boxNumber}"

EOB;

		echo $conf;
	}

	public static function config_client_routes_v0_1_78(Model_Node $node)
	{
		echo "#!/bin/bash\n\n";
		foreach($node->interfaces as $iface)
		{
			if(!$iface->offerDhcp)
				continue;
			echo "/sbin/ip route add ".$iface->IPv4->get_network_identifier()." via ".$node->vpnEndpoint->IPv4->get_address_in_network(2)."\n";
		}
		echo "\nexit 0\n";
	}

}
