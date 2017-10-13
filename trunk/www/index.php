<?php

require_once("../lib/lib.php");

list($themeName, $actionName, $args) = Link::parseUrl($_SERVER["REQUEST_URI"]);
if($themeName && $actionName)
{
	Core_Template::run($smarty, 'Theme', $themeName, $actionName, $args);
}
else
{
	header("HTTP/1.x 404 Not Found");
	echo "404";
}


