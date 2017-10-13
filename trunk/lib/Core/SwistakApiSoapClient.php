<?php

class Core_SwistakApiSoapClient extends SoapClient
{
	private $soapClient = null;

	public function __construct()
	{
		parent::__construct("http://www.swistak.pl/out/wsdl/wsdl.html?wsdl", array('trace' => 1));
	}
}

$swistakApiSoapClient = null;
function Sw($functionName, $params = array())
{
// 	l($functionName);
// 	l($params);
// 	die;
	global $swistakApiSoapClient;
	try
	{
		if ($swistakApiSoapClient == null)
			$swistakApiSoapClient = new Core_SwistakApiSoapClient();

		return $swistakApiSoapClient->$functionName($params);
	}
	catch(SoapFault $soapFault)
	{
		return $soapFault;
		//die("ERROR: Coś poszło niepoprawnie [#30].");
	};
}