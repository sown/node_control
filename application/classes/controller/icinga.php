<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Icinga extends Controller_AbstractAdmin
{
 	public function action_hosts()
        {
                $this->check_ip($_SERVER['REMOTE_ADDR']);
                $this->auto_render = FALSE;
                $this->response->headers('Content-Type','application/json');
                $servers = Doctrine::em()->getRepository('Model_Server')->findBy(array('retired' => 0), array('name' => 'ASC'));
                $hosts = array();
                foreach($servers as $server)
                {
                        $parent = trim($server->parent);
                        $parent = (empty($parent) ? null : $parent);
                        $attrs = array(
                                'type' => $server->state.$server->purpose,
				'use_var' => true,
                                'parent' => $parent,
                                'internal_ipv4' => null,
                                'internal_ipv6' => null,
                                'external_ipv4' => null,
                                'external_ipv6' => null,
				'contacts' => null,
                        );

                        foreach ($server->interfaces as $interface)
                        {
                                if (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.internal')) && !$interface->subordinate)
                                {
                                        $attrs['internal_ipv4'] = (strlen(trim($interface->IPv4Addr)) > 0 ? $interface->IPv4Addr : null);
                                        $attrs['internal_ipv6'] = (strlen(trim($interface->IPv6Addr)) > 0 ? $interface->IPv6Addr : null);
                                }
                                elseif (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.external')) && !$interface->subordinate)
                                {
                                        $attrs['external_ipv4'] = (strlen(trim($interface->IPv4Addr)) > 0 ? $interface->IPv4Addr : null);
                                        $attrs['external_ipv6'] = (strlen(trim($interface->IPv6Addr)) > 0 ? $interface->IPv6Addr : null);
                                }
                        }
			$contacts = $server->contacts;
			if (sizeof($contacts) > 0)
			{
				$contactlist = array();
				foreach ($contacts as $c => $contact)
				{
					$contactlist[] = $contact->email;
				}		
				if (sizeof($contactlist) > 0)
				{
					$attrs['contacts'] = implode(",", $contactlist);
				}		
			}
                        $hosts[$server->name] = $attrs;
                }
		$other_hosts = Doctrine::em()->getRepository('Model_OtherHost')->findBy(array('retired' => 0), array('name' => 'ASC'));
		foreach($other_hosts as $other_host)
                {
			$attrs = array(
				'type' => $other_host->type,
				'use_var' => false,
                                'parent' => (strlen($other_host->parent) ? $other_host->parent : null),
                                'internal_ipv4' => null,
                                'internal_ipv6' => null,
                                'external_ipv4' => null,
                                'external_ipv6' => null,
				'hostname' => (strlen($other_host->hostname) ? $other_host->hostname : null),
				'alias' => (strlen($other_host->alias) ? $other_host->alias : null),
				'check_command' => (strlen($other_host->checkCommand) ? $other_host->checkCommand : null),
				'contacts' => null,
			);
			$prefix = ($other_host->internal ? 'internal' : 'external');
			$attrs["{$prefix}_ipv4"] = (strlen($other_host->IPv4Addr) ? $other_host->IPv4Addr : null);
			$attrs["{$prefix}_ipv6"] = (strlen($other_host->IPv6Addr) ? $other_host->IPv6Addr : null);
			$contacts = $other_host->contacts;
                        if (sizeof($contacts) > 0)
                        {
				$contactlist = array();
                                foreach ($contacts as $c => $contact)
                                {
                                        $contactlist[] = $contact->email;
                                }
                                if (sizeof($contactlist) > 0)
                                {
                                        $attrs['contacts'] = implode(",", $contactlist);
                                }
                        }
			$hosts[$other_host->name] = $attrs;
		}
                $this->response->body(SOWN::jsonpp(json_encode($hosts)));
        }

}
