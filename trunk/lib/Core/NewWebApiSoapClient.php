<?php
class Core_NewWebApiSoapClient extends SoapClient
{
	public function __construct()
	{
		parent::__construct('https://webapi.allegro.pl/service.php?wsdl');
	}
}