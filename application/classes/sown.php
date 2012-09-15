<?php defined('SYSPATH') or die('No direct script access.');

class SOWN
{
	public static function send_irc_message($message)
	{
		$host = 'bot.sown.org.uk';
		$port = 4444;
		
		if ($_SERVER['HTTP_HOST'] == 'www.sown.org.uk')
			$host = 'sown-vpn.ecs.soton.ac.uk';
		
		$fp = fsockopen($host, $port);
		fwrite($fp, $message);
		fclose($fp);
	}

	public static function notify_icinga($hostname, $service, $status, $message)
	{
		$host = 'monitor.sown.org.uk';
		$port = 8080;

		$post = '{"host": "'.$hostname.'", "service": "'.$service.'", "status": '.$status.', "output": "'.$message.'"}';
		$url = "http://$host:$port/submit_result";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		return curl_exec($ch);
	}

	public static function send_nsca($host, $service, $status, $message)
	{
	        $data="$host\t$service\t$status\t$message\n";

	        $descriptorspec = array(
	           0 => array("pipe", "r"),
	           1 => array("file", "/dev/null", "a"),
	           2 => array("file", "/dev/null", "a")
	        );

	        $process = proc_open('/usr/sbin/send_nsca -H monitor.sown.org.uk', $descriptorspec, $pipes, "/tmp", NULL);

	        if (is_resource($process))
	        {
	                fwrite($pipes[0], $data);
	                fclose($pipes[0]);
	
	                $return_value = proc_close($process);
	        }
	}

}
