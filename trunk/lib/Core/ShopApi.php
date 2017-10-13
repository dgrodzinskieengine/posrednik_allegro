<?php
class Core_ShopApi extends SoapClient {


	function __construct($url = '', $login_param = '', $pass_param = '') {
		parent::__construct($url.'/api/?WSDL', array('trace' => true, 'cache_wsdl' => WSDL_CACHE_NONE));
		
		$login = new StdClass;
			$login->login = $login_param;
			$login->password = $pass_param;
			if($login_param != '' && $pass_param != '')
			self::doAllegroLogin($login);
	}

	public function Login($params)
	{
		$login = new StdClass;
		$login->login = $params['login'];
		$login->password = $params['pass'];
		return self::doAllegroLogin($login);
	}

}