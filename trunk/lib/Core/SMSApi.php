<?php
define('CORE_SMSAPI_LIB_PATH', dirname(__FILE__).'../../smsapi');

class Core_SMSApi {
	public static $eco_price = 0.07; //cena smsa w pakiecie eco
	public static $instance = null;
	public static function getEcoPrice()
	{
		return self::$eco_price;
	}
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new Core_SMSApi();
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
	private function _sendSMS($params, $queue = NULL) {
		if (isset($params['to'], $params['text'])) {
			try {
				$sms = $this -> apiClient -> actionSend();
				//$sms->paramsOther(array('eco' => '1', 'encoding' => 'utf-8'));
				$sms->setEco(true);
				$sms->setUtf8(true);
				$sms->setSkipForeign(true);
				$sms->setNormalize(true);
				$sms -> setTo($params['to']);
				$sms -> setText($params['text']);
				// $sms -> setTest($this -> test_mode);//$sms->uri();var_dump($sms);die();
				$response = $sms -> execute();
				// print_r($response);
				$res = $response->getList();//var_dump($id[0]);// ->getNumber();
				$id = !$res[0]->getError();
				if ($id) {
					$return = array();
					$return['id'] = $res[0]->getId();
					$return['points'] = $res[0]->getPoints();
					$return['status'] = $res[0]->getStatus();
					$return['number'] = $res[0]->getNumber();
					$return['error'] = $res[0]->getError();
					return $return;
				} else {
					return false;
				}
			} catch (Exception $e) {
				return $e->getCode();
			}
		} else {
			return false;
		}
	}

	public function sendAdminSMS($params)
	{
		$eco = true;
		if(isset($params['force']) && $params['force'] == 1)
			$eco = false;
		if (isset($params['to'], $params['text'])) {
			try {
				$sms = $this -> apiClient -> actionSend();
				if($eco)
					$sms->setEco(true);
				$sms->setUtf8(true);
				$sms->setSkipForeign(true);
				//$sms->setUtf8(true);
				$sms->setNormalize(true);
				$sms -> setTo($params['to']);
				$sms -> setText($params['text']);
				$sms -> setTest($this -> test_mode_admin);
				$response = $sms -> execute();
				$id = $response->getList();// ->getNumber();
				$id = !$id[0]->getError();
				//$id = $response->getNumber();
				if ($id) {
					return $id;
				} else {
					return false;
				}
			} catch (Exception $e) {
				return $e->getMessage();
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
	 */
	public function sendQueueSMS($params)
	{
		//var_dump($params->asArray());
		$sms = $this->_sendSMS(array('to' => $params['sms_phone_number'], 'text' => $params['sms_text']));
		//var_dump($sms);
		$status_ok = false;
		if(is_array($sms) && isset($sms['id']))
		{
			$params['sms_smsapi_id'] = $sms['id'];
			$params['sms_status'] = 0;
			$status_ok = true;
		}
		elseif($sms === false)
		{
			$params['sms_status'] = 666;
			$status_ok = false;
		}
		elseif(is_array($sms) && isset($sms['error']) &&!empty($sms['error']))
		{
			$params['sms_status'] = (int)$sms['error'];
			$status_ok = false;
		}
		else
		{
			$params['sms_status'] = (int)$sms;
			$status_ok = false;
		}
		//$params['sms_status'] = 1;
		$params['points'] = (float)$sms['points'];
		$params->save();
		if($status_ok)
			return $sms;
		else
		return $status_ok;
	}

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
		$sms_queue = M('SMSApiQueue') -> findBySQL($query);
		foreach ($sms_queue as $sms) {
			var_dump($sms);
		}
	}
	
	/*
	 * @param params = array (
	 * 	text - treść wiadomości
	 * 	to - numer telefonu odbiorcy
	 * 	shops_id - id sklepu - nadawcy smsa
	 * 	timeout - unix timestamp lub data (Y-m-d h:i:s) - data wygaśnięcia ważności smsa 
	 * )
	 */
	public function addToQueue(array $params = array()) {
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
				$sms = M('SMSApiQueue') -> create();
				$sms['shops_id'] = (int)$params['shops_id'];
				$sms['sms_phone_number'] = $to;
				$sms['sms_text'] = $text;
				$sms['timeout_timestamp'] = $timeout;
				$sms['sms_status'] = NULL;
				$sms -> save();
				return $sms['smsapi_queue_id'];
			} 
		}
	}

	public function charcheShopSMS($shop_id, $customer_id, $points, $price)
	{
//		mysql_select_db(EC24H_DB_PREF);
//		$date = date('Y-m-d H:i:s', time());
//		$day = substr($date, 0, 10);
//		$month = substr($date, 0, 7);
//		$current_account = M('Ecommerce24hCustomerAccount')->findOrCreate("customers_shops_id = {$shop_id} AND customers_account_day = '". date("Y-m-d", time()) ."' AND customers_account_operation_description = 'Wysyłka sms przez smsApi.pl'");
//		$current_account = $current_account[0];
//		$current_account['customers_id'] = $customer_id;
//		$current_account['customers_shops_id'] = $shop_id;
//		$current_account['customers_account_day'] = $day;
//		$current_account['customers_account_month'] = $month;
//		$current_account['customers_account_operation_amount'] = $current_account['customers_account_operation_amount'] - ($points * $price);
//		$current_account['customers_account_operation_description'] = 'Wysyłka sms przez smsApi.pl';
//		$current_account->save();

//		mysql_select_db(DB_PREF);
	}
}
