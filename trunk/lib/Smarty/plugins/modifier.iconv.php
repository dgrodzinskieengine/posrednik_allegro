<?php
function smarty_modifier_iconv($string, $from = 'iso-8859-2', $to = 'utf-8')
{
	return iconv($from, $to, $string);
}

/* vim: set expandtab: */

?>
