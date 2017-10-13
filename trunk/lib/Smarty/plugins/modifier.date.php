<?php
function smarty_modifier_date($value, $format = 'Y-m-d')
{
	return date($format, $value);
}

/* vim: set expandtab: */

?>
