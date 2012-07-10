<?php
class Model_Builder
{
	public static function create_node($vpnServerName = 'sown-vpn.ecs.soton.ac.uk')
	{
		$certificate = Model_Certificate::build();

		$vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->findOneByName($vpnServerName);

		$port = $vpnServer->getFreePort();
		$protocol = 'udp';
		$ipv4 = $vpnServer->getFreeIPv4Addr(30);
		$ipv6 = $vpnServer->getFreeIPv6Addr(126);
		$vpnEndpoint = Model_VpnEndpoint::build($port, $protocol, $ipv4, $ipv6, $vpnServer);

		$boxNumber = Model_Node::getNextBoxNumber();
		$firmwareImage = '';
		$notes = '';
		$node = Model_Node::build($boxNumber, $firmwareImage, $notes, $certificate, $vpnEndpoint);

		$mac = '';
		$wirelessChannel = 0;
		$type = '100M';
		$networkAdapter = Model_NetworkAdapter::build($mac, $wirelessChannel, $type, $node);

		$ipv4 = null;
		$ipv6 = null;
		$name = 'eth0';
		$ssid = '';
		$type = 'dhcp';
		$offerDhcp = '';
		$is1x = '';
		$node->interfaces->add(Model_Interface::build($ipv4, $ipv6, $name, $ssid, $type, $offerDhcp, $is1x, $networkAdapter, $node));

		$mac = '';
		$wirelessChannel = 1;
		$type = 'g';
		$networkAdapter = Model_NetworkAdapter::build($mac, $wirelessChannel, $type, $node);

		$ipv4 = $vpnServer->getFreeIPv4Addr(24);
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
		$node->delete();
	}
}
