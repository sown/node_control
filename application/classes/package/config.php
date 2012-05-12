<?php defined('SYSPATH') or die('No direct script access.');

abstract class Package_Config
{
	public static function send_shell_script($script)
	{
		$r = Request::$current;
		$r->response()->body($script);
		$options = array('inline' => 'script.sh', 'mime_type' => 'text/x-sh');
		
		// This function never returns.
		$r->response()->send_file(TRUE, TRUE, $options);
	}
	
	public static function send_uci_config($package, $config, $last_mod = NULL)
	{
		if ($last_mod === NULL)
			$last_mod = time();

		$req = Request::$current;
		$r = $req->response();
		$r->headers('Last-Modified', gmdate("D, d M Y H:i:s T", $last_mod));

		$hash = UCIUtils::set_hash($package, $config);

		// Sets the Etag. If the client request etag matches, sends the 304 and exits
		$r->check_cache('"'.$hash.'"', $req);

		// check if the client sent an 'if-modified-since' header
		if ($since = strtotime($req->headers('if-modified-since')))
		{
			if ($since >= $last_mod)
			{
				// No need to send data
				$r->status(304);
				// This request is finished
				return;
			}
		}
		
		
		$r->body(UCIUtils::render_UCI_config($package, $config));
		
		$options = array(
			'mime_type' => 'application/x-uci'
		);
		$r->send_file(TRUE, $package, $options);
	}
	
	public static function send_tgz($files)
	{
		$dirname = sys_get_temp_dir() .'/sown_tgz_'. time();
		if(! mkdir($dirname))
			throw new Exception("Failed to create dir '$dirname'");

		$list = array();
		
		// Fixing a bug in the tar library
		if (count($files) == 1)
			$files['.ignore'] = array('content' => 'Please ignore this file.');
		
		foreach ($files as $dst => $src)
		{
			$temp_dest = $dirname.'/'.$dst;
			$temp_dest_dir = dirname($temp_dest);

			if (! is_dir($temp_dest_dir))
				mkdir($temp_dest_dir, umask(), TRUE);

			if (is_string($src))
			{
				// It's a filename.
				if (!link($src, $temp_dest))
					throw new Exception("Failed to link file $src to $temp_dest.");
			}
			elseif (is_array($src))
			{
				if (isset($src['content']))
				{
					file_put_contents($temp_dest, $src['content']);
				}
				elseif (isset($src['file']))
				{
					copy($src['file'], $temp_dest);
					touch($temp_dest, filemtime($src['file']));
				}
				else
				{
					throw new Exception("Missing file source for key $dst");
				}
				
				if (isset($src['mtime']))
					touch($temp_dest, $src['mtime']);
			}
			else
			{
				throw new Exception("Unsupported type for key $dst");
			}

			$list[] = $temp_dest;
		}

		$file = tempnam(sys_get_temp_dir(), 'sown_tgz_');
		if ($file === FALSE)
			throw new Exception('Failed to create temporary file :(.');

		require_once('Archive/Tar.php');
		$archive = new Archive_Tar($file, 'gz');
		$archive->createModify($list, '', $dirname);
		
		// PHP doesn't have a nice way of doing this
		`rm -rf $dirname`;
		
		Request::$current->response()->send_file($file, FALSE, array(
			'delete'    => TRUE,
			'mime_type' => 'application/x-gtar'
		));
	}
	
	public static function get_node(Request $request)
	{
		$cert = static::get_client_cert();

		if ($cert === NULL)
		{
                        SOWN::send_irc_message('Node config: client '.Request::$client_ip.' with CN '.$_SERVER['SSL_CLIENT_S_DN_CN'].' did not send a certificate in a request.');
			return null;
		}
		
		if (static::is_bootstrap_cert($cert))
		{
			if ($request->param('request_name') != 'credentials')
			{
				SOWN::send_irc_message('Node config: client '.Request::$client_ip.' is using the bootstrap certificate in a request.');
				return null;
			}

			// Get node object
			$node = Model_Node::getByMac($request->post('mac'));
			if($node === null) SOWN::send_irc_message('Node config: failed to find node with MAC: '.$request->post('mac'));
			return $node;
		}

		openssl_x509_export($cert, $dump);

		// Get node object
		$node = Model_Node::getByCertificate(PKI::PEM_decode($dump));
		if($node === null) SOWN::send_irc_message('Node config: failed to find node by certificate.');
		return $node;
	}
	
	public static function get_client_cert()
	{
		$cert = null;
		if(isset($_SERVER["SSL_CLIENT_CERT"])) $cert = $_SERVER["SSL_CLIENT_CERT"];

		if ($cert === null || empty($cert))
			return null;

		return openssl_x509_read($cert);
	}
	
	public static function is_cert_valid($cert)
	{
		return (openssl_x509_checkpurpose($cert, X509_PURPOSE_SSL_CLIENT, array('/srv/vpn-keys/ca.crt')) === true);
	}
	
	public static function get_cert_cn()
	{
		return $_SERVER['SSL_CLIENT_S_DN_CN'];
		//$data = openssl_x509_parse($cert);
		//return $data['subject']['CN'];
	}
	
	public static function is_bootstrap_cert($cert)
	{
		return static::get_cert_cn() == 'client_bootstrap'; //&& static::is_cert_valid($cert);
	}
	
	public static function makeHeader($commentchar, Model_Node $node, $version)
	{
		return "$commentchar\n$commentchar Config created for ".$node->getHostname().", running version $version of ". static::package_name .".\n\n";
	}
}
