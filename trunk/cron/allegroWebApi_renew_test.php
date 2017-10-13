<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

/**
	Etapy występujące w procesie:
	1. Kończenie aukcji przed czasem
	2. Wznawianie aukcji
*/

$mailer = new Core_Mailer();

$versions = array();
$sessions = array();

$skip_shop = array();


consoleLog("START");

consoleLog("START - kasowanie starych wpisów w renew");

$renews = M('AllegroWebApiShopAuctionRenew')->db->getAssoc("SELECT asar_id, shop_id, asar_renew, asar_renew_queue, asar_renew_repeats, insert_timestamp FROM allegro_shop_auction_renew ORDER BY asar_id;");
foreach($renews as $renew)
{
	$shop_active = (int)M('AllegroWebApiShopAuctionRenew')->db->getValue("SELECT shop_active FROM shop WHERE shop_id = '{$renew['shop_id']}';");
	if (strtotime($renew['insert_timestamp']) < time() - 60*60*24*120 || $shop_active == 0 || ($renew['asar_renew'] == 0 && $renew['asar_renew_queue'] == 0))
	{
		$sql = "DELETE FROM allegro_shop_auction_renew WHERE asar_id = '{$renew['asar_id']}';";
		consoleLog($sql);
		M('AllegroWebApiShopAuctionRenew')->db->execQuery($sql);
	}
}

consoleLog("STOP - kasowanie starych wpisów w renew");


consoleLog("START - kończenie aukcji przed czasem");

/// 1. Kończenie aukcji przed czasem
$aukcjeDoZakonczenia = M('ShopProductFinish')->find("spf_used = 0", array("order" => "shop_id, product_id, kit_id"));
foreach($aukcjeDoZakonczenia as $aukcjaDoZakonczenia)
{
	$shopAuctions = $aukcjaDoZakonczenia->AllegroWebApiShopAuctions();

	$shop_id = $aukcjaDoZakonczenia['shop_id'];

	$shopsAR = M('Shop')->find($shop_id);
	if ($shopsAR)
	{
		if ($shopsAR[0]['shop_active'] == 0)
			continue;
	}

	foreach($shopAuctions as $shopAuction)
	{
		$user_id = $shopAuction['user_id'];

		if ($skip_shop[$shop_id][$user_id])
			continue;

		$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
		if ($shopSettings)
			$shopSettings = $shopSettings[0];

		$web_api_code = $shopSettings['web_api_code'];

// 		$shopAuctionRenews = $shopAuction->AllegroWebApiShopAuctionRenew;
// #		print_r($shopAuctionRenews);
// 		if($shopAuctionRenews)
// 		{
// 	#		foreach($shopAuctionRenews as $shopAuctionRenew)
// 	#		{
// 				$shopAuctionRenews['asar_renew_queue'] = 0;
// 				$shopAuctionRenews['asar_renew'] = 0;
// 				$shopAuctionRenews->save();
// 	#		}
// 		}
// 		consoleLog("SELECT auction_id FROM allegro_shop_auction WHERE shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND product_id = '{$aukcjaDoZakonczenia['product_id']}'");
		$auction_ids = M('AllegroWebApiShopAuction')->db->getAssoc("SELECT auction_id FROM allegro_shop_auction WHERE shop_id = '{$shop_id}' AND product_id = '{$aukcjaDoZakonczenia['product_id']}'");
		foreach((array)$auction_ids as $auction_id)
		{
			consoleLog("UPDATE allegro_shop_auction_renew SET asar_renew = 0, asar_renew_queue = 0 WHERE shop_id = '{$shop_id}' AND auction_id = '{$auction_id['auction_id']}';");
			M('AllegroWebApiShopAuctionRenew')->db->execQuery("UPDATE allegro_shop_auction_renew SET asar_renew = 0, asar_renew_queue = 0 WHERE shop_id = '{$shop_id}' AND auction_id = '{$auction_id['auction_id']}';");
		}
// die;
		$country_id = (int)$shopAuction['country_id'];

		$allegroLogin = '';
		$allegroPassword = '';
		if ($country_id == 1)
		{
			$allegroLogin = $shopSettings['login_allegro'];
			$allegroPassword = $shopSettings['password_allegro'];
		}
		if ($country_id == 228)
		{
			$allegroLogin = $shopSettings['login_testwebapi'];
			$allegroPassword = $shopSettings['password_testwebapi'];
		}

		if (trim($allegroLogin) == '' || trim($allegroPassword) == '' || trim($web_api_code) == '')
		{
			consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
			$shopSettings['error_counter'] += 1;
			$shopSettings->save();

			continue;
		}

		try
		{
			$client = new Core_AllegroWebApiSoapClient();

			/// pobieranie wersji WebAPI
			if (!isset($versions[$country_id][$web_api_code]))
			{
				consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");
				$versions[$country_id][$web_api_code] = $client->doQuerySysStatus(1, $country_id, $web_api_code);
			}
			$version = $versions[$country_id][$web_api_code];

			// właściwe logowanie do serwisu
			if (!isset($sessions[$shop_id][$user_id]))
			{
				$sessions[$shop_id][$user_id] = array();
			}
			if (!isset($sessions[$shop_id][$user_id][$country_id]))
			{
				consoleLog("Logowanie do WebApi (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}).");
				$sessions[$shop_id][$user_id][$country_id] = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $web_api_code, $version['ver-key']);

				if ($sessions[$shop_id][$user_id][$country_id]['user-id'] > 0)
				{
					$shopSettings['user_id'] = $user_id = $sessions[$shop_id][$user_id][$country_id]['user-id'];
					$shopSettings['error_counter'] = 0;
					$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
					$shopSettings->save();
				}
			}
			$session = $sessions[$shop_id][$user_id][$country_id];


			consoleLog("Kończenie przed czasem (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$shopAuction['auction_id']}).");
			$client->doFinishItem($session['session-handle-part'], $shopAuction['auction_id']);
		}
		catch(SoapFault $soapFault)
		{
			if ($soapFault->faultcode != 'ERR_INVALID_ITEM_ID' && $soapFault->faultcode != 'ERR_YOU_CANT_CHANGE_ITEM')
				$skip_shop[$shop_id][$user_id] = true;

			consoleLog("ERROR: Kończenie przed czasem (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$shopAuction['auction_id']}) {$soapFault->faultcode}: {$soapFault->faultstring}");
		}
	}

	if (!$skip_shop[$shop_id][$user_id])
	{
		$aukcjaDoZakonczenia['spf_used'] = 1;
		$aukcjaDoZakonczenia->save();
	}
}
consoleLog("STOP - kończenie aukcji przed czasem");

die;
consoleLog("START - wznawianie aukcji");
/// 2. Wznawianie aukcji
$shopAuctionRenews = M('AllegroWebApiShopAuctionRenew')->find('asar_renew_queue = 1 AND asar_renew_repeats <= 3', array('order' => 'update_timestamp'));
// $shopAuctionRenews = M('AllegroWebApiShopAuctionRenew')->find(175757);
// $shopSettings_ = M('AllegroWebApiShopSettings')->find('error_counter < 5 AND get_other_bids = 1 AND shop_id = 646', array('order' => 'shop_id'));
if ($shopAuctionRenews)
foreach($shopAuctionRenews as $shopAuctionRenew)
{
	$shop_id = $shopAuctionRenew['shop_id'];

	$shopAuction = $shopAuctionRenew->AllegroWebApiShopAuction;

	if ($shopAuction)
	{
		$product_id = (int)$shopAuction['product_id'];
		$kit_id = (int)$shopAuction['kit_id'];
		$user_id = (int)$shopAuction['user_id'];
	}

	$shopsAR = M('Shop')->find($shop_id);
	if ($shopsAR)
	{
		if ($shopsAR[0]['shop_active'] == 0)
			continue;
	}
	$nr_konfiguracji = 1;
	$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
	if ($shopSettings)
	{
		$shopSettings = $shopSettings[0];
		$nr_konfiguracji = (int)$shopSettings['nr_konfiguracji'];
	}

	$product_count = 0;
	$for_sale = false;
	$product_status = 0;
	$price = 0;

	try{
		$shop_api = new Core_ShopApi($shopsAR['shop_url'], $shopsAR['shop_name'], $shopsAR['shop_password']);
		$renew_auctions = $shop_api->getAuctionRenew($nr_konfiguracji);
	}

	catch(SoapFault $soapFault)
	{
		$renew_auctions = true;
	}
	if(!$renew_auctions)
	{
		M('AllegroWebApiShopAuctionRenew')->db->execQuery("UPDATE allegro_shop_auction_renew asar INNER JOIN allegro_shop_auction asa ON (asar.auction_id = asa.auction_id)  SET asar_renew = 0, asar_renew_queue = 0 WHERE asar.shop_id = '{$shop_id}' AND asa.user_id = '{$user_id}';");
		continue;
	}

	try{
		$shop_api = new Core_ShopApi($shopsAR['shop_url'], $shopsAR['shop_name'], $shopsAR['shop_password']);
		$prod = $shop_api->getProduct($product_id);
		$for_sale = $prod->{'for_sale'};
		$product_count = $prod->{'quantity'};
		$product_status = $prod->{'status'};
		$price = $prod->{'price'};
	}
	catch(SoapFault $soapFault)
	{
		$for_sale = true;
		if ($soapFault->faultcode == 'data')
		{
			$for_sale = false;
			consoleLog($soapFault->faultstring);
		}
	}

	if ($product_id == 0 || !$for_sale)
	{
		$shopAuctionRenew['update_timestamp'] = date('Y-m-d H:i:s');
		$shopAuctionRenew['asar_renew_repeats'] += 1;
// 		if ($shopAuctionRenew['asar_renew_repeats'] >= 4)
// 			$shopAuctionRenew['asar_renew_queue'] = 0;
		$shopAuctionRenew->save();

		continue;
	}

	$country_id = false;
	$form = unserialize($shopAuctionRenew['asar_dataform']);
	$values = array();
	foreach($form as $i => $formPos)
	{
		if (!isset($form[$i]->{'fvalue-date'}))
			$form[$i]->{'fvalue-date'} = '';

		if (!isset($form[$i]->{'fvalue-rage-int'}))
		{
			$form[$i]->{'fvalue-range-int'} = array(
					'fvalue-range-int-min' => 0,
					'fvalue-range-int-max' => 0
				);
		}

		if (!isset($form[$i]->{'fvalue-range-float'}))
		{
			$form[$i]->{'fvalue-range-float'} = array(
					'fvalue-range-float-min' => 0,
					'fvalue-range-float-max' => 0
				);
		}

		if (!isset($form[$i]->{'fvalue-range-date'}))
		{
			$form[$i]->{'fvalue-range-date'} = array(
					'fvalue-range-date-min' => 0,
					'fvalue-range-date-max' => 0
				);
		}

		switch ($formPos->{'fid'})
		{
			case 1:
				$values['fid:'.$formPos->{'fid'}] = $formPos->{'fvalue-string'};
				break;
			case 2:
				$values['fid:'.$formPos->{'fid'}] = $formPos->{'fvalue-int'};
				break;
			case 3:
				$values['fid:'.$formPos->{'fid'}] = $formPos->{'fvalue-datetime'};
				break;
			case 4:
			case 5:
			case 13:
			case 14:
			case 29:
				$values['fid:'.$formPos->{'fid'}] = $formPos->{'fvalue-int'};
				break;
			case 7:
			case 8:
				$values['fid:'.$formPos->{'fid'}] = $formPos->{'fvalue-float'};
				break;
			case 9:
				$country_id = $formPos->{'fvalue-int'};
				break;
		}
	}

	$can_sell = false;

	try{
		$shop_api = new Core_ShopApi($shopsAR['shop_url'], $shopsAR['shop_name'], $shopsAR['shop_password']);
	
		$can_sell = $shop_api->canSell($values["fid:5"], $product_count, $product_status, $price);
	}
	catch(SoapFault $soapFault)
	{
		$can_sell = true;
		if ($soapFault->faultcode == 'data')
		{
			//$can_sell = false;
			//consoleLog($soapFault->faultstring)
		}
	}

	if (!$can_sell)
	{
		$shopAuctionRenew['update_timestamp'] = date('Y-m-d H:i:s');
		$shopAuctionRenew['asar_renew_repeats'] += 1;
// 		if ($shopAuctionRenew['asar_renew_repeats'] >= 4)
// 			$shopAuctionRenew['asar_renew_queue'] = 0;
		$shopAuctionRenew->save();

		continue;
	}

	$user_id = (int)$shopSettings['user_id'];
	$web_api_code = $shopSettings['web_api_code'];

	if ($skip_shop[$shop_id][$user_id])
		continue;

	$allegroLogin = '';
	$allegroPassword = '';
	if ($country_id == 1)
	{
		$allegroLogin = $shopSettings['login_allegro'];
		$allegroPassword = $shopSettings['password_allegro'];
	}
	if ($country_id == 228)
	{
		$allegroLogin = $shopSettings['login_testwebapi'];
		$allegroPassword = $shopSettings['password_testwebapi'];
	}

	if (trim($allegroLogin) == '' || trim($allegroPassword) == '' || trim($web_api_code) == '')
	{
		print_r($shopAuctionRenew->asArray());
		consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
		$shopSettings['error_counter'] += 1;
		$shopSettings->save();

		continue;
	}

	try
	{
		$client = new Core_AllegroWebApiSoapClient();

		/// pobieranie wersji WebAPI
		if (!isset($versions[$country_id][$web_api_code]))
		{
			consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");
			$versions[$country_id][$web_api_code] = $client->doQuerySysStatus(1, $country_id, $web_api_code);
		}
		$version = $versions[$country_id][$web_api_code];

		// właściwe logowanie do serwisu
		if (!isset($sessions[$shop_id][$user_id]))
		{
			$sessions[$shop_id][$user_id] = array();
		}
		if (!isset($sessions[$shop_id][$user_id][$country_id]))
		{
			consoleLog("Logowanie do WebApi (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}).");
			$sessions[$shop_id][$user_id][$country_id] = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $web_api_code, $version['ver-key']);

			if ($sessions[$shop_id][$user_id][$country_id]['user-id'] > 0)
			{
				$shopSettings['user_id'] = $user_id = $sessions[$shop_id][$user_id][$country_id]['user-id'];

				$shopSettings['error_counter'] = 0;
				$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');

				$shopSettings->save();
			}
		}
		$session = $sessions[$shop_id][$user_id][$country_id];

		consoleLog("Wznowienie aukji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$shopAuctionRenew['auction_id']}).");

		$local = rand();
		$private = 0; // nie prywatn
		
		/// pobranie aktualnego formularza sprzedaży w celu spisania obecnie dostępnych fid'ów
		$Core_WebApiServer = new Core_WebApiServer();
		$sellformBlank = $Core_WebApiServer->getSellform(array("country_id" => $country_id, "category_id" => $values['fid:2']));
// // 				l($sellformBlank);
		$sellform_ids = array();
		foreach($sellformBlank as $sellformBlank_)
			$sellform_ids[$sellformBlank_['sellform_id']] = $sellformBlank_['sellform_id'];

		///jeżeli danego fid'a nie ma już w nowej strukturze dla kategorii Allegro to usuwamy nadmiarowe pole
		foreach($form as $kf => $f)
		{
			if(!isset($sellform_ids[$f->fid]))
				unset($form[$kf]);
		}

		$dataform = serialize($form);
// 		print_r($form);die;
// 		consoleLog("sell", "doNewAuctionExt", array($session['session-handle-part'], $form, $private, $local));
		$item = $client->doNewAuctionExt($session['session-handle-part'], $form, $private, $local);

// 		webApiLog("sell", "doVerifyItem", array($session['session-handle-part'], $local));
		$check = $client->doVerifyItem($session['session-handle-part'], $local);

// 		webApiLog("sell", "doVerifyItem", array($session['session-handle-part']));
		$myData = $client->doGetMyData($session['session-handle-part']);

		$user_id = $myData['user-data']->{'user-id'};
		$user_rating = $myData['user-data']->{'user-rating'};

		if(isset($item) && isset($check) && isset($item['item-id']))
		{
			// zapisanie w bazie Allegro Pośrednika informacji o wystawionych aukcjach
			$shopAuction = M('AllegroWebApiShopAuction')->create();
			$shopAuction['shop_id'] = $shop_id;
			$shopAuction['user_id'] = $user_id;
			$shopAuction['product_id'] = $product_id;
			$shopAuction['kit_id'] = $kit_id;
			$shopAuction['country_id'] = $country_id;
			$shopAuction['auction_id'] = $item['item-id'];
			$shopAuction['auction_name'] = $values["fid:1"];
			$shopAuction['auction_allegro_price'] = strtr($item['item-info'], array(',' => '', 'zł' => '', ' ' => ''));

			$price = strtr($values["fid:7"], ",", ".");
			if ($price <= 0)
				$price = strtr($values["fid:8"], ",", ".");

			$shopAuction['auction_price'] = $price;
			$shopAuction['auction_image_url'] = $image_url;
			$shopAuction['auction_payment'] = $values["fid:14"];
			$shopAuction['auction_transport'] = $values["fid:13"];
			$shopAuction['auction_items'] = $values["fid:5"];

// 			if ((int)$values["fid:3"] < 1000000000)	/// just in case
			$values["fid:3"] = time();

			$shopAuction['date_start'] = date("Y-m-d H:i:s", $values["fid:3"]);
			switch($values["fid:4"]) {
				case 0:
					$shopAuction['date_stop'] = date("Y-m-d H:i:s", $values["fid:3"]+86400*3);
					break;
				case 1:
					$shopAuction['date_stop'] = date("Y-m-d H:i:s", $values["fid:3"]+86400*5);
					break;
				case 2:
					$shopAuction['date_stop'] = date("Y-m-d H:i:s", $values["fid:3"]+86400*7);
					break;
				case 3:
					$shopAuction['date_stop'] = date("Y-m-d H:i:s", $values["fid:3"]+86400*10);
					break;
				case 4:
					$shopAuction['date_stop'] = date("Y-m-d H:i:s", $values["fid:3"]+86400*14);
					break;
			}

			/// bo wtedy sklep i czas aukcji wydłużony do 30 dni
			if ($values["fid:29"] == 1)
				$shopAuction['date_stop'] = date("Y-m-d H:i:s", $values["fid:3"]+86400*30);

			$shopAuction->save();

			M('AllegroWebApiShopAuction')->db->execQuery("UPDATE allegro_shop_auction SET auction_hidden = 1 WHERE shop_id = {$shop_id} AND auction_active = 0 AND product_id = '{$product_id}' AND auction_id <> '{$shopAuction['auction_id']}';");

			/// Tutan zapisywane są dane w związku z RENEW
			$shopAuctionRenew_new = M('AllegroWebApiShopAuctionRenew')->create();
			$shopAuctionRenew_new['shop_id'] = $shop_id;
			$shopAuctionRenew_new['auction_id'] = $item['item-id'];
			$shopAuctionRenew_new['asar_dataform'] = $dataform;
			$shopAuctionRenew_new['asar_counter'] = $shopAuctionRenew['asar_counter'] + 1;
			$shopAuctionRenew_new->save();

			$shopAuctionRenew['asar_renew_repeats'] += 1;
			$shopAuctionRenew['asar_renew_queue'] = 0;
			$shopAuctionRenew['update_timestamp'] = date('Y-m-d H:i:s');
			$shopAuctionRenew['auction_id_new'] = $item['item-id'];
		}
		else
		{
			$shopAuctionRenew['update_timestamp'] = date('Y-m-d H:i:s');
			$shopAuctionRenew['asar_renew_repeats'] += 1;
// 			if ($shopAuctionRenew['asar_renew_repeats'] >= 5)
// 				$shopAuctionRenew['asar_renew_queue'] = 0;
		}

		$shopAuctionRenew->save();
	}
	catch(SoapFault $soapFault)
	{


// 		print_r($soapFault);

// 		if ($soapFault->faultcode == 'ERR_USER_PASSWD')
// 		{
			$shopAuctionRenew['asar_renew_repeats'] += 1;
			$shopAuctionRenew->save();
			
			$ignored_codes = array(
				'ERR_INVALID_VALUE_IN_ATTRIB_FIELD' => true
			);

			if(!isset($ignored_codes[$soapFault->faultcode]))
				$skip_shop[$shop_id][$user_id] = true;
// 		}

		consoleLog("ERROR: Wznowienie aukji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$shopAuctionRenew['auction_id']}, error_counter: {$shopAuctionRenew['asar_renew_repeats']}) ".$soapFault->faultstring);
	}

	M('AllegroWebApiShopAuction')->db->execQuery("UPDATE allegro_shop_auction_renew SET asar_renew = 0, asar_renew_queue = 0 WHERE shop_id = '{$shop_id}' AND asar_renew_queue = 1 AND asar_renew_repeats > 3");
}
consoleLog("STOP - wznawianie aukcji");

consoleLog("STOP");
