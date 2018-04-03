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

		'config_openvpn_2_4' => array(
                        array(
                                '>=' => '0.1.78',
                                'method' => 'config_openvpn_2_4_v0_1_78'
                        ),
                ),

		'config_client_routes' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_client_routes_v0_1_78'
			),
		),
	);

	# If called with no node, this function will call itself
	# to generate a tar file of config files
	public static function config_openvpn_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$files = array();
			$repository = Doctrine::em()->getRepository('Model_Node');
			foreach($repository->findByUndeployable(0) as $node)
			{
				$fn =  __function__;
				$files = array_merge($files, static::$fn($node));
			}
			static::send_tgz($files, array());

			# morse: Can this fall-through?
		}

		if($node->certificate->cn == "")
		{
			# No certificate defined
			return array();
		}

		$ep = $node->vpnEndpoint;

		if (!is_object($ep))
		{
			# No endpoint
			return array();
		}

		# Look for any subnets we should route:
		$iroute_v6 = array();
		foreach($node->interfaces as $i)
		{
			if($i->IPv6 != null && strpos($i->name, "wlan") !== FALSE)
			{
				$iroute_v6[] = "iroute-ipv6 " . $i->IPv6->get_network_start() . "/" . $i->IPv6AddrCidr;
			}
		}

		$iroute_v6 = join("\r\n", $iroute_v6);

		$dns_host = Kohana::$config->load('system.default.dns.host');
		$routes = trim(Kohana::$config->load('system.default.routes'));
$conf = <<< EOB
# Comments are preceded with '#' or ';'

# Bind to the server address that the nodes know about.
# This breaks on udp as the server may reply with a different src_ip.
local {$ep->vpnServer->getPrimaryIPAddress()}

# Accept Connections on this port.
port {$ep->port}

# sown-vpn is correctly configured to use udp
proto {$ep->protocol}

# sown-vpn uses tap tunnels
dev tap{$ep->id}

# Locations of SSL files
ca /etc/openvpn/package_managment/{$node->certificate->ca}
cert /etc/openvpn/package_managment/server-{$node->certificate->ca}
key /etc/openvpn/package_managment/server-{$node->certificate->ca}.key

# Diffie Hellman Parameters
dh /etc/openvpn/dh1024.pem

# Use this subnet for this client
server {$ep->IPv4->get_network_address()} {$ep->IPv4->get_subnet_mask()}

# IPv6 on tunnel network
tun-ipv6
push tun-ipv6
ifconfig-ipv6 {$ep->IPv6->get_network_start()}1/{$ep->IPv6AddrCidr} {$ep->IPv6->get_network_start()}2 
push ifconfig-ipv6 {$ep->IPv6->get_network_start()}2/{$ep->IPv6AddrCidr} {$ep->IPv6->get_network_start()}1
push route-ipv6 2001:630:d0:f700::/56

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
client-connect "/etc/openvpn/client-routes/connect-{$node->certificate->cn}"
client-disconnect "/etc/openvpn/client-routes/disconnect-{$node->certificate->cn}"

EOB;

		return array('server'.$ep->id.'.conf' => array(
			'content' => $conf,
			'mtime'   => $ep->lastModified->getTimestamp(),
		));
	}

	# If called with no node, this function will call itself
        # to generate a tar file of config files
        public static function config_openvpn_2_4_v0_1_78(Model_Node $node = null, $ip = null)
        {
                if($node === null)
                {
                        $files = array();
			if (!empty($ip))
			{
				$interface = Doctrine::em()->getRepository('Model_ServerInterface')->findOneBy(array("IPv4Addr" => $ip));
				if (!empty($interface))
				{
					$vpnServers = $interface->server->vpnServers;
					if (!empty($vpnServers[0]))
					{
			                        foreach(Doctrine::em()->getRepository('Model_Node')->findByUndeployable(0) as $node)
        			                {	
							if ($node->vpnEndpoint && $node->vpnEndpoint->vpnServer->id == $vpnServers[0]->id)
							{
        	        		        		$fn =  __function__;
	                	        		        $files = array_merge($files, static::$fn($node, $ip));
							}
						}
					}
					else 
					{
						throw new HTTP_Exception_404("Server with IPv4 interface address has no VPN servers.");
					}
                        	}
				else
				{
					throw new HTTP_Exception_404("IPv4 address does not correspond to a server interface.");
				}
			}
			else 
			{
				throw new HTTP_Exception_404("No IPv4 address provided.");
			}
                        static::send_tgz($files, array());

                        # morse: Can this fall-through?
                }

                if($node->certificate->cn == "")
                {
                        # No certificate defined
                        return array();
                }

                $ep = $node->vpnEndpoint;
                $dns_host = Kohana::$config->load('system.default.dns.host');
                $routes = trim(Kohana::$config->load('system.default.routes'));

$conf = <<< EOB
# Comments are preceded with '#' or ';'

# Bind to the server address that the nodes know about.
# This breaks on udp as the server may reply with a different src_ip.
local {$ip}

# Accept Connections on this port.
port {$ep->port}

# sown-vpn is correctly configured to use udp
proto {$ep->protocol}

# sown-vpn uses tap tunnels
dev tap{$ep->id}

# Locations of SSL files
ca /etc/openvpn/package_managment/{$node->certificate->ca}
cert /etc/openvpn/package_managment/server-{$node->certificate->ca}
key /etc/openvpn/package_managment/server-{$node->certificate->ca}.key

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

script-security 3
client-connect "/etc/openvpn/client-routes/connect-{$node->certificate->cn}"
client-disconnect "/etc/openvpn/client-routes/disconnect-{$node->certificate->cn}"

EOB;

                return array('server'.$ep->id.'.conf' => array(
                        'content' => $conf,
                        'mtime'   => $ep->lastModified->getTimestamp(),
                ));
        }

	# If called with no node, this function will call itself
	# to generate a tar file of config files
	public static function config_client_routes_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$files = array();
			$repository = Doctrine::em()->getRepository('Model_Node');
			foreach($repository->findAll() as $node)
			{
				$fn =  __function__;
				$files = array_merge($files, static::$fn($node));
			}
			static::send_tgz($files, array());

			# morse: Can this fall-through?
		}

		if($node->certificate->cn == "")
		{
			# No certificate defined
			return array();
		}

		$ep = $node->vpnEndpoint;

                if (!is_object($ep))
                {
                        # No endpoint
                        return array();
                }

		$confconnect = "#!/bin/bash\n\n";
		$confdisconnect = "#!/bin/bash\n\n";
		foreach($node->interfaces as $iface)
		{
			if( ! ( (!$iface->offerDhcp) && ($iface->IPv4AddrCidr != 32) )){
				$confconnect    .= "/usr/bin/sudo /sbin/ip route add ".$iface->IPv4->get_network_identifier()." via ".$ep->IPv4->get_address_in_network(2)."\n";
				$confdisconnect .= "/usr/bin/sudo /sbin/ip route del ".$iface->IPv4->get_network_identifier()." via ".$ep->IPv4->get_address_in_network(2)."\n";
			}
			if($iface->offerDhcpV6){
				$confconnect	.= "/usr/bin/sudo /sbin/ip -6 route add ".$iface->IPv6->get_network_identifier()." via ".$ep->IPv6->get_address_in_network(2)."\n";
				$confconnect 	.= "echo \"iroute-ipv6 ".$iface->IPv6->get_network_identifier()."\" >> \$1";
				$confdisconnect	.= "/usr/bin/sudo /sbin/ip -6 route del ".$iface->IPv6->get_network_identifier()." via ".$ep->IPv6->get_address_in_network(2)."\n";
			}
		}
		$confconnect .= "\nexit 0\n";
		$confdisconnect .= "\nexit 0\n";

		return array(
			'connect-'.$node->certificate->cn => array(
				'content' => $confconnect,
				'mtime'   => $node->lastModified->getTimestamp(),
			),
			'disconnect-'.$node->certificate->cn => array(
				'content' => $confdisconnect,
				'mtime'   => $node->lastModified->getTimestamp(),
			),
		);
	}

}
