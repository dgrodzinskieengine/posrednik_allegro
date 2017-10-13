<?php

class Theme_Webapi extends Core_Template
{
	public function defaultView($args)
	{
		//ini_set("soap.wsdl_cache_enabled", 0);
		//ini_set("session.auto_start", 0);
		//session_start();

		$server = new SoapServer(null, array(
				"uri" => DOMENA
			));
		$server->setClass("Core_WebApiServer");
		$server->setPersistence(SOAP_PERSISTENCE_SESSION);
		$server->handle();
		die;	///< bez tego w odpowiedzi siedzą jeszcze jakieś cuda, które psują całą zabawę
	}
}
