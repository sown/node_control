<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Lucid_Monitoring extends Package_Config
{
	const package_name = "sown_openwrt_monitoring";

	public static $supported = array(
		'config_nfsen' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_nfsen_v0_1_78'
			),
		),
		'config_icinga' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_icinga_v0_1_78'
			),
		),
	);

	public static function config_nfsen_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$repository = Doctrine::em()->getRepository('Model_Node');
			$data = "%sources = (\n";
			foreach($repository->findByUndeployable(0) as $node)
			{
				$fn =  __function__;
				$data .= static::$fn($node);
			}
			$data .= ")\n";
			static::send_file($data, 'nfsen-nodes.perl', 'text/perl');
		}
		$vpnEndpoint = $node->vpnEndpoint;
		if (!empty($vpnEndpoint))
		{
			return "'".$node->nfsenName."' => { 'port' => '".$node->vpnEndpoint->port."', 'col' => '#".substr(md5($node->name), 0, 6)."', 'type' => 'netflow' },\n";
		}
		return "";
	}

	public static function config_icinga_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$repository = Doctrine::em()->getRepository('Model_Node');
			$data = "";
			foreach($repository->findAll() as $node)
			{
				$fn =  __function__;
				$data .= static::$fn($node);
			}
			static::send_file($data, 'icinga-nodes.cfg', 'text/plain');
		}
		if($node->currentDeployment == null)
		{
			return "";
		}
		$emails = array();
		foreach($node->currentDeployment->currentAdmins as $admin)
		{
			$emails[] = $admin->user->email;
		}
		$email = implode(',', $emails);
		$name = $node->hostname;
		$alias = $node->name . " (#" . $node->boxNumber .")";
		$url = "";
		$latitude = $node->currentDeployment->latitude;
		$longitude = $node->currentDeployment->longitude;
		$range = $node->currentDeployment->range;
		$box_number = $node->boxNumber;
		$node_id = $node->id;
		$parents = null;
		$address = null;
		if($node->vpnEndpoint != null)
		{
			#TODO: Fix me so we use loopbacks
			$parents = $node->vpnEndpoint->vpnServer->server->name;
			# Handle multiple interfaces
			$ipv4_addrs = array();

			if(substr($node->vpnEndpoint->IPv4Addr, 0, 8) != "169.254."){
				$ipv4_addrs[] = "tap0=".$node->vpnEndpoint->IPv4->get_address_in_network(2);
			}
			foreach($node->interfaces as $i)
			{
				if( ($i->IPv4 != null) && (!$i->offerDhcp) )
				{
					$ipv4_addrs[] = $i->name."=".$i->IPv4->get_address();
				}
			}
			$address = implode(',', $ipv4_addrs);
		}
		else
		{
			foreach($node->interfaces as $i)
			{
				if($i->IPv4 != null && strpos($i->name, "wlan") === FALSE)
				{
					$ipv4_addrs[] = $i->name."=".$i->IPv4->get_address();
				}
			}
			$address = implode(',', $ipv4_addrs);
		}
		$radio_details = array();
		foreach($node->interfaces as $interface)
		{
			if($interface->networkAdapter->wirelessChannel != 0)
			{
				$radio_details[] = array('ssid' => $interface->ssid, 'protocol' => Kohana::$config->load('system.default.adapter_types.'.$interface->networkAdapter->type), 'enc' => $interface->encryption);
			}
		}
		$radio_details = json_encode($radio_details);

		$o['alias'] = $alias;
		$o['2d_coords'] = $latitude.",".$longitude;
		$o['3d_coords'] = $latitude.",".$longitude.",".$range;
		$o['_BOXNUMBER'] = $box_number;
		$o['_NODEID'] = $node_id;
		$o['contacts'] = "+".$name."_admin";
	
		$use = "node";
		
		$hostgroups = "*Nodes";
		$node_dep_type = $node->currentDeployment->type;
		switch ($node_dep_type)
		{
			case 'home':
				$hostgroups.=",*Home Nodes";
				break;
			case 'campus':
			case 'native':
				$hostgroups.=",*Native Nodes";
				break;
		}
		
		$extbld_node = $node->externalBuild;
		$is_dev_node = $node->currentDeployment->isDevelopment;
                if (!empty($extbld_node))
                {
                        $hostgroups .= ',*External Build Nodes';
			if (!empty($is_dev_node))
			{
				$use = 'devnode';
				$notification_lines = "host_notification_commands\thost-notify-by-irc-dev\n\tservice_notification_commands\tnotify-by-irc-dev";
			}
			else 
			{
				$use = 'prodnode';
                        	$notification_lines = "host_notification_commands\tnodeadmin-notify-by-email\n\tservice_notification_commands\tnodeadmin-service-notify-by-email\n\temail\t\t\t\t\t{$email}";
			}
                }
		elseif (!empty($is_dev_node))
		{
			$use = 'devnode';
			$hostgroups .= ',*Development Nodes';
			$notification_lines = "host_notification_commands\thost-notify-by-irc-dev\n\tservice_notification_commands\tnotify-by-irc-dev";
		}
		else
		{
			$use = 'prodnode';
			$hostgroups .= ',*Production Nodes';
			$notification_lines = "host_notification_commands\tnodeadmin-notify-by-email\n\tservice_notification_commands\tnodeadmin-service-notify-by-email\n\temail\t\t\t\t\t{$email}";
		}
		$firmware_versions = Kohana::$config->load('system.default.firmware_versions');
		if (!empty($firmware_version)) 
		{
			$hostgroups .= ',*'.$firmware_versions[$node->firmwareVersion].' Nodes';
		}

		$config = "
define contact {
	contact_name			{$name}_admin
	host_notification_period	24x7
	service_notification_period	24x7
	host_notification_options	d,r
	service_notification_options	w,c,r
	{$notification_lines}
}

define host {
	host_name	{$name}
	use		{$use}
	hostgroups	{$hostgroups}
	address		{$address}
	parents		{$parents}
	alias		{$alias}
	2d_coords	{$latitude},{$longitude}
	3d_coords	{$latitude},{$longitude},{$range}
	_BOXNUMBER	{$box_number}
	_NODEID		{$node_id}
	_RADIODETAILS	{$radio_details}
	contacts	+{$name}_admin
}

";
		if ($node_dep_type == "home")
		{
			$config .= "
define service {
	host_name	{$parents}
	use		vpnserver
	service_description	VPNSERVER-{$name}
	check_command	check_via_node_control!node!{$name}!OpenvpnRunning
}

define service {
	host_name	{$parents}
	use		nodecert
	service_description	NODECERT-{$name}
	check_command	check_via_node_control!node!{$name}!CertExpiry
}

";
		}
		return $config;
	}
}
