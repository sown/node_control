<?php defined('SYSPATH') or die('No direct script access.');

class DNSUtils {

 	public static function generateHostsForwardFragment($dir, $nameservers, $servers, $other_hosts, $wwwserver)
        {
		$domain = Kohana::$config->load('system.default.admin_system.domain');

                $dns = "; Primary Services\n";
                list($dns2, $nss, $domain_server_interface) = DNSUtils::generateNameserverEntries($nameservers, false);
                $dns .= $dns2;
                $dns .= (is_object($wwwserver) ? "@\t\t\t\tIN\tA\t{$wwwserver->IPv4Addr}\n" : "");
                foreach ($nss as $ns => $ns_addrs)
                {
                        $dns .= "\n";
                        $dns .= $ns . SOWN::tabs($ns, 5) . "IN\tA\t{$ns_addrs[0]}\n";
                        $dns .= (!empty($ns_addrs[1]) ? $ns . SOWN::tabs($ns, 5) . "IN\tAAAA\t{$ns_addrs[1]}\n" : "");
                }
                if (is_object($wwwserver))
                {
                        $dns .= "\n";
                        $dns .= "www\t\t\t\tIN\tA\t{$wwwserver->IPv4Addr}\n";
                        $www_ipv6 = $wwwserver->IPv6Addr;
                        $dns .= (!empty($www_ipv6) ? "www\t\t\t\tIN\tAAAA\t{$www_ipv6}\n" : "");
                }
                $irc_server = Kohana::$config->load('system.default.irc_server');
                $dns .= "\n";
                $dns .= "_irc._tcp.$domain.".SOWN::tabs("_irc._tcp.$domain.", 5)."IN\tSRV\t1 0 6667\t$irc_server.\n";
                if (is_object($wwwserver))
                {
                        $dns .= "_http._tcp.$domain.".SOWN::tabs("_http._tcp.$domain.", 5)."IN\tSRV\t1 0 80\t\twww.$domain.\n";
                        $dns .= "_http._tcp.$domain.".SOWN::tabs("_http._tcp.$domain.", 5)."IN\tSRV\t1 0 443\t\twww.$domain.\n";
                }
                if (is_object($domain_server_interface))
                {
                        $dns .= "_domain._udp.$domain.".SOWN::tabs("_domain._udp.$domain.", 5)."IN\tSRV\t1 0 53\t\t{$domain_server_interface->hostname}.$domain.\n";
                        $dns .= "_domain._tcp.$domain.".SOWN::tabs("_domain._tcp.$domain.", 5)."IN\tSRV\t1 0 53\t\t{$domain_server_interface->hostname}.$domain.\n";
                }
                $dns .= "\n\n; Servers\n";
		foreach ($servers as $server)
                {
                        if ($server->hasLocalInterface())
                        {
                                foreach ($server->interfaces as $interface)
                                {
                                        $dns .= DNSUtils::generateServerInterfaceDNSEntry($interface);
                                }
                                $dns .= "\n";
                        }
                }
                $dns .= "\n; Other CNAMEs\n";
                foreach ($servers as $server)
                {
                        if ($server->hasOnlyLocalCName())
                        {
                                foreach ($server->interfaces as $interface)
                                {
                                        foreach (explode(',', $interface->cname) as $cname)
                                        {
                                                $dns .= (!empty($cname) && $cname != "www" ? $cname . SOWN::tabs($cname, 4) . "IN\tCNAME\t" . $interface->hostname . ".\n" : "");
                                        }
                                }
                        }
                }
                $dns .= "\n; Other Hosts\n";
                foreach ($other_hosts as $other_host)
                {
                        if ($other_host->internal) {
                                $tabs = SOWN::tabs($other_host->hostname, 4);
                                $dns .= (strlen($other_host->IPv4Addr) ? $other_host->hostname . $tabs . "IN\tA\t" . $other_host->IPv4Addr. "\n" : "");
                                $dns .= (strlen($other_host->IPv6Addr) ? $other_host->hostname . $tabs . "IN\tAAAA\t" . $other_host->IPv6Addr. "\n" : "");
                                $dns .= $other_host->hostname . $tabs . "IN\tTXT\t" . "\"mac: {$other_host->mac} type:{$other_host->type}\"\n";
                        }
                        foreach (explode(',', $other_host->cname) as $cname)
                        {
                                $dns .= (!empty($cname) && !strpos($cname, '.') ? $cname . SOWN::tabs($cname, 4) . "IN\tCNAME\t" . $other_host->hostname . '.' . Kohana::$config->load('system.default.admin_system.domain') . ".\n" : "");
                        }
                }
		$file = "$dir/fragment.$domain-hosts";
                $handle = fopen($file, "w");
		fwrite($handle, "; Hosts Forward Fragment - Generated automatically using admin system's DNSUtils on ".date("Y-m-d H:i:s")."\n");	
                fwrite($handle, $dns);
                fclose($handle);
	}

	public static function generateHostsReverseFragment($dir, $nameservers, $server_interfaces, $other_hosts)
        {
		$dns_head = DNSUtils::generateNameserverEntries($nameservers) . "\n";
		$domain = Kohana::$config->load('system.default.admin_system.domain');
                $local_vlan = Kohana::$config->load('system.default.vlan.local');
                $ipv4_rev_subnet =  Kohana::$config->load('system.default.dns.reverse_subnets.ipv4');
		$dns4 = $dns_head;
                foreach ($server_interfaces as $addr)
                {
			if (strlen($addr['IPv4Addr']))
                        {
	                        $rdns = DNSUtils::reversePTR($addr['IPv4Addr'], $ipv4_rev_subnet, 4);
        	                $dns4 .= "$rdns\tPTR\t" . $addr['hostname'] . ".$domain.\n";
			}
                }
                foreach ($other_hosts as $addr)
                {
                        if (strlen($addr->IPv4Addr) && $addr->internal)
                        {
                                $rdns = DNSUtils::reversePTR($addr->IPv4Addr, $ipv4_rev_subnet, 4);
                                $dns4 .= "$rdns\tPTR\t" . $addr->hostname . ".$domain.\n";
                        }
                }
		$file4 = "$dir/fragment.$ipv4_rev_subnet-hosts";
                $handle4 = fopen($file4, "w");
		fwrite($handle4, "; Hosts IPv4 Reverse Fragment - Generated automatically using admin system's DNSUtils on ".date("Y-m-d H:i:s")."\n");
		fwrite($handle4, $dns4);
                fclose($handle4);

                $snbits = explode(':', Kohana::$config->load('system.default.dns.reverse_subnets.ipv6'));
                foreach ($snbits as $snb => $snbit)
                {
                        $snbits[$snb] = str_repeat('0',4 - strlen($snbit)) . $snbit;
                }
		$dns6 = $dns_head;
                $dns6 .= ';$ORIGIN ' . implode('.', array_reverse(str_split(implode('', $snbits)))) . ".ip6.arpa.\n\n";
                foreach ($server_interfaces as $addr)
                {
			if (strlen($addr['IPv6Addr'])) 
			{
                        	$rdns = DNSUtils::reversePTR($addr['IPv6Addr'], '', 6) . ".ip6.arpa.";
                        	$dns6 .= "$rdns\tPTR\t" . $addr['hostname'] . ".$domain.\n";
			}
                }
                foreach ($other_hosts as $addr)
                {
                        if (strlen($addr->IPv6Addr) && $addr->internal)
                        {
                                $rdns = DNSUtils::reversePTR($addr->IPv6Addr, '', 6);
                                $dns6 .= "$rdns\tPTR\t" . $addr->hostname . ".$domain.\n";
                        }
                }
		$file6 = "$dir/fragment.ip6ptr-hosts";
                $handle6 = fopen($file6, "w");
                fwrite($handle6, "; Hosts IPv6 Reverse Fragment - Generated automatically using admin system's DNSUtils on ".date("Y-m-d H:i:s")."\n");
		fwrite($handle6, $dns6);
                fclose($handle6);
        }

	public static function generateNodesForwardFragment($dir, $results)
	{
		$file = "$dir/fragment.sown.org.uk-nodes";
      		$handle = fopen($file, "w");

      		if($handle == false)
      		{
            		die('Failed to open file '.$file."\n");
      		}

      		fwrite($handle, "; Nodes Forward Fragment - Generated automatically using admin system's DNSUtils on ".date("Y-m-d H:i:s")."\n");
      		foreach($results as $r => $result)
      		{
                        $ipv4 = DNSUtils::convertToEndpoint($result['IPv4Addr'], 4);
                        $ipv6 = DNSUtils::convertToEndpoint($result['IPv6Addr'], 6);
            		$hostname = "node".$result['boxNumber'];
            		$lat = $result['latitude'];
            		$loc = "";
            		if (!empty($lat) && floatval($lat) != 0) 
			{
                  		$lat = SOWN::decimal_to_minute_second_degrees($lat, 'latitude');
                  		$long = SOWN::decimal_to_minute_second_degrees($result['longitude'], 'longitude');
                  		$loc = "${lat[0]} ${lat[1]} ${lat[2]} ${lat[3]} ${long[0]} ${long[1]} ${long[2]} ${long[3]} 10m 100m 60m 60m";
            		}
            		$type = $result['type'];
            		if (empty($type)) $type = "unknown";
            		$txt = "\"mac:".$result['mac'].";type:$type;firmware:".$result['firmwareImage'].";\"";
            		if(strlen($hostname) > 4)
            		{
                  		if(preg_match("/[A-Za-z0-9-_]+/", $hostname))
                  		{
                        		fwrite($handle, $hostname."\tIN\tA\t".$ipv4."\n");
                        		fwrite($handle, $hostname."\tIN\tAAAA\t".$ipv6."\n");
                        		if (!empty($loc)) fwrite($handle, $hostname."\tIN\tLOC\t".$loc."\n");
					fwrite($handle, $hostname."\tIN\tTXT\t".$txt."\n");
				}
			}
		}
		fwrite($handle, ";\n");
		fclose($handle);
	}

	public static function generateNodesReverseFragment($dir, $results)
	{
		$ipv4_rev_subnet =  Kohana::$config->load('system.default.dns.reverse_subnets.ipv4');
		$file4 = "$dir/fragment.$ipv4_rev_subnet-nodes";
		$handle4 = fopen($file4, "w");
      		$file6 = "$dir/fragment.ip6ptr-nodes";
      		$handle6 = fopen($file6, "w");
		
		if($handle4 == false)
      		{
            		die('Failed to open file '.$file6."\n");
      		}
      		if($handle6 == false)
      		{
            		die('Failed to open file '.$file6."\n");
      		}

		fwrite($handle4, "; Nodes IPv4 Reverse Fragment - Generated automatically using admin system's DNSUtils on ".date("Y-m-d H:i:s")."\n");
      		fwrite($handle6, "; Nodes IPv6 Reverse Fragment - Generated automatically using admin system's DNSUtils on ".date("Y-m-d H:i:s")."\n");
      		foreach ($results as $r => $result)
      		{
			$ipv4 = DNSUtils::convertToEndpoint($result['IPv4Addr'], 4);
                        $ipv6 = DNSUtils::convertToEndpoint($result['IPv6Addr'], 6);
            		$hostname = "node".$result['boxNumber'];

            		if(strlen($hostname) > 4)
            		{
                  		if(preg_match("/[A-Za-z0-9-_]+/", $hostname))
                  		{
                        		fwrite($handle4, DNSUtils::reversePTR($ipv4, $ipv4_rev_subnet, 4) . "\tPTR\t".$hostname.".sown.org.uk.\n");
                        		fwrite($handle6, DNSUtils::reversePTR($ipv6, '', 6) . "\tPTR\t".$hostname.".sown.org.uk.\n");
                  		}	
            		}
      		}
		fwrite($handle4, ";\n");
      		fclose($handle4);
      		fwrite($handle6, ";\n");
      		fclose($handle6);
	}

	public static function generateZoneHeader($dir) 
	{
      		$handle = fopen($dir.'/db.sown.org.uk', 'w');
      		fwrite($handle, '$TTL    86400
@       IN      SOA     sown.org.uk. support.sown.org.uk. (
                 '.date('YmdH').'   ; Serial
                          86400         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                          86400 )       ; Negative Cache TTL
;

$INCLUDE "/etc/bind/fragment.sown.org.uk-hosts"
$INCLUDE "/etc/bind/fragment.sown.org.uk-nodes"
;$INCLUDE "/etc/bind/fragment.sown.org.uk-users"
;$INCLUDE "/etc/bind/fragment.sown.org.uk-streams"
');
      		fclose($handle);
	}
	
	public static function generateReverseZoneIPv4Header($dir) 
	{
		$handle = fopen($dir.'/db.10.13', 'w');
      		fwrite($handle, '$TTL    86400
@       IN      SOA     sown.org.uk. support.sown.org.uk. (
                 '.date('YmdH').'   ; Serial
                          86400         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                          86400 )       ; Negative Cache TTL
;

$INCLUDE "/etc/bind/fragment.10.13-hosts"
$INCLUDE "/etc/bind/fragment.10.13-nodes"
;$INCLUDE "/etc/bind/fragment.10.13-users"
');
      		fclose($handle);
	}

	public static function generateReverseZoneIPv6Header($dir) 
	{
      		$handle = fopen($dir.'/db.ip6ptr', 'w');
      		fwrite($handle, '$TTL    86400
@       IN      SOA     sown.org.uk. support.sown.org.uk. (
                 '.date('YmdH').'   ; Serial
                          86400         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                          86400 )       ; Negative Cache TTL
;

$INCLUDE "/etc/bind/fragment.ip6ptr-hosts"
$INCLUDE "/etc/bind/fragment.ip6ptr-nodes"
');
      		fclose($handle);
	}

	private static function generateNameserverEntries($nameservers, $just_text = true)
	{
                $domain = Kohana::$config->load('system.default.admin_system.domain');
                $dns = "";
                $nss = array();
                $domain_server_interface = null;
                foreach ($nameservers as $nsi)
                {
                        $ns = (preg_match("/^ns/", $nsi->cname) ? $nsi->cname : $nsi->hostname);
                        $ns = (strpos($ns, ',') ? substr($ns, 0, strpos($ns, ",")) : $ns);
                        $nss[$ns] = array($nsi->IPv4Addr, $nsi->IPv6Addr);
                        $dns .= "@\t\t\t\tIN\tNS\t$ns.$domain.\n";
                        $domain_server_interface = ($ns == "ns0" ? $nsi : $domain_server_interface);
                }
                return ($just_text ? $dns : array($dns, $nss, $domain_server_interface));
        }
	
	private static function reversePTR($addr, $subnet = '', $protocol = 4)
	{
                if ($protocol == 4)
                {
                        $leftaddr = preg_replace("/^$subnet\.*/", "", $addr);
                        return implode('.', array_reverse(explode('.', $leftaddr)));
                }
                elseif ($protocol == 6)
                {
                        if (strpos($addr, "::") !== FALSE)
                        {
                                $addr_bits = explode(':', $addr);
                                $addr_filler = array();
                                for ($a = sizeof($addr_bits); $a < 8; $a++)
                                {
                                        $addr_filler[] = "0000";
                                }
                                $addr = str_replace("::", ':' . implode(':', $addr_filler) . ':', $addr);
                        }
                        $leftaddr = preg_replace("/^$subnet/", "", $addr);
                        $addr_bits = explode(':', $leftaddr);
                        foreach($addr_bits as $ab => $addr_bit)
                        {
                                $addr_bits[$ab] = (strlen($addr_bit) > 0 && strlen($addr_bit) < 4 ? $addr_bits[$ab] = str_repeat('0',4 - strlen($addr_bit)) . $addr_bit : $addr_bit);
                        }
                        $leftaddr = implode(':', $addr_bits);
                        return implode('.', str_split(strrev(str_replace(':', '', $leftaddr)))).".ip6.arpa.";
                }
        }

	private static function convertToEndpoint($ip, $type = 4 ) 
	{
      		if (empty($ip) || !in_array($type, array(4,6))) return $ip;
      		if ($type == 4) 
		{
            		$ipbits = explode(".", $ip);
            		$ipbits[sizeof($ipbits)-1] = $ipbits[sizeof($ipbits)-1] + 2;
            		return implode(".", $ipbits);
      		}
      		else 
		{
            		$ipbits = explode(":", $ip);
            		$ipbits[sizeof($ipbits)-1] = dechex(hexdec($ipbits[sizeof($ipbits)-1]) + 2);
            		return implode(":", $ipbits);
      		}
      		return $ip;
	}

	private static function generateServerInterfaceDNSEntry($interface)
	{
		$dns = "";
                $ipv4 = $interface->IPv4Addr;
                $ipv6 = $interface->IPv6Addr;
                if (is_object($interface->vlan) && strlen($interface->hostname) && (!empty($ipv4) || !empty($ipv6)))
                {
                        $cname_list = $interface->cname;
                        if ($interface->vlan->name == Kohana::$config->load('system.default.vlan.local'))
                        {
                                $tabs = SOWN::tabs($interface->hostname, 4);
                                $dns .= $interface->hostname . $tabs . "IN\tA\t" . $ipv4 . "\n";
                                $dns .= (!empty($ipv6) ? $interface->hostname . $tabs . "IN\tAAAA\t" . $ipv6 . "\n" : "");
                                foreach (explode(',', $cname_list) as $cname)
                                {
                                        $dns .= (!empty($cname) && !preg_match("/^(ns[0-9]|www)$/", $cname) && !strpos($cname, '.') ? $cname . SOWN::tabs($cname, 4) . "IN\tCNAME\t" . $interface->hostname . "." . Kohana::$config->load('system.default.admin_system.domain') . ".\n" : "");
                                }
                                $dns .= $interface->hostname . $tabs . "IN\tTXT\t" . "\"mac: ".$interface->mac." type:server\"\n";
                                $dns .= $interface->hostname . $tabs . "IN\tHINFO\t\"".$interface->server->processor."\" \"".$interface->server->kernel."\"\n";
                        }
                        elseif (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.external')))
                        {
                                $hostname_bits = explode('.', $interface->hostname);
                                $hostname = $hostname_bits[0];
                                $tabs = SOWN::tabs($hostname, 4);
                                $dns .= $hostname . $tabs . "IN\tA\t" . $ipv4 . "\n";
                                $dns .= (!empty($ipv6) ? $hostname . $tabs . "IN\tAAAA\t" . $ipv6 . "\n" : "");
                                foreach (explode(',', $cname_list) as $cname)
                                {
                                        $dns .= (!empty($cname) && !preg_match("/^(ns[0-9]|www)$/", $cname) && !strpos($cname, '.') ? $cname . SOWN::tabs($cname, 4) . "IN\tCNAME\t" . $interface->hostname . ".\n" : "");
                                }
                                $dns .= $hostname . $tabs . "IN\tTXT\t\"mac: ".$interface->mac." type:server\"\n";
                                $dns .= $hostname . $tabs . "IN\tHINFO\t\"".$interface->server->processor."\" \"".$interface->server->kernel."\"\n";
                        }
                }
                return $dns;
	}
}
