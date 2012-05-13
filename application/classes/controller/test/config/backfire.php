<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test_Config_Backfire extends Controller
{
	public function action_default()
	{
		$package      = $this->request->param('package');
		$version      = $this->request->param('version');
		$request_name = $this->request->param('request_name');

		$this->curl($package, $version, $request_name);
	}

	private function curl($package, $version, $request_name)
	{
		$url = "https://sown-auth2.ecs.soton.ac.uk/package/config/backfire/$package/$version/$request_name";

		$publicKey = "/tmp/pub.key";
		$privateKey = "/tmp/priv.key";
		$mac = "00:11:5b:e4:7e:cb";

		$ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, array("mac", $mac));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($ch, CURLOPT_SSLCERT, $publicKey); 
		curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '');
		curl_setopt($ch, CURLOPT_SSLKEY, $privateKey); 
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
