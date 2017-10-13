<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty breakUrl modifier plugin
 *
 * Type:     modifier<br>
 * Name:     indent<br>
 * Purpose:  indent lines of text
 * @author   Kamil Szot <kamil.szot at gmail dot com>
 * @param string
 */
function smarty_modifier_breakUrl($string)
{
	return preg_replace('`/[^/]|&[^&]`is', '<wbr>\\0', $string);
}

?>
