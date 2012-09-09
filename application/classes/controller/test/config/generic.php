<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test_Config_Generic extends Controller
{
	public function check_login($role = NULL)
	{
		if (!Auth::instance()->logged_in($role))
		{
			if (!Auth::instance()->logged_in())
				$this->request->redirect(Route::url('login').URL::query(array('url' => $this->request->url())));
			else
				throw new HTTP_Exception_403('You do not have permission to access this page.');				
		}
	}

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

	public function action_change_password()
	{
		$this->check_login();
		$view = View::Factory("template");
		$view->title = "Change Password";
		$sidebar = View::factory('partial/sidebar');
		$sidebar->username = Auth::instance()->get_user();
		$view->sidebar = $sidebar;

		if(!Auth::instance()->is_local())
		{
			$view->content = "Sorry, but your account password cannot be changed via our system.";
		}
		else
		{
			$content = View::factory('pages/change_password');
			$content->username = Auth::instance()->get_user();
			$content->info = array();
			if($this->request->method() == "POST")
			{
				$oldpassword = $this->request->post('oldpassword');
				$password1 = $this->request->post('password1');
				$password2 = $this->request->post('password2');
				if($password1 != $password2)
				{
					$content->info['error'][] = "New passwords do not match";
				}
				else
				{
					if(!Auth::instance()->change_password($oldpassword, $password1))
					{
						$content->info['error'][] = "Failed to update password";
					}
					else
					{
						$content->info['notice'][] = "Password updated successfully";
					}
				}
			}
			$view->content = $content;
		}
		echo (string) $view->render();
	}

	public function action_info()
	{
		$mydevice = Model_Device::getFromDeviceIP($_SERVER['REMOTE_ADDR']);
		echo "<div>";
		if(!is_null($mydevice))
		{
			echo "Your device is connected to SOWN.<br/>";
			echo "MAC address: ".$mydevice->mac."<br/>";
			if(!is_null($mydevice->user))
			{
				echo "This device is associated with a user.<br/>";
			}
		}
		else
		{
			echo "Your device is <em>not</em> connected to SOWN.<br/>";
		}
		echo "</div>";

		echo "<hr />";

		echo "<div>";
		$mynode = Model_Node::getFromDeviceIP($_SERVER['REMOTE_ADDR']);
		if(!is_null($mynode))
		{
			echo "You are connected to node: ".$mynode->currentDeployment->name."<br/>";
		}
		echo "</div>";

		echo "<hr />";

		echo "<div>";
		if(!is_null($mydevice))
		{
			if(!is_null($mynode) && in_array($mydevice, $mynode->currentDeployment->privilegedDevices))
			{
				echo "Your device enjoys special privileges when connected to this node.<br/>";
			}
			else
			{
				echo "Your device <em>does not</em> enjoy special privileges when connected to this node.<br/>";
			}
		}
		echo "</div>";

		echo "<hr />";

		echo "<div>";
		if(!is_null($mydevice))
		{
			echo "Your data consumption over the past 24 hours:<br/>";
			echo "<img src='http://sown-auth2.ecs.soton.ac.uk/radacct-tg/graphs.php?col=sta-rrds&entry=".str_replace(':', '-', strtoupper($mydevice->mac))."' />";
		}
		echo "</div>";

		echo "<hr />";

		echo "<div>";
		if(!is_null($mydevice) && !is_null($mynode) && in_array($mydevice, $mynode->currentDeployment->privilegedDevices))
		{
			echo "Your node's data consumption over the past 24 hours:<br/>";
			echo "<img src='http://sown-auth2.ecs.soton.ac.uk/radacct-tg/graphs.php?col=nas-rrds&entry=deployment".$mynode->currentDeployment->id."' />";
		}
		echo "</div>";
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

		$view = View::factory('template');
                $view->title = "Test";

                $sidebar = View::factory('partial/sidebar');
                $sidebar->username = Auth::instance()->get_user();
                $view->sidebar = $sidebar;

                $view->content = $content;

                echo (string) $view->render();
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
