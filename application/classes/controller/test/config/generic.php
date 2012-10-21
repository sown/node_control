<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test_Config_Generic extends Controller_AbstractAdmin
{
	public function action_default()
	{
		$this->check_login('systemadmin');
		$os           = $this->request->param('os');
		$type         = $this->request->param('type');
		$package      = $this->request->param('package');
		$version      = $this->request->param('version');
		$request_name = $this->request->param('request_name');
		$hostname     = $this->request->param('hostname');

		$keyfiles = $this->storeKeys($hostname, $os);
		$this->curl($type, $os, $package, $version, $request_name, $keyfiles, $hostname);
		$this->removeKeys($keyfiles);
	}

	public function action_info()
	{
		$mydevice = Model_Device::getFromDeviceIP($_SERVER['REMOTE_ADDR']);
		$content = "";
		$content .= "<div>";
		if(!is_null($mydevice))
		{
			$content .= "Your device is connected to SOWN.<br/>";
			$content .= "MAC address: ".$mydevice->mac."<br/>";
			if(!is_null($mydevice->user))
			{
				$content .= "This device is associated with a user.<br/>";
			}
		}
		else
		{
			$content .= "Your device is <em>not</em> connected to SOWN.<br/>";
		}
		$content .= "</div>";

		$content .= "<hr style='display:block' />";

		$content .= "<div>";
		$mynode = Model_Node::getFromDeviceIP($_SERVER['REMOTE_ADDR']);
		if(!is_null($mynode))
		{
			$content .= "You are connected to node: ".$mynode->currentDeployment->name."<br/>";
		}
		$content .= "</div>";

		$content .= "<hr style='display:block' />";

		$content .= "<div>";
		if(!is_null($mydevice))
		{
			if(!is_null($mynode) && in_array($mydevice, $mynode->currentDeployment->privilegedDevices))
			{
				$content .= "Your device enjoys special privileges when connected to this node.<br/>";
			}
			else
			{
				$content .= "Your device <em>does not</em> enjoy special privileges when connected to this node.<br/>";
			}
		}
		$content .= "</div>";

		$content .= "<hr style='display:block' />";

		$content .= "<div>";
		if(!is_null($mydevice))
		{
			$content .= "Your device's data consumption over the past 24 hours:<br/>";
			$content .= "<img src='http://sown-auth2.ecs.soton.ac.uk/radacct-tg/graphs.php?col=sta-rrds&entry=".str_replace(':', '-', strtoupper($mydevice->mac))."' />";
		}
		$content .= "</div>";

		$content .= "<hr style='display:block' />";

		$content .= "<div>";
		if(!is_null($mydevice) && !is_null($mynode) && in_array($mydevice, $mynode->currentDeployment->privilegedDevices))
		{
			$content .= "This node's data consumption over the past 24 hours:<br/>";
			$content .= "<img src='http://sown-auth2.ecs.soton.ac.uk/radacct-tg/graphs.php?col=nas-rrds&entry=node_deployment".$mynode->currentDeployment->id."' />";
		}
		$content .= "</div>";

                $this->template->title = "Connection Information";
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->content = $content;
	}

	public function action_home()
	{
		$this->check_login('systemadmin');
		$content="<style>
.test .ID {
	font-style: italic;
	color: white;
	background-color: navy;
}

.test th {
	text-align: left;
	vertical-align: top;
	width: 150px;
}

.test div {
	border: solid 1px black;
}

.test td, .test th {
	padding: 2px;
}

.test table {
	border-collapse: collapse;
	width: 100%;
}

.test .empty {
	display: none;
	color: gray;
}
</style>";
		$content .= "<div class=\"test\">";
		$repository = Doctrine::em()->getRepository('Model_Node');
		foreach($repository->findAll() as $node)
		{
			$content .= "<hr/>\n" . $node->toHTML()."<br/>";
		}

		$repository = Doctrine::em()->getRepository('Model_Server');
		foreach($repository->findAll() as $server)
		{
			$content .= "<hr/>\n" . $server->toHTML()."<br/>";
		}

		$repository = Doctrine::em()->getRepository('Model_User');
		foreach($repository->findAll() as $user)
		{
			$content .= "<hr/>\n" . $user->toHTML()."<br/>";
		}
		$content .= "</div>";

                $this->template->title = "Test";
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->content = $content;
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

	private function curl($type, $os, $package, $version, $request_name, $keyfiles, $hostname)
	{
		if ('config' == $type)
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
		}
		elseif ('status' == $type)
		{
			$route = 'package_status';
			$url = 'https://localhost'.Route::url($route, array(
					'request_name' => $request_name,
					'hostname' => $hostname,
					'os' => $os,
			));
		}
		else
		{
			throw new Exception("Unsupported type");
		}
		

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
			print_r(curl_getinfo($ch));
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
