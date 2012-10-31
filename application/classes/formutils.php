<?php defined('SYSPATH') or die('No direct script access.');

class FormUtils {

	public static function drawForm($fields, $values, $submits = array(), $errors = array(), $success = "")
	{
		$formHtml = Form::open();
		if (!empty($errors))
		{
			$formHtml .= "  <p class=\"error\">Some errors were encountered, please check the details you entered.</p>\n";
	        	$formHtml .= "  <ul class=\"errors\">\n";
			foreach ($errors as $e => $error) 
			{
				$formHtml .= "    <li>$e should be $error[0]</li>\n";
			}
			$formHtml .= "  </ul>";
		}
		if (!empty($success)) 
		{
			$formHtml .= "  <p class=\"success\">$success</p>\n";
		}
		$formHtml .= "  <dl>\n";
		foreach ($fields as $f => $field) 
		{
			$formHtml .= "    <dt>" . Form::label($f, $field['title']) . "</dt>\n";
			$formHtml .= "    <dd>" . FormUtils::drawElement($field, $f, $values[$f]);
			if (!empty($field['hint'])) 
			{
				$formHtml .= "&nbsp;" . $field['hint'];
			}
			$formHtml .= "</dd>\n";
	
		}
		if (!empty($submits)) 
		{
			$formHtml .= "  ".Form::submit(NULL, 'Submit');
		}
		else 
		{		
			foreach ($submits as $s => $submit) {
				$formHtml .= "  ".Form::submit($s, $submit) . "&nsbp;";
			}
		}
       		$formHtml .= Form::close();
		return $formHtml;
	}

	private static function drawElement($field, $name, $value) 
	{
		if (!isset($value)) {
			$value = "";
		}
		switch($field['type'])
		{
			case 'input':
				return Form::input($name, $value);
			case 'textarea':
				return Form::textarea($name, $value);
			case 'select':
				return Form::select($name, $field['options'], $value);
			default:
				return "Do not recognise type $type";
		}
	}	

}
