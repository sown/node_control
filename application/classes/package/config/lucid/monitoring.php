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
	);

	public static function config_nfsen_v0_1_78(Model_Node $node = null)
	{
		if($node === null)
		{
			$repository = Doctrine::em()->getRepository('Model_Node');
			$data = "%sources = (\n";
			foreach($repository->findAll() as $node)
			{
				$fn =  __function__;
				$data .= static::$fn($node);
			}
			$data .= ")\n";
			static::send_file($data, 'nfsen-nodes.perl', 'text/perl');
		}
		return "'".$node->name."' => { 'port' => '".$node->vpnEndpoint->port."', 'col' => '#".substr(md5($node->name), 0, 6)."', 'type' => 'netflow' }\n";
	}

}
