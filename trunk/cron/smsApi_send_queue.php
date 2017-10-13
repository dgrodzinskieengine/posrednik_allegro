<?php
require_once(dirname(__FILE__)."/../lib/lib.php");
$days = 1; // ilosc dni z jakich wysylane sa sms, starsze smsy sa ignorowane
//$smsApi = Core_SMSApi::getInstance();var_dump($smsApi);die();
$eco_price = Core_SMSApi::getEcoPrice();
//var_dump(M('Ecommerce24hCustomerAccount')->db);
//var_dump(M('SMSApiQueue')->db);
//var_dump(M('Ecommerce24hCustomerAccount')->db);
//die();
//var_dump($eco_price);die();

consoleLog("START - sendSMSQueue");
$czas['sendSMSQueue'] = time();

$count = M('SMSApiQueue')->findBySql("SELECT sms_shop_id, count(sms_shop_id) count FROM smsapi_queue WHERE sms_status IS NULL AND send_after <= NOW() GROUP BY sms_shop_id");
foreach($count as $c)
{
	consoleLog('Wysyłam dla shop_id: '.$c['sms_shop_id']);
	mysql_select_db(DB_PREF);
	$points = 0;
	$shops = M('Shop')->find("shop_id = '{$c['sms_shop_id']}' AND shop_active = 1 AND shop_sms_enabled = 1");
	$shop = $shops[0];
	//jesli sklep nie ma ustawionego numeru telefonu to go olewamy
	if(empty($shop['shop_phone_number']))
	{
		consoleLog('Pomijam sklep shop_id: '. $c['sms_shop_id']. ' BRAK NUMERU TELEFONU ADMINA');
		continue;
	}

	$now = time();
	$date_now = date('Y-m-d H:i:s', $now);
	//var_dump($update_lock);die();

	$eco_price = $shop['shop_eco_sms'];

	//ilosc juz wyslanych smsow dla tego sklepu
	$sent_count = M('SMSApiQueue')->findBySql("SELECT count(smsapi_queue_id) count_sent FROM smsapi_queue WHERE sms_status IS NOT NULL AND sms_shop_id = '{$c['sms_shop_id']}'");
//var_dump(array('juz wyslano' => $sent_count[0]['count_sent']));
	consoleLog('Do tej pory wysłano: '.$sent_count[0]['count_sent']);
	//ilosc pozostalych do wykorzystania darmowych smsow
	$free_to_send = (($shop['shop_free_sms'] - $sent_count[0]['count_sent']) > 0) ? $shop['shop_free_sms'] - $sent_count[0]['count_sent'] : 0;
	//var_dump(array('darmowe smsy' =>$free_to_send));
	consoleLog('Ilosc darmowych smsow: '.$free_to_send);

	mysql_select_db(EC24H_DB_PREF);
	$customer_info = M('Ecommerce24hCustomerShop')->getInfo($shop['shop_name']);
	//$customer_info = M('Ecommerce24hCustomerShop')->getInfo('kwiateo');//var_dump($customer_info);
	
	//jesli nie znaleziono konta sklepu to olewamy ten sklep bo nie mozna naliczyc oplat
	if(!$customer_info)
		continue;

	$customer_id = $customer_info['customers_id'];//var_dump($customer_id);die();
	//$shop_id = $c['sms_shop_id'];
	$shop_id = $customer_info['customers_shops_id'];//var_dump($shop_id);die();
//var_dump($customer_id, $shop_id);continue;
	//stan konta sklepu jesli ujemny to przyjmujemy ze zero (bo potem liczymy z tego limit do zapytania)
	$account_value = M('Ecommerce24hCustomerAccount')->currentAccount($shop_id);//$account_value = 0.09;
	$account_value = ($account_value > 0) ? $account_value : 0;

	//ile smsow platnych moze wyslac ten sklep przy aktualnym stanie konta
	$charged_send_count = floor($account_value / $eco_price);//var_dump(array('platne'=>$charged_send_count));
	consoleLog('Ilosc platnych smsow: '.$charged_send_count);

	//ile smsow moze wyslac sklep wliczajac darmowe smsy
	$shop_limit = (int) ($free_to_send + $charged_send_count);//var_dump($shop_limit,'s_limit');die();
	
	//var_dump(array('limit'=>$shop_limit));

	//priorytet sklepu, jesli >=7 to moze miec saldo ujemne
	$customers_priority = M('Ecommerce24hCustomerAccount')->findBySql("SELECT customers_priority FROM customers WHERE customers_id = ". $customer_info['customers_id']);
	$customers_priority = 7;//(int)$customers_priority[0]['customers_priority'];
	
	mysql_select_db(DB_PREF);

	//jesli sklep moze schodzic na ujemny stan konta bierzemy paczke do 100 smsow, zeby nie zapchac skryptu
	if($customers_priority >=7 || $shop_limit > 100)
		$shop_limit = 100;

	//var_dump("UPDATE smsapi_queue SET cron_lock = '{$date_now}' WHERE sms_status IS NULL AND sms_shop_id = '{$c['sms_shop_id']}' AND (cron_lock IS NULL OR cron_lock < '{$date_now}' - INTERVAL 1 HOUR) LIMIT '{$shop_limit}'");
	//ustawiam blokade na wpisy ktore bede wysylac, zeby nie wyslac przez 2 niezalezne crony tego samego smsa, blokada wazna 1h
	$update_lock = M('SMSApiQueue')->db->execQuery("UPDATE smsapi_queue SET cron_lock = '{$date_now}' WHERE sms_status IS NULL AND sms_shop_id = '{$c['sms_shop_id']}' AND (cron_lock IS NULL OR cron_lock < '{$date_now}' - INTERVAL 1 HOUR) AND send_after <= NOW() LIMIT {$shop_limit}");
	if($customers_priority >=7)
	{
		//sklepy z dopuszczalnym saldem ujemnym

		$send_queue = M('SMSApiQueue')->find("sms_status IS NULL AND sms_shop_id = '{$c['sms_shop_id']}' AND send_after > NOW() - INTERVAL '{$days}' DAY AND cron_lock = '{$date_now}' AND send_after <= NOW()");
		mysql_select_db(DB_PREF);
		//$send_queue = M('Shop')->find("shop_id = '{$c['sms_shop_id']}' AND shop_active = 1");
		//$send_queue = M('SMSApiQueue')->findBySql("SELECT sms_shop_id, count(sms_shop_id) count FROM smsapi_queue WHERE sms_status IS NULL");
		//var_dump($send_queue);
		$smsApi = Core_SMSApi::getInstance();
		foreach ($send_queue as $q)
		{
			consoleLog('Wysyłam smsa '. $q['smsapi_queue_id']);
			$r = $smsApi->sendQueueSMS($q);
			if($r)
			{
				$points+=(int)$r['points'];
				consoleLog(' OK');
			}
			else
			{
				consoleLog('BLAD WYSYLKI SMSA! '. $q['smsapi_queue_id']);
			}
			if($points > $free_to_send && $r)
			{
				//var_dump('charguj sklep');
				consoleLog('Naliczam oplate za smsy (sztuk): '.$r['points']. ' customers_shops_id: '.$shop_id);
				//jesli miesci sie w darmowym pakiecie to nie naliczamy oplaty
				if($points > $free_to_send)
					$smsApi->charcheShopSMS($shop_id, $customer_id, (int)$r['points'], $eco_price);
			}
				
		}
	}
	else
	{
		//pozostale sklepy, przerywamy wysylke po przekroczeniu konta i dajemy komunikat do sklepu jesli pozostaly smsmy do wyslania

		$send_queue = M('SMSApiQueue')->find("sms_status IS NULL AND sms_shop_id = '{$c['sms_shop_id']}' AND send_after > NOW() - INTERVAL '{$days}' DAY AND cron_lock = '{$date_now}' AND send_after <= NOW() LIMIT {$shop_limit}");
		mysql_select_db(DB_PREF);	
		//var_dump($send_queue);
		$smsApi = Core_SMSApi::getInstance();
		foreach ($send_queue as $q)
		{
			consoleLog('Wysyłam smsa '. $q['smsapi_queue_id']);
			$r = $smsApi->sendQueueSMS($q);
			if($r)
			{
				$points+=(int)$r['points'];
				consoleLog(' OK');
			}
			else
			{
				consoleLog('BLAD WYSYLKI SMSA! '. $q['smsapi_queue_id']);
			}
			if($points > $free_to_send && $r)
			{
				//var_dump('charguj sklep');
				consoleLog('Naliczam oplate za smsy (sztuk): '.$r['points']. ' customers_shops_id: '.$shop_id);
				//jesli miesci sie w darmowym pakiecie to nie naliczamy oplaty
				if($points > $free_to_send)
					$smsApi->charcheShopSMS($shop_id, $customer_id, (int)$r['points'], $eco_price);
			}
		}
		mysql_select_db(DB_PREF);

		//jesli sklep ma wiecej smsow do wysylki niz pozwala na to jego stan konta
		if($c['count'] > $free_to_send + $charged_send_count && $shop_limit > 0)
		{
			$smsApi->sendAdminSMS(array('to' => $shop['shop_phone_number'], 'text' => 'Ecommerce24h: stan konta osiagnal 0 i wysylka SMSow zostala wstrzymania do momentu doladowania konta w sklepie.'));
			//var_dump('sms do admina');
			consoleLog('Wysyłam powiadomienie do administratora sklepu '. $c['sms_shop_id'] . ' na numer: '. $shop['shop_phone_number']);
		}
/*
		$queue_total_sent_count = M('SMSApiQueue')->findBySql("SELECT count(smsapi_queue_id) count_sent FROM smsapi_queue WHERE sms_status IS NOT NULL AND sms_shop_id = '{$c['sms_shop_id']}'");
		$queue_left_count = M('SMSApiQueue')->findBySql("SELECT count(smsapi_queue_id) count_sent FROM smsapi_queue WHERE sms_status IS NULL AND sms_shop_id = '{$c['sms_shop_id']}'");
		$q_l_c = (int)$queue_left_count[0]['count_sent'];
		$q_t_s_c = (int)$queue_total_sent_count[0]['count_sent'];
		mysql_select_db(EC24H_DB_PREF);
		$account_value = M('Ecommerce24hCustomerAccount')->currentAccount($c['sms_shop_id']);//$account_value = -0.47;
		$account_value = ($account_value > 0) ? $account_value : 0;
		mysql_select_db(DB_PREF);	
		if($points > 0 &&($q_l_c * $eco_price) > $account_value && $shop['shop_free_sms'] < $q_t_s_c)
		{
			//wyslij sms z ostrzezeniem o przekroczeniu konta
			$smsApi->sendAdminSMS(array('to' => $shop['shop_phone_number'], 'text' => 'Ecommerce24h: stan konta osiagnal 0 i wysylka SMSow zostala wstrzymania do momentu doladowania konta w sklepie.'));
			var_dump('sms do admina');
		}*/
	}
}
//var_dump($count->asArray());

$czas['sendSMSQueue'] = time() - $czas['sendSMSQueue'];
consoleLog("STOP - sendSMSQueue ({$czas['sendSMSQueue']} sekund)");

?>