<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File: function.cmodule.php
* Type: function
* Name: cmodule
* Purpose: Output header containing the source file name and
* the time it was compiled.
* -------------------------------------------------------------
*/
function smarty_function_cmodule($tag_arg, &$smarty){
require_once(DIR_BASE.'frontend/modules/'.$tag_arg['modname'].'.php');
	$module = new $tag_arg['modname'];
	$core = core::getInstance();

	$core->template->assign('tag_args',$module->$tag_arg['funname']($tag_arg));
	$output = $core->template->fetch($tag_arg['file'].'.tpl');
return $output;
}
?>