<?php

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("parse_message", "yet_another_table_mycode_parse_message");

function yet_another_table_mycode_info()
{
    return array(
		"name"			=> "Yet Another Table MyCode",
		"description"	=> "Simple, flexible and powerful MyCode for tables.",
		"website"		=> "https://github.com/vincemaz/yet-another-table-mycode",
		"author"		=> "VinceMaz",
		"authorsite"	=> "https://github.com/vincemaz",
		"version"		=> "1.0.0",
		"codename"		=> str_replace('.php', '', basename(__FILE__)),
		"compatibility" => "18*"
	);
}

function yet_another_table_mycode_parse_message($message)
{
	$content = yet_another_table_mycode_parse_table($message);
    return $content;
}

function yet_another_table_mycode_parse_table($content) {
	$defaultOptions = array("width"=>"50%", "align"=>"center");
	$content = preg_replace_callback(
		"#\[table=?(.*?)](.*?)\[/table]#si", 
		function ($matches) use ($defaultOptions){
			$htmlAttributes = yet_another_table_mycode_parse_options($matches[1], $defaultOptions);
			return '<table'.$htmlAttributes.">".yet_another_table_mycode_parse_row($matches[2])."</table>";
	    },
	    $content);

	return $content;
}

function yet_another_table_mycode_parse_row($content) {
	$content = preg_replace_callback(
		"#\R*\[tr=?(.*?)](.*?)\[/tr]\R*#si", 
		function ($matches) use (&$count, $damnit){
			$count++;
			$htmlAttributes = yet_another_table_mycode_parse_options($matches[1]);
			return "<tr".$htmlAttributes.">".yet_another_table_mycode_parse_column($matches[2], $count)."</tr>";
	    },
	    $content, -1, $count);
	
	return trim($content);
}

function yet_another_table_mycode_parse_column($content, $rowNum) {
	$defaultOptions = array("align"=>"center");
	$content = preg_replace_callback(
		"#\R*\[td=?(.*?)]\R*(.*?)\R*\[/td]\R*#si", 
		function ($matches) use ($rowNum, $defaultOptions){
			$defaultOptions['class'] = 'trow'.(($rowNum%2)+1);
			$htmlAttributes = yet_another_table_mycode_parse_options($matches[1], $defaultOptions);
			return '<td'.$htmlAttributes.'>'.$matches[2].'</td>';
	    },
	    $content);

	$defaultOptions = array("class"=>"tcat", "align"=>"center");
	$content = preg_replace_callback(
		"#\R*\[th=?(.*?)]\R*(.*?)\R*\[/th]\R*#si", 
		function ($matches) use ($defaultOptions) {
			$htmlAttributes = yet_another_table_mycode_parse_options($matches[1], $defaultOptions);
			return '<th'.$htmlAttributes.'>'.$matches[2].'</th>';
	    },
	    $content);		
	return trim($content);
}

function yet_another_table_mycode_parse_options($customOptions, $defaultOptions=null) {
	$htmlAttributes = "";
	if (!empty($customOptions)) {
		$options = json_decode($customOptions, true, 2);
		// merge custom options with default options
		if(!empty($defaultOptions)) {
			foreach ($defaultOptions as $key => $value) {
				if (!array_key_exists($key, $options)){
					$options[$key] = $value;
				}
			}	
		}
	}
	else if(!empty($defaultOptions)){
		$options = $defaultOptions;
	}

	// build html attributes
	if(!empty($options)) {
		foreach ($options as $key => $value) {
			$htmlAttributes .= " $key=";
	        if(is_string($value)){
	         	$htmlAttributes .= "\"$value\"";
	        }
	        else {
	        	$htmlAttributes .= $value;
	        }
	    }
	}

    return $htmlAttributes;
}