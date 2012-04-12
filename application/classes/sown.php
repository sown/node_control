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
}