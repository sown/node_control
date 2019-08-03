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

	public static function config_setup_v0_1_78(Model_Node $node = null, $nonce = null)
	{
		$nodeSetupRequest = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findOneByNonce($nonce); 
		$crypted_password = password_hash($nodeSetupRequest->password, PASSWORD_BCRYPT);
		$openvpn = call_user_func("Package_Config_".ucfirst($node->firmwareVersion)."_Tunnel::config_openvpn_v0_1_78_raw", $node);
                static::send_tgz(array(
                        'client.crt'      => array(
                                'content' => $node->certificate->publicKey,
                                'mtime'   => $node->certificate->lastModified->getTimestamp(),
                        ),
                        'client.key'      => array(
                                'content' => $node->certificate->privateKey,
                                'mtime'   => $node->certificate->lastModified->getTimestamp(),
                        ),
			'openvpn'         => array(
				'content' => UCIUtils::render_UCI_config("openvpn", $openvpn),
				'mtime'	  => $node->vpnEndpoint->lastModified->getTimestamp(),
			),
			'hosts'           => array(
				'content' => "127.0.0.1 localhost

10.5.0.239 auth2.sown.org.uk

::1     localhost ip6-localhost ip6-loopback
ff02::1 ip6-allnodes
ff02::2 ip6-allrouters",
				'mtime'   => time(),
                        ),
			'shadow'	  => array(
				'content' => 'root:'.$crypted_password.':16775:0:99999:7:::
daemon:*:0:0:99999:7:::
ftp:*:0:0:99999:7:::
network:*:0:0:99999:7:::
nobody:*:0:0:99999:7:::',
				'mtime'	  => time(),
			),
                ));	
	}
}
