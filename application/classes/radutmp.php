<?php
class radutmp
{
	public static function get_info()
	{
		$ds = array();
		$fh = fopen(Kohana::$config->load('system.default.static_files.radutmp'), 'r');
		while(!feof($fh))
		{
			$str = fread($fh, 112);
			if(strlen($str) == 0)
			{
				break;
			}
			$foo = unpack('c32login/Inas_port/c8session_id/Nnas_address/Nframed_address/iproto/l2time/l2delay/Itype/cporttype/c3res/c16caller_id/c12reserved', $str);
			$d = array();
			foreach(array('login', 'session_id', 'caller_id') as $key)
			{
				$id = 1;
				$d[$key] = '';
				while(isset($foo[$key.$id]))
				{
					$d[$key] .= chr($foo[$key.$id]);
					$id++;
				}
				$d[$key] = trim($d[$key]);
				if($key == 'login')
				{
					$d[$key] = str_replace('-', ':', strtolower($d[$key]));
				}
			}
			foreach(array('nas_port', 'proto', 'type') as $key)
			{
				$d[$key] = $foo[$key];
			}
			$d['time'] = date('r', $foo['time1']);
			foreach(array('nas_address', 'framed_address') as $key)
			{
				$d[$key] = IP_Address::factory(long2ip($foo[$key]));
			}
			if($d['type'] == 1)
			{
				$ds[(string)$d['framed_address']] = $d;
			}
		}
		fclose($fh);
		return $ds;
	}

	public static function get_info_from__device_ip($ip)
	{
		$d = static::get_info();
		if(isset($d[$ip]))
		{
			return $d[$ip];
		}
		return null;
	}

	public static function get_mac_from_device_ip($ip)
	{
		$d = static::get_info();
		if(isset($d[$ip]))
		{
			return $d[$ip]['login'];
		}
		return null;
	}

	public static function get_node_ip_from_device_ip($ip)
	{
		$d = static::get_info();
		if(isset($d[$ip]))
		{
			return $d[$ip]['nas_address'];
		}
		return null;
	}
}
