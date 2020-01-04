<?php defined('SYSPATH') or die('No direct script access.');

abstract class Package_Config
{
	public static function send_shell_script($script)
	{
		static::send_file($script, 'script.sh', 'text/x-sh');
	}

	public static function send_file($data, $filename, $mimetype)
	{
		$r = Request::$current;
		$r->response()->body($data);
		$options = array('inline' => $filename, 'mime_type' => $mimetype);
		
		// This function never returns.
		$r->response()->send_file(TRUE, TRUE, $options);
	}
	
	public static function send_uci_config($package, $config, $last_mod = NULL)
	{
		$last_mod = static::get_last_modified($last_mod);

		$req = Request::$current;
		$r = $req->response();
		$r->headers('Last-Modified', gmdate("D, d M Y H:i:s T", $last_mod));

		$hash = UCIUtils::set_hash($package, $config);


		# TODO: run send_nsca to tell nagios node  $_SERVER['SSL_CLIENT_S_DN_CN'] requested updates for $package
		/* Don't send nagios updates for requests from localhost,
			we only want to do this for the _actual_node_  */
		if($_SERVER['REMOTE_ADDR'] != "127.0.0.1")
		{
			if($package == "crontabs")
			{
				SOWN::send_nsca($_SERVER['SSL_CLIENT_S_DN_CN'], 
					"CONFIG-CRONTABS", 0, 
					"Update Requested");
			}
			if($package == "wireless")
			{
				SOWN::send_nsca($_SERVER['SSL_CLIENT_S_DN_CN'], 
					"CONFIG-WIRELESS", 0, 
					"Update Requested");
			}
		}
		else
		{
                        SOWN::send_irc_message('!h debug: ignoring send_nsca request from '.$_SERVER['REMOTE_ADDR']);
		}
	
		// Sets the Etag. If the client request etag matches, sends the 304 and exits
		$r->check_cache('"'.$hash.'"', $req);
		// check if the client sent an 'if-modified-since' header
		$sa = strptime($req->headers('If-Modified-Since'), '%a, %e %b %Y %H:%M:%S');

		$since = gmmktime($sa['tm_hour'], $sa['tm_min'], $sa['tm_sec'], $sa['tm_mon']+1, $sa['tm_mday'], $sa['tm_year']+1900);

		if($since !== FALSE)
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
	
	public static function send_tgz($files, $mod = NULL)
	{
		$last_mod = static::get_last_modified($mod);
		
		if ($since = strtotime(Request::$current->headers('if-modified-since')))
		{
			if ($since >= $last_mod)
			{
				// No need to send data
				$r->status(304);
				// This request is finished
				return;
			}
		}

		# morse 2013/12/15 added extra rand, just using isn't enough
		# as nodes have ntp-sync'd clocks, and all call for updates
		# in the same second or two.
		$dirname = sys_get_temp_dir() .'/sown_tgz_'. time().rand();
		if(! mkdir($dirname)){
			throw new Exception("Failed to create dir '$dirname'");
		}
		$list = array();
		
		// Fixing a bug in the tar library
		if (sizeof($files) == 0)
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
				if (!copy($src, $temp_dest)){
					throw new Exception("Failed to link file $src to $temp_dest.");
				}
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

		# CM: EVIL HACK to make files owned by root....
		exec("sudo /srv/www/static_files/chown-as-root.sh " . $dirname);
		
		require_once('Archive/Tar.php');
		$archive = new Archive_Tar($file, 'gz');
		$archive->createModify($list, '', $dirname);
		# This work better than an `rm -rf $dirname`
		foreach(glob("{$dirname}/*") as $filename)
		{
			unlink($filename);
		}
		rmdir($dirname);

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
		$node = Model_Node::getByHostname(static::get_cert_cn());
		if($node === null) SOWN::send_irc_message('Node config: failed to find node by certificate.');
		return $node;
	}

	public static function get_server(Request $request)
	{
		$cert = static::get_client_cert();

		if ($cert === NULL)
		{
                        SOWN::send_irc_message('Server config: client '.Request::$client_ip.' with CN '.$_SERVER['SSL_CLIENT_S_DN_CN'].' did not send a certificate in a request.');
			return null;
		}
		
		openssl_x509_export($cert, $dump);

		// Get node object
		$server = Model_Server::getByHostname(static::get_cert_cn());
		if($server === null) SOWN::send_irc_message('Server config: failed to find server by certificate.');
		return $server;
	}

	public static function get_server_node($node_id)
        {
		if ($node_id === NULL)
		{
			return NULL;
		}
		$node = Doctrine::em()->getRepository('Model_Node')->find($node_id);
		if($node === NULL) SOWN::send_irc_message('Server config: failed to find node by id.');
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
		$domain = Kohana::$config->load('system.default.domain');
                $hostname = str_replace(".$domain", "", $_SERVER['SSL_CLIENT_S_DN_CN']);
		return $hostname;
	}
	
	public static function is_bootstrap_cert($cert)
	{
		return static::get_cert_cn() == 'client_bootstrap'; //&& static::is_cert_valid($cert);
	}
	
	public static function makeHeader($commentchar, Model_Node $node, $version)
	{
		return "$commentchar\n$commentchar Config created for ".$node->getHostname().", running version $version of ". static::package_name .".\n\n";
	}

	private static function get_last_modified($mod)
	{
		if ($mod === NULL)
		{
			$last_mod = time();
		}
		else if (is_array($mod))
		{
			$last_mod = 0;
			foreach($mod as $m)
			{
				if(is_string($m))
					$last_mod = max($last_mod, filemtime($m));
				else
					$last_mod = max($last_mod, $m->lastModified->getTimestamp());
			}
		}
		else
		{
			$last_mod = $mod;
		}
		foreach(Model_Entity::get_entities() as $m)
		{
			$last_mod = max($last_mod, $m->lastModified->getTimestamp());
		}
		return $last_mod;
	}
}
