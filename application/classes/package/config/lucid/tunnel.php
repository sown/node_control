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
	);

	public static function config_openvpn_v0_1_78(Model_Node $node)
	{
		$ep = $node->vpnEndpoint;
$conf = <<< EOB
# Comments are preceded with '#' or ';'

# Accept Connectionions on this port.
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
# TODO get routes from the database
push "route 10.12.0.0 255.254.0.0"
push "route 152.78.189.82 255.255.255.255"
push "route 152.78.189.90 255.255.255.255"

# Tell the client it must tell us when it is
# disconnecting. This prevents time-out errors
# and means routes come down at the right time
# This only works with udp.
;explicit-exit-notify

# Push these configurations to the client
# TODO get DNS servers from the database
push "dhcp-option DNS 10.13.0.254"

# Allow clients to see each other
# This is useless in sown-vpns configuration
client-to-client

# Send keep-alives
keepalive 10 120

# Maximum number of clients for this server

# Downgrade priveleges after initialization
user nobody
group nogroup

# Preserve as much as possible between restarts
persist-key
persist-tun

# Keep per-server log files
log /var/log/openvpn/server{$ep->id}.log
status /var/log/openvpn/server{$ep->id}-status.log

# Set logging verbosity to 3
verb 3
EOB;

		echo $conf;
	}
}
