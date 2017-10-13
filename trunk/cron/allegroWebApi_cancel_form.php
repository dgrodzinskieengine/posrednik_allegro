<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

include dirname(__FILE__) . "/base_cli.php";

consoleLog("START");

$czas = array();
$czas[0] = time();

$versions = array();
$sessions = array();

// tablica sklepków, w których wykryto błąd logowania i trzeba skipnąć, aby nie zatykać
$skip_shop = array();

$canceled = M('AllegroWebApiCancelledProducts')->getForCron();

consoleLog('Liczba zwrotów prowizji: '.count($canceled));

foreach ($canceled as $c)
{
	$shop_id = $c['shop_id'];
	$user_id = $c['seller_id'];

	$country_id = $c['country_id'];
	$auction_id = $c['auction_id'];

	if ($skip_shop[$shop_id][$user_id])
		continue;

	$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
		if ($shopSettings && (int)$shopSettings[0]['error_counter'] < 5)
		{
			$shopSettings = $shopSettings[0];
		}
		else
		{
			consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
			$skip_shop[$shop_id][$user_id] = true;
			//continue;
		}
		$web_api_code = $shopSettings['web_api_code'];
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
			if ($shopSettings)
			{
				$skip_shop[$shop_id][$user_id] = true;
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();
				
			}
			continue;
		}

		try
		{
			$client = new Core_AllegroWebApiSoapClient(false);

			/// pobieranie wersji WebAPI
			if (!isset($versions[$country_id][$web_api_code]))
			{
				consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");
				$versions[$country_id][$web_api_code]=$client->doQuerySysStatus(array('sysvar'=>1, 'countryId'=>$country_id, 'webapiKey'=>$web_api_code));
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
				$sessions[$shop_id][$user_id][$country_id] = $client->doLogin(array('userLogin'=>$allegroLogin,'userPassword'=>$allegroPassword,'countryCode'=>$country_id,'webapiKey'=>$web_api_code,'localVersion'=>$version->{verKey}));

				$shopSettings['error_counter'] = 0;
				$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
				$shopSettings->save();
			}
			$session = $sessions[$shop_id][$user_id][$country_id];


			$bids_items_count = M('AllegroWebApiBid')->getAuctionAndUserItemsCount($c['auction_id'], $c['buyer_id']);
			if(is_null($bids_items_count) || !($bids_items_count >= 0))
			{
				$bids_items_count = 0;
			}
				
			$form = $client->doSendRefundForm(array('sessionId'=>$session->{sessionHandlePart},'dealId'=>$c['deal_id'],'reasonId'=>$c['reason_id'],'refundQuantity'=>$bids_items_count));

			$acp_id = $c['acp_id'];
			$canceled_product = M('AllegroWebApiCancelledProducts')->find("acp_id = '{$acp_id}'");
			$canceled_product = $canceled_product[0];
			$canceled_product['sent'] = 1;
			$canceled_product['refund_id']=$form->{refundId};
			$canceled_product['update_time'] = date('Y-m-d H:i:s');
			$canceled_product->save();			

		}
		catch(SoapFault $soapFault)
		{
			consoleLog("ERROR: Pobranie licytacji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) {$soapFault->faultcode}: ".$soapFault->faultstring);
// 			consoleLog("FAULTCODE:". $soapFault->faultcode);
			sleep(1);
// 			$skip_shop[$shop_id][$user_id] = true;
/*
			if ($soapFault->faultcode == 'ERR_AUCTION_KILLED')
			{
				$shopAuction['auction_active'] = 0;
				$shopAuction->save();
			}
			if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD' || $soapFault->faultcode == 'ERR_WEBAPI_KEY_INACTIVE')
			{
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();
// 				$skip_shop[$shop_id] = true;
				$skip_shop[$shop_id][$user_id] = true;
			}*/

			if($soapFault->faultcode == 'ERR_NO_SESSION')
				$skip_shop[$shop_id][$user_id] = true;
		}
		
}
$czas[0] = time() - $czas[0];
consoleLog("STOP - cancel forms ({$czas[0]} sekund)");


?>