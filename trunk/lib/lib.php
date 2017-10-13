<?php

define("DIR", dirname(__FILE__));

require_once(DIR.'/../configure.php');

if (isset($_SERVER['REMOTE_ADDR']))
{
	if ($_SERVER['REMOTE_ADDR'] == '192.162.150.105')
	{
		define('DBG', true);
	}
	else
	{
		define('DBG', false);
	}
}
else
{
	define('DBG', true);
}

#define('MAIL_FROM', 'allegro@walaszek.pl');
#define('MAIL_FROMNAME', 'Pośrednik Allegro');
#define('MAIL_USERNAME', 'allegro@walaszek.pl');
#define('MAIL_PASSWORD', '8reksio');
#define('MAIL_MAILER', 'smtp');
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_AUTH', true);
define('MAIL_SMTP_SECURE', 'tls');
define('MAIL_SMTP_PORT', 587);
define('MAIL_POP_HOST', 'pop.gmail.com');
define('MAIL_POP_SECURE', 'ssl');
define('MAIL_POP_PORT', 995);

require_once('Core/ActiveRecord.php');
require_once('Core/Debug.php');
require_once('Core/Link.php');

if (!class_exists('Smarty'))
	require_once("Smarty/Smarty.class.php");

$smarty = new Smarty();
$smarty->template_dir = DIR;
$smarty->compile_dir = DIR.'/../temp/smarty_compile';
$smarty->cache_dir = DIR.'/../temp/smarty_cache';

$smarty->force_compile = true;	// << na produkcyjnym musi być false
$smarty->caching = true;		// << na produkcyjnym powinno być true

function __autoload($className)
{
	list($type) = explode("_", $className);
	if (in_array($type, array('ActiveRecord', 'Box', 'Core', 'Theme')))
	{
		$fileName = strtr($className, array("_" => "/"));
		require_once($fileName. '.php');
	}
}

function db()
{
	return Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_PREF);
}

$db = db();

function consoleLog($mess)
{
	print date('Y-m-d H:i:s').' / '.$mess."\n";
}

// aby w kodzie separatorem dziesiętnym była kropka
setlocale(LC_NUMERIC, 'en_US.utf8');



