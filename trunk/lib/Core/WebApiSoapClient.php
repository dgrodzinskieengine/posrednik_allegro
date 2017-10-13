<?php

class Core_WebApiSoapClient extends SoapClient
{
	private $soapClient = null;

	public function __construct()
	{
		parent::__construct(null, array(
			"uri" => ALLEGRO_POSREDNIK_HOST,
			"location" => "http://".ALLEGRO_POSREDNIK_HOST."/webapi"
		));
	}
}

$webApiSoapClient = null;
function S($functionName, $params = array())
{
// 	l($functionName);
// 	l($params);
// 	die;
	global $webApiSoapClient;
	try
	{
		if ($webApiSoapClient == null)
			$webApiSoapClient = new Core_WebApiSoapClient();

		return $webApiSoapClient->$functionName($params);
	}
	catch(SoapFault $soapFault)
	{
		return $soapFault;
		//die("ERROR: Coś poszło niepoprawnie [#30].");
	};
}