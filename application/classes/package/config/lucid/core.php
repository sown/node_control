<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Lucid_Core extends Package_Config
{
	const package_name = "sown_openwrt_core";

	public static $supported = array(
		'config_radiusd' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_radiusd_v0_1_78'
			),
		),
	);

	public static function config_radiusd_v0_1_78(Model_Node $node = null)
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
			static::send_file($data, 'nodes.conf', 'text/plain');
		}
		return "
client {$node->vpnEndpoint->IPv4->get_address_in_network(2)} {
	secret		= '{$node->radiusSecret}'
	shortname	= {$node->FQDN}
}
";
	}
}
