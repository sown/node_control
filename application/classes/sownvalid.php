<?php defined('SYSPATH') or die('No direct script access.');

class SownValid extends Valid {

        public static function mac($value)
        {
                return (bool) preg_match('/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/', $value);
        }

	public static function ipv4($value, $allownull=false)
	{
		if ($allownull && $value == "") return true;
		return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	public static function ipv4cidr($value)
        {
                return (is_numeric($value) && $value <=30 && $value >=0);
        }

	public static function ipv6($value,$allownull=false)
        {
		if ($allownull && $value == "") return true;
                return (bool) filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

	public static function ipv6cidr($value)
        {
                return (is_numeric($value) && $value <=126 && $value >=0);
        }

	public static function ssid($value)
        {
                return (bool) preg_match('/^[^"<>\'&]{0,30}$/', $value);
        }

	public static function wirelessChannel($value, $type = 'g')
	{
		if  (!is_numeric($value))
		{
			return FALSE;
		}
		switch($type)
		{
			case 'g':
				return ($value <=13 && $value >=1);
			case 'n':
				return (($value <=13 && $value >=1) || in_array($value, array(36,40,44,48,52,56,60,64,100,104,108,112,116,120,124,128,132,136,140)));
			case 'a':
				return (in_array($value, array(36,40,44,48,52,56,60,64,100,104,108,112,116,120,124,128,132,136,140)));
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
}
