<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test_Config_Generic extends Controller
{
	public function action_default()
	{
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
		$repository = Doctrine::em()->getRepository('Model_Node');
		foreach($repository->findAll() as $node)
		{
			echo "<hr/>";
			echo $node->toString()."<br/>";
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
		$url = "https://sown-auth2.ecs.soton.ac.uk/package/config/$os/$package/$version/$request_name";

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
			echo "<h1>Error</h1>";
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
