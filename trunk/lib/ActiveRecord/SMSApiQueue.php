<?php
class ActiveRecord_SMSApiQueue extends Core_ActiveRecord
{
	public $tableName = "smsapi_queue";
	public $primaryKey = "smsapi_queue_id";

	function __construct($data = array()) {
$this->db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_PREF);parent::__construct($data);
//mysql_select_db(DB_PREF);
	}

	public function addToQueue($params = array())
	{
		if (isset($params['to'], $params['text'], $params['shops_id'])) {
			$to = preg_replace('`[^0-9]`', '', $params['to']);
			if ($to) {
				if (isset($params['timeout'])) {
					if (is_numeric($params['timeout'])) {
						$timeout = date('Y-m-d h:i:s', $params['timeout']);
					} else {
						$timeout = NULL;//$params['timeout'];
					}
				} else {
					$timeout = NULL;
				}
				
				//$text = mysql_real_escape_string((string)$params['text']);
				$text = (string)$params['text'];

				$shopId = (int)$params['shops_id'];

				/** @var  $shopObj ActiveRecord_Shop*/
				$shopObj = M('Shop')->first($shopId);

				if($shopObj && $shopObj->isSmsServiceActive()) {
					$sms = $this->create();
					$sms['sms_shop_id'] = $shopId;
					$sms['sms_phone_number'] = (string)$to;
					$sms['sms_text'] = $text;
					if($timeout)
						$sms['timeout_timestamp'] = $timeout;

					if(isset($params['send_after'])) {
						$sms['send_after'] = $params['send_after'];
					} else {
						$sms['send_after'] = date('Y-m-d h:i', time());
					}
					//$sms['sms_status'] = NULL;
					$sms -> save();
					return $sms['smsapi_queue_id'];
				}
			}
		}

		return false;
	}
}
?>