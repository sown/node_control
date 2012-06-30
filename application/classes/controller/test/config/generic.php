<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test_Config_Generic extends Controller
{
	public function check_login()
	{
		if (!Auth::instance()->logged_in())
		{
			$this->request->redirect(Route::url('package_login').URL::query(array('url' => $this->request->url())));
		}
	}

	public function action_default()
	{
		$this->check_login();
		$os           = $this->request->param('os');
		$package      = $this->request->param('package');
		$version      = $this->request->param('version');
		$request_name = $this->request->param('request_name');
		$hostname     = $this->request->param('hostname');

		$keyfiles = $this->storeKeys($hostname, $os);
		$this->curl($os, $package, $version, $request_name, $keyfiles);
		$this->removeKeys($keyfiles);
	}

	public function action_home()
	{
		$this->check_login();
		$repository = Doctrine::em()->getRepository('Model_Node');
		foreach($repository->findAll() as $node)
		{
			echo "<hr/>";
			echo $node->toString()."<br/>";
		}

		$repository = Doctrine::em()->getRepository('Model_Server');
		foreach($repository->findAll() as $server)
		{
			echo "<hr/>";
			echo $server->toString()."<br/>";
		}

		$repository = Doctrine::em()->getRepository('Model_User');
		foreach($repository->findAll() as $user)
		{
			echo "<hr/>";
			echo $user->toString()."<br/>";
		}
	}

	private function storeKeys($hostname, $os)
	{
		switch($os)
		{
			case "backfire":
				$entity = Model_Node::getByHostname($hostname);
				break;
			case "lucid":
				$entity = Model_Server::getByName($hostname);
				break;
		}
		$cert = $entity->certificate;
		$fpub = tempnam('/tmp/', 'pub_key_');
		$fh = fopen($fpub, 'w+');
		fputs($fh, $cert->publicKey);
		fclose($fh);
		$fpriv = tempnam('/tmp/', 'priv_key_');
		$fh = fopen($fpriv, 'w+');
		fputs($fh, $cert->privateKey);
		fclose($fh);
		return array('public' => $fpub, 'private' => $fpriv);
	}

	private function removeKeys($keyfiles)
	{
		foreach(array_values($keyfiles) as $file)
		{
			unlink($file);
		}
	}

	private function curl($os, $package, $version, $request_name, $keyfiles)
	{
		if ('backfire' == $os || 'lucid' == $os)
			$route = 'package_config_'.$os;
		else
			throw new Exception("Unsupported OS type");
		
		$url = 'https://localhost'.Route::url($route, array(
				'package' => $package,
				'version' => $version,
				'request_name' => $request_name,
			)/*, TRUE <- This gets the hostname/port from the request, but it doesn't work when port forwarding */);

		$mac = "00:11:5b:e4:7e:cb";

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, array("mac", $mac));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($ch, CURLOPT_SSLCERT, $keyfiles['public']);
		curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '');
		curl_setopt($ch, CURLOPT_SSLKEY, $keyfiles['private']);
		curl_setopt($ch, CURLOPT_SSLKEYPASSWD, '');

		$ret = curl_exec($ch);
		if(!$ret)
		{
			echo "<h1>CURL Error</h1>";
			echo $url."</br>";
			echo curl_error($ch);
		}
		else
		{
			$info = curl_getinfo($ch);
			echo "Type: ".$info['content_type']."<br />";

			if($info['content_type'] == 'application/x-gtar')
			{
				require_once('Archive/Tar.php');
				$f = tempnam('/tmp/', 'test_');
				$fh = fopen($f, 'w+');
				fputs($fh, $ret);
				fclose($fh);
				$tar = new Archive_Tar($f);
				foreach($tar->listContent() as $file)
				{
					echo "<h1>".$file['filename']."</h1>";
					$content = $tar->extractInString($file['filename']);
					if($content != "")
					{
						echo "<em>File with ".count(explode("\n", $content))." lines</em>";
						echo "<pre>$content</pre>";
					}
					else
					{
						echo "<em>Empty file</em>";
					}
				}
				unlink($f);
			}
			else
			{
				echo "<em>File with ".count(explode("\n", $ret))." lines</em>";
				echo "<pre>$ret</pre>";
			}
		}
	}
}
