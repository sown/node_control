<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Package_List extends Controller
{
	protected $upstream_list_url;
	
	public function action_default()
	{
		// TODO set a cache lifetime somehow
		$packages = Request::factory($this->upstream_list_url, Cache::instance())
			->execute();

		try
		{
			$uncompressed = gzuncompress($packages->body());
		}
		catch (Exception $e)
		{
			// zlib is crap.
			$temp = tmpfile();
			fwrite($temp, $packages->body());
			fseek($temp, 0);
			
			$proc = proc_open('gunzip', 
				array(
					0 => $temp,
					1 => array("pipe", "w")
				),
				$pipes);
			
			$uncompressed = stream_get_contents($pipes[1]);
			
			$return = proc_close($proc);
			
			fclose($temp);
		}
		
		echo $data;
	}
}