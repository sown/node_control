<?php defined('SYSPATH') or die('No direct script access.');

class ShellUtils {

	public static function arrayToShellVars(array $array)
	{
		$out = array();
		foreach ($array as $key => $value) {
			$out[] = $key.'='.$value;
		}
		
		return implode("\n", $out);
	}
}
