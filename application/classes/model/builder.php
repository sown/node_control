<?php
class Model_Builder
{
	public static function create_node($boxNumber = '', $vpnServerName = 'sown-auth2.ecs.soton.ac.uk', $wiredMac = '', $wirelessMac = '', $firmwareImage = '', $notes = '')
	{
		$certificate = Model_Certificate::build();
		$vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->findOneByName($vpnServerName);

		$port = $vpnServer->getFreePort();
		$protocol = 'udp';
		$ipv4 = $vpnServer->getFreeIPv4Addr(30);
		$ipv6 = $vpnServer->getFreeIPv6Addr(126);
		$vpnEndpoint = Model_VpnEndpoint::build($port, $protocol, $ipv4, $ipv6, $vpnServer);

		$boxNumber = Model_Node::getNextBoxNumber();
		$node = Model_Node::build($boxNumber, $firmwareImage, $notes, $certificate, $vpnEndpoint);

		$wirelessChannel = 0;
		$type = '100M';
		$networkAdapter = Model_NetworkAdapter::build($wiredMac, $wirelessChannel, $type, $node);

		$ipv4 = null;
		$ipv6 = null;
		$name = 'eth0';
		$ssid = '';
		$type = 'dhcp';
		$offerDhcp = '';
		$is1x = '';
		$node->interfaces->add(Model_Interface::build($ipv4, $ipv6, $name, $ssid, $type, $offerDhcp, $is1x, $networkAdapter, $node));

		$wirelessChannel = 1;
		$type = 'g';
		$networkAdapter = Model_NetworkAdapter::build($wirelessMac, $wirelessChannel, $type, $node);

		$ipv4 = IP_Network_Address::factory($vpnServer->getFreeIPv4Addr(24)->get_address_in_network(-1), 24);
		//$ipv6 = IP_Network_Address::factory($vpnServer->getFreeIPv6Addr(64)->get_address_in_network(-1), 64);
		$ipv6 = $vpnServer->getFreeIPv6Addr(64);
		$name = 'wlan0';
		$ssid = 'eduroam';
		$type = 'static';
		$offerDhcp = 1;
		$is1x = 1;
		$node->interfaces->add(Model_Interface::build($ipv4, $ipv6, $name, $ssid, $type, $offerDhcp, $is1x, $networkAdapter, $node));

		$node->save();
		
		return $node;
	}

	public static function destroy_node($boxNumber)
	{
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($boxNumber);
		if (!empty($node)) 
		{
			$node->delete();
			return TRUE;
		}
		return FALSE;
	}

	public static function create_deployment($nodeId, $name, $longitude, $latitude, $cap, $userId)
	{
		$deployment = Model_Deployment::build($name, $latitude, $longitude, $cap);
		$deployment->save();
		$nodeDeployment = Model_NodeDeployment::build($nodeId, $deployment->id);
		$nodeDeployment->save();
		$deploymentAdmin = Model_DeploymentAdmin::build($deployment->id, $userId);
		$deploymentAdmin->save();
		return Doctrine::em()->getRepository('Model_Deployment')->find($deployment->id);
	}

	public static function destroy_deployment($id)
	{
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($id);
		if (!empty($deployment))
		{
			$deployment->delete();
			return TRUE;
		}
		return FALSE;
	}

	public static function end_deployment($id)
        {
		$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($id);
		if (empty($deployment))
		{
			return FALSE;
		}
		foreach ($deployment->admins as $admin)
		{
			if ($admin->endDate->getTimestamp() > time())
			{
				$admin->endDate = new \DateTime();
				$admin->save();
			}
		}
		foreach ($deployment->node_deployments as $nodeDeployment)
		{
			if ($nodeDeployment->endDate->getTimestamp() > time())
                        {
                                $nodeDeployment->endDate = new \DateTime();;
				$nodeDeployment->save();
                        }
		}
		if ($deployment->endDate->getTimestamp() > time())
		{
			$deployment->endDate = new \DateTime();
			$deployment->save();
			return TRUE;
		}
		return FALSE;
	}
}
