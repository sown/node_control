<?php defined('SYSPATH') or die('No direct script access.');

class UCIUtils
{

	public static function set_hash($package, array & $config, $last_modified = NULL)
	{
		// Make sure we have a meta block
		if (! isset($config['meta']))
		{
			$config['meta']=array(array());
		}
		else
		{
			// Make sure we only have one meta block
			$config['meta'] = array($config['meta'][0]);
			
			// Unset the hash entry
			unset($config['meta'][0]['hash']);
		}
		
		return $config['meta'][0]['hash'] = md5(static::render_UCI_config($package, $config));
	}

	public static function render_UCI_config($package, array $config)
	{
		$lines = array('package \''.$package.'\'','');
		
		foreach ($config as $type => $instances) {
			foreach ($instances as $key => $options) {
				if (is_numeric($key))
					$lines[] = "config '$type'";
				else
					$lines[] = "config '$type' '$key'";
				
				foreach ($options as $option => $value) {
					if (! is_array($value))
					{
						$lines[] = "\toption '$option' '$value'";
					}
					else
					{
						foreach ($value as $entry) {
							$lines[] = "\tlist '$option' '$entry'";
						}
					}
				}
				$lines[] = '';
			}
		}
		
		return implode("\n", $lines);
	}

}
