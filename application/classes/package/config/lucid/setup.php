<?php defined('SYSPATH') or die('No direct script access.');

class Package_Config_Lucid_Setup extends Package_Config
{
	const package_name = "sown_openwrt_setup";

	public static $supported = array(
		'config_setup' => array(
			array(
				'>=' => '0.1.78',
				'method' => 'config_setup_v0_1_78'
			),
		),
	);

	public static function config_icinga_v0_1_78(Model_Node $node = null)
	{
                static::send_tgz(array(
                        'client.crt'      => array(
                                'content' => $node->certificate->publicKey,
                                'mtime'   => $node->certificate->lastModified->getTimestamp(),
                        ),
                        'client.key'      => array(
                                'content' => $node->certificate->privateKey,
                                'mtime'   => $node->certificate->lastModified->getTimestamp(),
                        ),
                ));	
	}
}
