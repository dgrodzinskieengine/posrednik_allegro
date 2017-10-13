<?php

function tmpdbg($msg)
{
	if (is_array($msg) || is_object($msg)) $msg = print_r($msg,true);
	$param = debug_backtrace();
	$line = isset($param[0]['line'])?$param[0]['line']:'-- line --';
	$file = isset($param[0]['file'])?str_replace(realpath(__DIR__.'/../../'), '',$param[0]['file']) :'-- file --';
	file_put_contents('/tmp/dbg_allegro.log', date("Y-m-d H:i:s")." | {$file}[{$line}] | $msg\n", FILE_APPEND);
}
/**
 * @brief funkcje do szybkiego debugowania
 */
function l($msg = "", $stack = false) {
	return lv($msg, -1, 1, $stack);
}
function &lv(&$msg = "", $skipStart = 0, $skipLen = 0, $stack = false) {
  if(defined("DBG") && DBG === true) {
/*		$stack = xdebug_get_function_stack(); // array_reverse(
		array_splice($stack, $skipStart, $skipLen);
		foreach($stack as $i => $s) {
			$file = file($stack[$i]['file']);
			echo "{".$stack[$i]['file']."}:{$stack[$i]['line']} <b>".htmlentities(trim($file[$stack[$i]['line']-1]))."</b>\n";
		}
*/
		if($stack === false) {
			$stack = array_reverse(debug_backtrace());
		}
		array_splice($stack, $skipStart, $skipLen);
		print "<style>.dbg { text-align: left; position: relative; z-index: 1000; background: white; opacity: 0.95; border: 1px solid #808080; white-space: pre;} .dbg, .dbg code { font-family: Dina, Clean, Luicda Console, Monospace; font-size: 8pt; }</style>";
		print "<table class='dbg'>";
		$indent = "";
		foreach($stack as $i => $call) {
			$filePath = $call['file'];
			//$filePath = str_replace('C:\\www\\', 'okazje/', $filePath); // cut server path
			//$filePath = str_replace('/home/scotty/okazje/', 'okazje/', $filePath); // cut server path
			//$filePath = str_replace('/home/fizol/okazje/trunk/', 'okazje/', $filePath); // cut server path
			//$filePath = str_replace('/home/gangrena/scotty_filtry/', 'scotty_filtry/', $filePath); // cut server path
			$filePath = preg_replace('`^'.preg_quote(implode(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)), 0, -3)).DIRECTORY_SEPARATOR).'`is', '/',$filePath);
			$filePath = str_replace('\\', '/', $filePath);
			$fileContent = file_exists($call['file']) ? file($call['file']) : array();
			print "<tr>";
			print "<td valign='top'><a onClick='(new Image()).src=\"http://localhost:1305/$filePath:{$call['line']}?\"+Math.random(); return 0;' href='javascript:void(0)'>$filePath:{$call['line']}</a></td>";
			print "<td valign='top' style='font-weight: bold'>$indent&#172;".trim($fileContent[$call['line']-1])."</td>";
			print "<td valign='top' style='cursor:pointer; _cursor:hand;' onclick='this.childNodes[1].style.display = this._state = this._state == \"inline\" ? \"none\" : \"inline\"'><span>";
			if(isset($call['class']) && isset($call['type'])) {
				print "{$call['class']}{$call['type']}";
			}
			print "{$call['function']}(</span><span style='display:none;color:black;'>";
			$notFirst = false;
			if(isset($call['args']) && count($call['args'])>0) {
				foreach($call['args'] as $arg) {
					if($notFirst) { print ", "; }
					$notFirst = true;
					// highlight_string(var_export($arg));
					echo htmlspecialchars(print_r($arg, true));
				}
			}
			print "</span><span>)</span></td>";
			print "</tr>";
			$indent .= "&nbsp;";
		}
		print "<tr><td colspan='3'>";
		if (is_bool($msg))
			var_dump($msg);
		else
			echo htmlspecialchars(print_r($msg, true));
		print "</td></tr></table>";
  }
  return $msg;
}

if(defined("DBG") && DBG != false) {
	set_error_handler("inline_error_handler_with_stack_trace", ini_get('error_reporting'));
	set_exception_handler("inline_exception_handler_with_stack_trace");
}
function inline_exception_handler_with_stack_trace($e) {
	$msg = get_class($e).": ".$e->getMessage();
	$trace = array_reverse($e->getTrace());
	$trace[] = array(
		'file' => $e->getFile(),
		'line' => $e->getLine()
	);
	lv($msg, 0, 0, $trace);
}
function inline_error_handler_with_stack_trace($errno, $errstr) {
	if(!($errno & error_reporting())) return;
	if($errno & E_WARNING) $type[] = "E_WARNING";
	if($errno & E_NOTICE) $type[] = "E_NOTICE";
	if($errno & E_USER_ERROR) $type[] = "E_USER_ERROR";
	if($errno & E_USER_WARNING) $type[] = "E_USER_WARNING";
	if($errno & E_USER_NOTICE) $type[] = "E_USER_NOTICE";
	if($errno & E_STRICT) $type[] = "E_STRICT";
	if($errno & E_RECOVERABLE_ERROR) $type[] = "E_RECOVERABLE_ERROR";
	$errstr = "\n=================\n<b>".implode(", ", $type).": $errstr</b>\n=================\n";
	lv($errstr, -1, 1);
	return true;
}

if(defined("DBG") && DBG === true)
	error_reporting(E_ALL ^ E_NOTICE);
else
	error_reporting(0);

?>