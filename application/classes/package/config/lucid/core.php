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
		'config_named_fwd' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_named_fwd_v0_1_78'
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


	/* stolen from http://www.web-max.ca/PHP/misc_6.php */
	private static function DECtoDMS($dec)
	{

		// Converts decimal longitude / latitude to DMS
		// ( Degrees / minutes / seconds ) 
	
		// This is the piece of code which may appear to 
		// be inefficient, but to avoid issues with floating
		// point math we extract the integer part and the float
		// part by using a string function.
	
		$vars = explode(".",$dec);
		$deg = $vars[0];
		$tempma = "0.".$vars[1];

		$tempma = $tempma * 3600;
		$min = floor($tempma / 60);
		$sec = round($tempma - ($min*60), 3);
	
		return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
	}   


	public static function config_named_fwd_v0_1_78(Model_Node $node = null)
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
			static::send_file($data, 'fragment.sown.org.uk-node_control', 'text/plain');
		}

		/* Return a fragment of a zone file, eg:
node267 IN      A       10.13.213.254
node267 IN      LOC     50 55 9.000 N 1 20 34.000 W 10m 100m 60m 60m
node267 IN      TXT     "mac:00:18:0A:01:3D:5E type:home-TUNNELED"
node267 IN      HINFO   "MIPS AR2315" "OpenWRT 7.09 (Kamikaze)"
node267 IN      AAAA    2001:630:d0:f7d4::1
		*/
		$data = "";
		foreach($node->interfaces as $iface)
		{
			$deployment = $node->currentDeployment;

			if($iface->IPv4Addr != FALSE)
			{
				$data .= "$node->hostname\tIN\tA\t$iface->IPv4Addr\n";
			}
			if($iface->IPv6Addr != FALSE)
			{
				$data .= "$node->hostname\tIN\tAAAA\t$iface->IPv6Addr\n";
			}

			if($deployment != FALSE)
			{
				$lat = static::DECtoDMS($deployment->latitude);
				$long = static::DECtoDMS($deployment->longitude);
				if($long['deg'] < 0)
				{
					$long['deg'] = -$long['deg'];
					$long['dir'] = "W";
				}
				else
				{
					$long['dir'] = "E";
				}
				if($lat['deg'] < 0)
				{
					$lat['deg'] = -$long['deg'];
					$lat['dir'] = "S";
				}
				else
				{
					$lat['dir'] = "N";
				}
				
				$data .= "$node->hostname\tIN\tLOC\t".$lat['deg']." ".$lat['min']." ".$lat['sec']." ".$lat['dir']." ".$long['deg']." ".$long['min']." ".$long['sec']." ".$long['dir']."\n";
			}
		}

		return $data;
	}


	public static function config_named_rev_v0_1_78(Model_Node $node = null)
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
			static::send_file($data, 'fragment.10.13-node_control', 'text/plain');
		}

		/* Return a fragment of a zone file, eg:
		*/
		$data = "";
		foreach($node->interfaces as $iface)
		{
			$deployment = $node->currentDeployment;

			if($iface->IPv4Addr != FALSE)
			{
				$data .= "$node->hostname\tIN\tA\t$iface->IPv4Addr\n";
			}
			if($iface->IPv6Addr != FALSE)
			{
				$data .= "$node->hostname\tIN\tAAAA\t$iface->IPv6Addr\n";
			}
		}

		return $data;
	}
}
}
