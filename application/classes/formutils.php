<?php defined('SYSPATH') or die('No direct script access.');

class FormUtils {
	public static function parseForm($fields) {
		$formValues = array();
		foreach ($fields as $field => $value)
                {
	                $fieldParts = explode("_", $field);
                        switch (sizeof($fieldParts))
                        {
        	                case 2:
                	                $formValues[$fieldParts[0]][$fieldParts[1]] = $value;
                                        break;
                                case 3:
                                        $formValues[$fieldParts[0]][$fieldParts[1]][$fieldParts[2]] = $value;
                                        break;
				case 4:
                                        $formValues[$fieldParts[0]][$fieldParts[1]][$fieldParts[2]][$fieldParts[3]] = $value;
                                        break;
                                default:
                                        $formValues[$fieldParts[0]] = $value;
                        }
                }
		return $formValues;
	}

	public static function drawForm($name, $fields, $values, $submits = array(), $errors = array(), $success = "", $attributes = array())
	{
		$formHtml = Form::open(NULL, array('name' => $name));
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
		$inlineclass='';
                if (!empty($attributes['inline']))
                {
                                $inlineclass=' class="inline"';
                        }

		$formHtml .= "  <dl$inlineclass>\n";
		foreach ($fields as $f => $field) 
		{
			if(!isset($values[$f]))
			{
				$values[$f] = '';
			}
			$formHtml .= FormUtils::drawField($field, $f, $values[$f]);
		}
		$formHtml .= "  </dl>\n";
		
		if (is_array($submits)) 
		{
			$inlinestyle='';
			if (!empty($attributes['inline']))
			{
				$inlinestyle=' style="display: inline;"';
			}
			$formHtml .= "  <div class=\"buttons\"$inlinestyle>\n";
			if (is_array($submits))
			{
				if (sizeof($submits) == 0) 
				{
					$formHtml .= "    ".Form::submit(NULL, 'Submit')."\n";
				}
				else
				{		
					foreach ($submits as $s => $submit) {
						$formHtml .= "    ".Form::submit($s, $submit) . "&nbsp;\n";
					}
				}
			}
			$formHtml .= "  </div>\n";
		}
       		$formHtml .= Form::close();
		return $formHtml;
	}

	public static function makeStaticForm($formTemplate) 
	{
		foreach ($formTemplate as $f => $field) 
                {
                        if ($field['type'] == 'hidden')
                        {
                                unset($formTemplate[$f]);
                        }
                        elseif (in_array($field['type'], array('fieldset', 'table')))
                        {
				$formTemplate[$f]['fields'] = FormUtils::makeStaticForm($field['fields']);
                        }
                        else
                        {
                                unset($formTemplate[$f]['type']); 
				unset($formTemplate[$f]['hint']);
                        }
                }
		return $formTemplate;
	}

	private static function drawFormTable($table, $name, $values)
	{
		$formHtml = "    <table id=\"$name\" class=\"sowntable\" style=\"margin-bottom: 0.5em;\">\n      <tr class=\"tabletitle\">\n";
		foreach ($table['fields'] as $f => $field) 
		{
			if (empty($field['type']))
			{
				$field['type'] = 'static';
			}
			if ($field['type'] != "hidden")
			{
				$formHtml .= "        <th>" . $field['title'] . "</th>\n";
			}
		}
		$formHtml .= "      </tr>\n";
		$shade = "";
		foreach ($values as $r => $row)
		{
			$formHtml .= "      <tr class=\"sowntablerow\">\n";
			foreach ($table['fields'] as $f => $field)
			{
				if (empty($field['type'])) 
				{
					$field['type'] = "static";
				}
				if (!isset($row[$f]))
				{
					$row[$f] = '';
				}
				if ($field['type'] == "hidden")
				{
					$formHtml .= "        " . FormUtils::drawFormElement($field, $name.'_'.$r.'_'.$f, $row[$f], FormUtils::getTextValue($f, $row)) . "\n";
				}
				else 
				{
					$formHtml .= "        <td$shade>" . FormUtils::drawFormElement($field, $name.'_'.$r.'_'.$f, $row[$f], FormUtils::getTextValue($f, $row)) . "</td>\n";
				}
			}
			$formHtml .= "      </tr>\n";
			if (empty($shade)) {
				$shade = " class=\"shade\"";
			}
			else
			{
				$shade = "";
			}
		}
		$formHtml .= "    </table>\n";	
		return $formHtml;	
	}

	private static function drawFieldset($fieldset, $name, $values)
	{
		$formHtml = "    <fieldset>\n      <legend>" . $fieldset['title'] ."</legend>\n";
		if ($name == "notes")
                {
                        $formHtml .= "<div id=\"NotesMessage\"></div>";
                }
		foreach ($fieldset['fields'] as $f => $field)
		{
			if (!isset($values[$f])) 
			{
				$values[$f] = '';
			}	
			$formHtml .= FormUtils::drawField($field, $name.'_'.$f, $values[$f], FormUtils::getTextValue($f, $values));
		}
		$formHtml .= "    </fieldset>\n"; 
		return $formHtml;
	}

	private static function drawField($field, $name, $value, $textValue = '')
	{
		$formHtml = "";
		if (empty($field['type']))
	        {
                        $field['type'] = "static";
                }
		switch ($field['type'])
		{
			case 'hidden':
                        	$formHtml .= "    " . Form::hidden($name, $value) . "\n";
				break;
			case 'message':
				$formHtml .= "    <p>" . $value . "</p>\n";
				break;
			case 'fieldset':
				$formHtml .= FormUtils::drawFieldset($field, $name, $value);
				break;
			case 'table':
				$formHtml .= FormUtils::drawFormTable($field, $name, $value);
				break;
			case 'button':
				$formHtml .= FormUtils::drawFormElement($field, $name, $value, $textValue);
				break;
                	default:
             			$formHtml .= "    <div>\n";
                        	$formHtml .= "      <dt>" . Form::label($name, $field['title']) . ":</dt>\n";
                        	$formHtml .= "      <dd>" . FormUtils::drawFormElement($field, $name, $value, $textValue);
                        	if (!empty($field['hint']))
                        	{
                                	$formHtml .= "<span class=\"hint\">" . $field['hint'] . "</span>";
                        	}
                        	$formHtml .= "</dd>\n";
                        	$formHtml .= "    </div>\n";
                }
		return $formHtml;
	}

	private static function drawFormElement($field, $name, $value, $textValue = '') 
	{
		if (!isset($value)) {
			$value = "";
		}
		switch($field['type'])
		{
			case 'input':
				if (empty($field['size']))
				{
					$field['size'] = 30;
				}
				return Form::input($name, $value, array('id' => $name, 'size' => $field['size']));
			case 'textarea':
				return Form::textarea($name, $value, array('id' => $name));
			case 'password':
				return Form::password($name);
			case 'select':
				return Form::select($name, $field['options'], $value);
			case 'checkbox':	
				return Form::checkbox($name, 1, !empty($value));
			case 'button':
				return Form::input($name, $field['title'], array('type' => 'button', 'onClick' => $field['onClick']));
			case 'autocomplete':
				return FormUtils::autocomplete($name, $value, $textValue, $field['autocompleteUrl'], array('size' => $field['size']));
			case 'date':
				return FormUtils::datepicker($name, $value);
			case 'datetime':
                                return FormUtils::datetimepicker($name, $value);
			case 'hidden':
				return Form::hidden($name, $value);
			case 'static':
				return $value;
			case 'statichidden':
				return $value . " " . Form::hidden($name, $value);
			default:
				return "Do not recognise type " . $field['type'];
		}
	}

	private static function autocomplete($name, $value, $textValue, $autocompleteUrl, $attributes = array())
	{
		$autocomplete = "<div class=\"ui-widget\">\n";
		$autocomplete .= Form::hidden($name, $value, array('id' => $name)) . "\n";
		$attributes['id'] = $name . "Text";
		$autocomplete .= Form::input($name . "Text", $textValue, $attributes) . "\n";
		$autocomplete .= "</div>\n<script type=\"text/javascript\" language=\"javascript\">
$(function() {
  $('#{$name}Text').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '{$autocompleteUrl}',
        dataType: 'json',
        data: {
          text: $('#{$name}Text').val(),
        },
        success: function(data) {
          response($.map( data.items, function( item ) {
            return {
              id: item.id,
              label: item.label
            }
          }));
        },
      });
    },
    minLength: 2,
    select: function( event, ui ) {
      $('#{$name}').val(ui.item.id);
      $('#{$name}Text').val(ui.item.label);
    },
  });
});
</script>";
		return $autocomplete;
	}

	private static function datepicker($name, $value)
	{
		$datepicker = "<input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\" size=\"10\" />\n";
		$datepicker .= "<script language=\"javascript\"><!--
  $(function() {
    $( \"#$name\" ).datepicker();
  });
--></script>\n";
		return $datepicker;
	}

	private static function datetimepicker($name, $value)
        {
                $datetimepicker = "<input type=\"text\" id=\"$name\" />\n";
                $datetimepicker .= "<script language=\"javascript\"><!--
  $(function() {
    $( \"#$name\" ).datetimepicker();
  });
--></script>\n";
                return $datetimepicker;
        }

	private static function getTextValue($fieldName, $values)
	{
		if (isset($values[$fieldName . "Text"]))
		{
			return $values[$fieldName . "Text"];
		}
		return '';
	}
}