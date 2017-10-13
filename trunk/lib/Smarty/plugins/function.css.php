<?php
/**
 */
function smarty_function_css($params, &$smarty)
{
	$outputHtmlArray = array();
	if(isset($params['from']) && is_array($params['from']))
	{
		foreach ($params['from'] as $index => $css)
		{
			$outputHtmlArray[] = "<link rel='stylesheet' type='text/css' href='{$css}'>";
		}
	}
	return "<!-- css -->".implode("\n", $outputHtmlArray)."<!-- end:css -->";
}
?>
