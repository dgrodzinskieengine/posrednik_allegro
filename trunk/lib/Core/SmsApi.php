<?php
define('CORE_SMSAPI_LIB_PATH', '../smsapi');

class Core_SMSApi {
	private $eco_price = 0.07; //cena smsa w pakiecie eco
	public static $instance = null;
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new Core_SMSApi();die('test');
		} 
		return self::$instance;
	}
	private function __construct() {
		require CORE_SMSAPI_LIB_PATH.'/Autoload.php';
		$client = new \SMSApi\Client('ecommerce24h');
		$client -> setPasswordHash('69c999fbbac6c7426a960cd441047949');
		$this -> apiClient = new \SMSApi\Api\SmsFactory();
		$this -> apiClient -> setClient($client);
	}
	//
	private $test_mode = true;
	private $test_mode_admin = false;
	private $apiClient;
	private function _sendSMS($params) {
		if (isset($params['to'], $params['text'])) {
			try {
				$sms = $this -> apiClient -> actionSend();
				$sms -> setTo($params['to']);
				$sms -> setText($params['to']);
				$sms -> setTest($this -> test_mode);
				$response = $sms -> execute();
				$id = $response->getList() ->getNumber();
				if ($id) {
					return $id->getError();
				} else {
					return false;
				}
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}

	public function sendAdminSMS($params)
	{
		if (isset($params['to'], $params['text'])) {
			try {
				$sms = $this -> apiClient -> actionSend();
				$sms -> setTo($params['to']);
				$sms -> setText($params['text']);
				$sms -> setTest($this -> test_mode_admin);
				$response = $sms -> execute();
				$id = $response->getList() ->getNumber();
				if ($id) {
					return $id;
				} else {
					return false;
				}
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/*
	 * @param params = array(
	 * 	limit - int, maksymalna ilość smsów do pobrania i wysłania (domyślnie 0 - pobranie wszystkich smsów z kolejki)
	 * 	shops_id - id sklepu, dla którego wysłane będą smsy (domyślnie - wszystkie sklepy);
	 *  
	 * )
	 *//*
	public function sendQueue(array $params = array()) {
		if (!isset($params['limit']) && $params['limit']) {
			$limit = '';
		} else {
			$limit = ' LIMIT '.(int)$params['limit'];
		}
		$cond = array(); // tablica z warunkami do zapytania
		if (isset($params['shops_id']) && $params['shops_id']) {
			$cond[] = 'shops_id = '.(int)$params['shops_id'];
		}
		$cond[] = 'sms_status = 0';
		$query = 'SELECT * FROM smsapi_query WHERE '.implode(' AND ', $cond).$limit;
		$sms_queue = M('SmsApiQueue') -> findBySQL($query);
		foreach ($sms_queue as $sms) {
			var_dump($sms);
		}
	}*/
	
	/*
	 * @param params = array (
	 * 	text - treść wiadomości
	 * 	to - numer telefonu odbiorcy
	 * 	shops_id - id sklepu - nadawcy smsa
	 * 	timeout - unix timestamp lub data (Y-m-d h:i:s) - data wygaśnięcia ważności smsa 
	 * )
	 */
	public function addToQueue(array $params = array()) {return 'test';//$params;
	/*
		if (isset($params['to'], $params['text'], $params['shops_id'])) {
			$to = preg_replace('`[^0-9]`', '', $params['to']);
			if ($to) {
				if (isset($params['timeout'])) {
					if (is_numeric($params['timeout'])) {
						$timeout = date('Y-m-d h:i:s', $params['timeout']);
					} else {
						$timeout = $params['timeout'];
					}
				} else {
					$timeout = 'NULL';
				}
				$text = mysql_real_escape_string((string)$params['text']);
				$sms = M('SmsApiQueue') -> create();
				$sms['sms_shop_id'] = (int)$params['shops_id'];
				$sms['sms_phone_number'] = $to;
				$sms['sms_text'] = $text;
				$sms['timeout_timestamp'] = $timeout;
				$sms['sms_status'] = NULL;
				$sms -> save();
				return $sms['smsapi_queue_id'];
			} 
		}*/
	}
	public function getEcoPrice()
	{
		return 1.22;//$this->$eco_price;
	}
}
