<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dns extends Controller_AbstractAdmin
{
        public function before()
        {
                parent::before();
        }

	public function action_generate_dns()
        {
                #$this->check_ip($_SERVER['REMOTE_ADDR']);
                $this->auto_render = FALSE;
                $this->response->headers('Content-Type','text/plain');
                $zonetype = $this->request->param('zonetype');
                switch($zonetype)
                {
                       	case 'forward':
                               	$dns = $this->_build_hosts_forward_dns();
                               	break;
                       	case 'reverse_ipv4':
                               	$dns = $this->_build_hosts_reverse_ipv4_dns();
                               	break;
                       	case 'reverse_ipv6':
                               	$dns = $this->_build_hosts_reverse_ipv6_dns();
                               	break;
                       	default:
                               	throw new Exception("Unsupported DNS zone file type for $for");
                               	exit(1);
                }
                echo $dns;
        }

	private function _build_hosts_forward_dns()
        {
                $domain = Kohana::$config->load('system.default.admin_system.domain');
                $dns = "; Primary Services\n";
                list($dns2, $nss, $domain_server_interface) = $this->_build_ns_dns_entries(false);
                $dns .= $dns2;
                $www_interfaces = Doctrine::em()->getRepository('Model_ServerInterface')->createQueryBuilder('si')
                        ->where('si.hostname LIKE :hostname')->orWhere('si.cname LIKE :hostname')
                        ->setParameter('hostname', 'www')
                        ->setMaxResults(1)
                        ->getQuery()->getResult();
                $dns .= (is_object($www_interfaces[0]) ? "@\t\t\t\tIN\tA\t{$www_interfaces[0]->IPv4Addr}\n" : "");
                foreach ($nss as $ns => $ns_addrs)
                {
                        $dns .= "\n";
                        $dns .= $ns . SOWN::tabs($ns, 5) . "IN\tA\t{$ns_addrs[0]}\n";
                        $dns .= (!empty($ns_addrs[1]) ? $ns . SOWN::tabs($ns, 5) . "IN\tAAAA\t{$ns_addrs[1]}\n" : "");
                }
                if (is_object($www_interfaces[0]))
                {
                        $dns .= "\n";
                        $dns .= "www\t\t\t\tIN\tA\t{$www_interfaces[0]->IPv4Addr}\n";
                        $www_ipv6 = $www_interfaces[0]->IPv6Addr;
                        $dns .= (!empty($www_ipv6) ? "www\t\t\t\tIN\tAAAA\t{$www_ipv6}\n" : "");
                }
                $irc_server = Kohana::$config->load('system.default.irc_server');
                $dns .= "\n";
                $dns .= "_irc._tcp.$domain.".SOWN::tabs("_irc._tcp.$domain.", 5)."IN\tSRV\t1 0 6667\t$irc_server.\n";
                if (is_object($www_interfaces[0]))
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
                $servers = Doctrine::em()->getRepository('Model_Server')->findByRetired(0);
		foreach ($servers as $server)
                {
                        if ($server->hasLocalInterface())
                        {
                                foreach ($server->interfaces as $interface)
                                {
                                        $dns .= $this->_build_server_interface_forward_dns($interface);
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
		$other_hosts = Doctrine::em()->getRepository('Model_OtherHost')->findByRetired(0);
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
                return $dns;
        }

        private function _build_server_interface_forward_dns($interface)
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

        private function _build_hosts_reverse_ipv4_dns()
        {
                $domain = Kohana::$config->load('system.default.admin_system.domain');
                $local_vlan = Kohana::$config->load('system.default.vlan.local');
                $ipv4_rev_subnet =  Kohana::$config->load('system.default.dns.reverse_subnets.ipv4');
		$dns = $this-> _build_ns_dns_entries() . "\n";
                $local_addrs = Doctrine::em()->createQuery("SELECT si.IPv4Addr, si.hostname FROM Model_ServerInterface si JOIN si.vlan v JOIN si.server s WHERE v.name = '".$local_vlan."' AND s.retired != 1 AND si.IPv4Addr != '' ORDER BY si.IPv4Addr ASC")->getResult();
                foreach ($local_addrs as $addr)
                {
                        $rdns = SOWN::reverse_dns($addr['IPv4Addr'], $ipv4_rev_subnet, 4);
                        $dns .= "$rdns\tPTR\t" . $addr['hostname'] . ".$domain.\n";
                }
		$local_addrs_other = Doctrine::em()->getRepository('Model_OtherHost')->findBy(array('retired' => 0, 'internal' => 1));
		foreach ($local_addrs_other as $addr)
                {
			if (strlen($addr->IPv4Addr))
			{
                        	$rdns = SOWN::reverse_dns($addr->IPv4Addr, $ipv4_rev_subnet, 4);
                        	$dns .= "$rdns\tPTR\t" . $addr->hostname . ".$domain.\n";
			}
                }
                return $dns;
        }

	private function _build_hosts_reverse_ipv6_dns()
        {
                $domain = Kohana::$config->load('system.default.admin_system.domain');
                $local_vlan = Kohana::$config->load('system.default.vlan.local');
                $dns = $this-> _build_ns_dns_entries() . "\n";
                $local_addrs = Doctrine::em()->createQuery("SELECT si.IPv6Addr, si.hostname FROM Model_ServerInterface si JOIN si.vlan v JOIN si.server s WHERE v.name = '".$local_vlan."' AND s.retired != 1 AND si.IPv6Addr != '' ORDER BY si.IPv6Addr ASC")->getResult();
                $snbits = explode(':', Kohana::$config->load('system.default.dns.reverse_subnets.ipv6'));
                foreach ($snbits as $snb => $snbit)
                {
                        $snbits[$snb] = str_repeat('0',4 - strlen($snbit)) . $snbit;
                }
                $dns .= ';$ORIGIN ' . implode('.', array_reverse(str_split(implode('', $snbits)))) . ".ip6.arpa.\n\n";
                foreach ($local_addrs as $addr)
                {
                        $rdns = SOWN::reverse_dns($addr['IPv6Addr'], '', 6) . ".ip6.arpa.";
                        $dns .= "$rdns\tPTR\t" . $addr['hostname'] . ".$domain.\n";
                }
		$local_addrs_other = Doctrine::em()->getRepository('Model_OtherHost')->findBy(array('retired' => 0, 'internal' => 1));
                foreach ($local_addrs_other as $addr)
                {
			if (strlen($addr->IPv6Addr))
                        {
                        	$rdns = SOWN::reverse_dns($addr->IPv6Addr, '', 6);
                        	$dns .= "$rdns\tPTR\t" . $addr->hostname . ".$domain.\n";
			}
                }
                return $dns;
        }

        private function _build_ns_dns_entries($just_text = true)
        {
                $domain = Kohana::$config->load('system.default.admin_system.domain');
                $dns = "";
                $ns_interfaces = Doctrine::em()->getRepository('Model_ServerInterface')->createQueryBuilder('si')
                        ->where('si.hostname LIKE :hostname')->orWhere('si.cname LIKE :hostname')
                        ->orderBy('si.cname', 'ASC')
                        ->setParameter('hostname', 'ns%')
                        ->getQuery()->getResult();
                $nss = array();
                $domain_server_interface = null;
                foreach ($ns_interfaces as $nsi)
                {
                        $ns = (preg_match("/^ns/", $nsi->cname) ? $nsi->cname : $nsi->hostname);
			$ns = (strpos($ns, ',') ? substr($ns, 0, strpos($ns, ",")) : $ns);
                        $nss[$ns] = array($nsi->IPv4Addr, $nsi->IPv6Addr);
                        $dns .= "@\t\t\t\tIN\tNS\t$ns.$domain.\n";
                        $domain_server_interface = ($ns == "ns0" ? $nsi : $domain_server_interface);
                }
                return ($just_text ? $dns : array($dns, $nss, $domain_server_interface));
        }

}
