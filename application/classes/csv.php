<?php defined('SYSPATH') or die('No direct script access.');

class CSV
{
        public static function string_to_array($data, $hasnames = false)
        {
		$records = explode("\n", $data);
		$array = array();
		if ($hasnames) 
		{
			$fields = explode(",", array_shift($records));
		}
		foreach ($records as $record) {
			$elements = explode(",", $record);
			if (isset($fields)) 
			{
				$elemstemp = $elements;
				$elements = array(); 
				foreach($elemstemp as $e => $elemtemp) 
				{
					$elements[$fields[$e]] = $elemtemp;
				}
			}
			$array[] =  $elements;
		}
		return $array;
	}
}
