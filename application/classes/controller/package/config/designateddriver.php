<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Package_Config_Designateddriver extends Controller
{
	public function action_default()
	{
		$package      = $this->request->param('package');
		$version      = $this->request->param('version');
		$request_name = $this->request->param('request_name');

		if (substr($package, 0, 13) != 'sown_openwrt_')
			Package_Config::send_shell_script("echo \"Unsupported package '$package'\" >&2; return 1\n");
		
		$short = substr($package, 13);
		$classname = 'Package_Config_Designateddriver_' . ucfirst($short);
		
		if (! class_exists($classname))
			Package_Config::send_shell_script("echo \"Unable to find configure script for package '$package'.\" >&2; return 1\n");
		
		
		if (! isset($classname::$supported[$request_name]))
			Package_Config::send_shell_script("echo \"Request '$request_name' for package '$package' not supported.\" >&2; return 1\n");
		
		
		$versions = $classname::$supported[$request_name];
		// Go through backwards, newest version first
		$found = FALSE;
		for ($i=count($versions) -1; $i >= 0 ; $i--) {
			// Check we match the minimum version
			if ((isset($versions[$i]['>']) && 
					version_compare($version, $versions[$i]['>'], '>')) ||
				(isset($versions[$i]['>=']) &&
					version_compare($version, $versions[$i]['>='], '>=')))
			{
				// If there is a max version, and we do not match it, skip
				if ((isset($versions[$i]['<']) && 
						! version_compare($version, $versions[$i]['<'], '<')) ||
					(isset($versions[$i]['<=']) &&
						! version_compare($version, $versions[$i]['<='], '<=')))
					continue;
				$found = $i;
				break;
			}
		}

		if ($found === FALSE)
		{
			SOWN::send_irc_message('Node config: client '.Request::$client_ip.' requested a resource which could not be found: '."$package:$version:$request_name.");
			Package_Config::send_shell_script("echo \"Request '$request_name' at version '$version' for package '$package' not supported.\" >&2; return 1\n");
		}
		
		$node = $classname::get_node($this->request);
		if ($node == null)
		{
			SOWN::send_irc_message('Node config: Unable to determine node from client '.Request::$client_ip.'.');
			Package_Config::send_shell_script("echo \"Unable to determine node from request for '$request_name' at version '$version' for package '$package'.\" >&2; return 1\n");
		}
		
		//SOWN::send_irc_message('calling '.$classname.'::'.$versions.'['.$found.']["method"]('.$node.', '.$version.');');
		SOWN::notify_icinga($node->hostname, "UPDATE-CONFIG", 0, "UPDATE-CONFIG OK: Node requested '$request_name' for '$package'");
		$classname::$versions[$found]['method']($node, $version);
	}
}
