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
			$os = trim($server->os);
                        $os = (empty($os) ? null : $os);
			$services = array();
			// Cannot use $server->Services because these are not updated quick enough to be displayed on page reload.
                        $hostServices = Doctrine::em()->getRepository('Model_HostService')->findByServer($server);
			foreach ($hostServices as $hostService)
			{
				$services[] = $hostService->service->name;
			}
                        $attrs = array(
                                'type' => $server->state.$server->purpose,
				'use_var' => true,
                                'parent' => $parent,
				'os' => $os,
                                'internal_ipv4' => null,
                                'internal_ipv6' => null,
                                'external_ipv4' => null,
                                'external_ipv6' => null,
				'uplink_ipv4' => null,
                                'uplink_ipv6' => null,
                                'ipmi_ipv4' => null,
				'services' => $services,
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
				elseif (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.uplink')) && !$interface->subordinate)
				{
					$attrs['uplink_ipv4'] = (strlen(trim($interface->IPv4Addr)) > 0 ? $interface->IPv4Addr : null);
                                        $attrs['uplink_ipv6'] = (strlen(trim($interface->IPv6Addr)) > 0 ? $interface->IPv6Addr : null);
				}
				elseif (in_array($interface->vlan->name, Kohana::$config->load('system.default.vlan.ipmi')) && !$interface->subordinate)
				{
					$attrs['ipmi_ipv4'] = (strlen(trim($interface->IPv4Addr)) > 0 ? $interface->IPv4Addr : null);
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
			$services = array();
			// Cannot use $server->Services because these are not updated quick enough to be displayed on page reload.
                        $hostServices = Doctrine::em()->getRepository('Model_HostService')->findByOtherHost($other_host);
			foreach ($other_host->services as $hostService)
                        {
                                $services[] = $hostService->service->name;
                        }
			$attrs = array(
				'type' => $other_host->type,
				'use_var' => false,
                                'parent' => (strlen($other_host->parent) ? $other_host->parent : null),
				'os' => 'unknown',
                                'internal_ipv4' => null,
                                'internal_ipv6' => null,
                                'external_ipv4' => null,
                                'external_ipv6' => null,
				'uplink_ipv4' => null,
                                'uplink_ipv6' => null,
                                'ipmi_ipv4' => null,
				'hostname' => (strlen($other_host->hostname) ? $other_host->hostname : null),
				'alias' => (strlen($other_host->alias) ? $other_host->alias : null),
				'check_command' => (strlen($other_host->checkCommand) ? $other_host->checkCommand : null),
				'services' => $services,
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
