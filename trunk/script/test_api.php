<?php
include('../lib/lib.php');
$test = new Core_WebApiServer();

$test->doLogin(array('login' => '', 'password' => ''));
var_dump($test->whoLogin(array()));
// print_r($test);