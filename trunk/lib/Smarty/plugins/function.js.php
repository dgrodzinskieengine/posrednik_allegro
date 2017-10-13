<?php
/**
 */
function smarty_function_js($params, &$smarty)
{
	$outputHtmlArray = array();
	if (isset($params['from']) && is_array($params['from']))
	{
		foreach ($params['from'] as $index => $js)
		{
			$outputHtmlArray[] .= "<script type='text/javascript' src='{$js}'></script>";
		}
	}
	return "<!-- js -->".implode("\n", $outputHtmlArray)."<!-- end:js -->";
}
?>
