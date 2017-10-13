<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
* Smarty function plugin
* Requires PHP >= 4.3.0
* -------------------------------------------------------------
* Type:     function
* Name:     fckeditor
* Version:  1.1
* Author:   gazoot (gazoot care of gmail dot com)
* Purpose:  Creates a FCKeditor, a very powerful textarea replacement.
* -------------------------------------------------------------
* @param InstanceName Editor instance name (form field name)
* @param BasePath optional Path to the FCKeditor directory. Need only be set once on page. Default: /js/fckeditor/
* @param Value optional data that control will start with, default is taken from the javascript file
* @param Width optional width (css units)
* @param Height optional height (css units)
* @param ToolbarSet optional what toolbar to use from configuration
* @param CheckBrowser optional check the browser compatibility when rendering the editor
* @param DisplayErrors optional show error messages on errors while rendering the editor
*
* Default values for optional parameters (except BasePath) are taken from fckeditor.js.
*
* All other parameters used in the function will be put into the configuration section,
* CustomConfigurationsPath is useful for example.
* See http://docs.fckeditor.net/FCKeditor_2.x/Developers_Guide/Configuration/Configuration_Options for more configuration info.
*/
function smarty_function_fckeditor($params, &$smarty)
{
	if(!isset($params['InstanceName']) || empty($params['InstanceName']))
	{
		$smarty->trigger_error('fckeditor: required parameter "InstanceName" missing');
	}

	static $base_arguments = array();
	static $config_arguments = array();

	// Test if editor has been loaded before
	$init = count($base_arguments) == 0;
	
	// BasePath must be specified once.
	if(isset($params['BasePath']))
	{
		$base_arguments['BasePath'] = $params['BasePath'];
	}
	else if(empty($base_arguments['BasePath']))
	{
		$base_arguments['BasePath'] = '/frontend/js/fckeditor/';
	}

	$base_arguments['InstanceName'] = $params['InstanceName'];

	if(isset($params['Value']))
		$base_arguments['Value'] = $params['Value'];
	else
		$base_arguments['Value'] = '';

	if(isset($params['Width'])) $base_arguments['Width'] = $params['Width'];
	if(isset($params['Height'])) $base_arguments['Height'] = $params['Height'];
	if(isset($params['ToolbarSet'])) $base_arguments['ToolbarSet'] = $params['ToolbarSet'];
	if(isset($params['CheckBrowser'])) $base_arguments['CheckBrowser'] = $params['CheckBrowser'];
	if(isset($params['DisplayErrors'])) $base_arguments['DisplayErrors'] = $params['DisplayErrors'];

	// Use all other parameters for the config array (replace if needed)
	$other_arguments = array_diff_assoc($params, $base_arguments);
	$config_arguments = array_merge($config_arguments, $other_arguments);

	$out = '';

	if($init)
	{
		$out .= '<script type="text/javascript" src="' . $base_arguments['BasePath'] . 'fckeditor.js"></script>';
	}

	$out .= "\n<script type=\"text/javascript\">\n";
	$out .= "var oFCKeditor = new FCKeditor('" . $base_arguments['InstanceName'] . "');\n";

	foreach($base_arguments as $key => $value)
	{
		if(!is_bool($value))
		{
		$value = '"' . fck_escapejschars($value) . '"';
		}
		$out .= "oFCKeditor.$key = $value; ";
	}

	foreach($config_arguments as $key => $value)
	{
		if(!is_bool($value))
		{
		$value = '"' . fck_escapejschars($value) . '"';
		}
		$out .= "oFCKeditor.Config[\"$key\"] = $value; ";
	}

	$out .= "\noFCKeditor.Create();\n";
	$out .= "</script>\n";
	
	return $out;
}

function fck_escapejschars($str)
{
	$str = mb_ereg_replace("\\\\", "\\\\", $str);
	$str = mb_ereg_replace("\"", "\\\"", $str);
	$str = mb_ereg_replace("'", "\\'", $str);
	$str = mb_ereg_replace("\r\n", "\\n", $str);
	$str = mb_ereg_replace("\r", "\\n", $str);
	$str = mb_ereg_replace("\n", "\\n", $str);
	$str = mb_ereg_replace("\t", "\\t", $str);
	$str = mb_ereg_replace("<", "\\x3C", $str); // for inclusion in HTML
	$str = mb_ereg_replace(">", "\\x3E", $str);

	return $str;
}