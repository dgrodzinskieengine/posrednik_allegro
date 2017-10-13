<?php

function smarty_function_box($params, &$smarty)
{
	return $params['from'][$params['uniqueId']];
}

?>