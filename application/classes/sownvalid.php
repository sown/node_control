<?php defined('SYSPATH') or die('No direct script access.');

class SownValid extends Valid {

        public static function mac($value, $intf_name="")
        {
		if (preg_match("/^lo/", $intf_name))
                {
			return empty($value);
		}
                return (bool) preg_match('/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/', $value);
        }

	public static function ipv4($value, $allownull=false)
	{
		if ($allownull && $value == "") return true;
		return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	public static function ipv4cidr($value, $allownull=false, $intf_name="")
        {
		if ($allownull && $value == "") return true;
		$smallest = 30;
		if (preg_match("/^lo/", $intf_name))
		{
			$smallest = 32;
		}
                return (is_numeric($value) && $value <=$smallest && $value >=0);
        }

	public static function ipv6($value, $allownull=false)
        {
		if ($allownull && $value == "") return true;
                return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

	public static function ipv6cidr($value, $allownull=false)
        {
		if ($allownull && $value == "") return true;
                return (is_numeric($value) && $value <=126 && $value >=0);
        }

	public static function ssid($value)
        {
                return (bool) preg_match('/^[^"<>\'&]{0,30}$/', $value);
        }
	
	public static function interfaceName($value)
	{
		return (bool) preg_match('/^[a-z][a-z0-9:\.\-]{1,63}$/', $value);
	}

	public static function wirelessChannel($value, $type = 'g')
	{
		if  (!is_numeric($value))
		{
			return FALSE;
		}
		switch($type)
		{
			case 'b':
			case 'g':
			case 'bg':
				return ($value <=13 && $value >=1);
			case 'abg':
			case 'abgn':
			case 'bgn':
				return (($value <=13 && $value >=1) || in_array($value, array(36,40,44,48,52,56,60,64,100,104,108,112,116,120,124,128,132,136,149,153,157,161)));
			case 'a':
			case 'ac':
			case 'n':
			case 'nac':
				return (in_array($value, array(36,40,44,48,52,56,60,64,100,104,108,112,116,120,124,128,132,136,149,153,157,161)));
		}
		return FALSE;
	}
	public static function emptyField($value)
	{
		return empty($value);
	}
	
	public static function csvlist($value)
	{
		return (bool) preg_match('/^([1-9][0-9]*,)*[1-9][0-9]*$/', $value);
	}
	
	public static function localCname($value)
	{
		return (bool) preg_match('/^[a-z][a-z0-9\-]{0,29}$/', $value);
	}
	
	public static function notNodeCname($value)
	{
		return (bool) !preg_match('/^node[0-9]*$/', $value);
	}

}
