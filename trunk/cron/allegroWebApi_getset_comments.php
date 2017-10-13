<?php

// Kilka razy na dobę (może być co godzinę)

require_once(dirname(__FILE__)."/../lib/lib.php");

$zalogowany_shop_id = '';
$versions = array();
$sessions = array();

$skip_shop = array();

/// jedziemy po aktywnych licytacjach bez komentarzy i wystawiamy komentarze
$bids = M('AllegroWebApiBid')->find("fb_recvd = 1 AND fb_recvd_type = 1 AND fb_gave = 0", array("order" => "shop_id, country_id, auction_id, user_id"));
$auctionCountr = count($bids);
$counter = 1;
$zalogowany_shop_id = '';

foreach($bids as $bid)
{
	//print_r($auction->asArray());
	$shop_id = $bid['shop_id'];
	$country_id = $bid['country_id'];
	$auction_id = $bid['auction_id'];
	$bid_user_id = $bid['user_id'];

	$shopsAR = M('Shop')->find($shop_id);
	if ($shopsAR)
	{
		if ($shopsAR[0]['shop_active'] == 0)
			continue;
	}

	$shopAuction = $bid->AllegroWebApiShopAuction;

	$user_id = (int)$shopAuction['user_id'];

	$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
	if ($shopSettings) {
		$shopSettings = $shopSettings[0];
	} else {
		consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Brak wpisu w allegro_shop_settings.");
		continue;
	}

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

	if (trim($allegroLogin) == '' || trim($allegroPassword) == '' || trim($web_api_code) == '')
	{
		consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
		$shopSettings['error_counter'] += 1;
		$shopSettings->save();

		continue;
	}

	$comment = trim($shopSettings['comment']);
	if (strlen($comment) < 8)
		continue;

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

		/// wystawianie komentarzy
		consoleLog("Wystawianie kometnarza (shop_id: {$shop_id}, user_id: {$user_id}, country_id: $country_id, auction_id: $auction_id, user_id: $bid_user_id) ($counter z $auctionCountr).");
		$t =  array(
				'session-handle' => $session['session-handle-part'],
				'fe-item-id' => $auction_id,
				'fe-use-comment-template' => 0,
				'fe-to-user-id' => $bid_user_id,
				'fe-comment' => $comment,
				'fe-comment-type' => 'POS',
				'fe-op' => 2);
		if ((int)$client->__soapCall("doFeedback", $t)
			> 0)
		{
			$bid['fb_gave'] = 1;
			$bid->save();
		}
	}
	catch(SoapFault $soapFault)
	{
		consoleLog("Wystawianie kometnarza ERROR: {$soapFault->faultcode} (shop_id: {$shop_id}, country_id: {$country_id}) ".$soapFault->faultstring);
// print_r($soapFault);
		if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD')
		{
			$shopSettings['error_counter'] += 1;
			$shopSettings->save();

			$skip_shop[$shop_id][$user_id] = true;
		}
		if ($soapFault->faultcode == 'ERR_MANY_FEEDBACKS' || $soapFault->faultcode == 'ERR_AUCTION_KILLED')
		{
			$bid['fb_gave'] = 1;
			$bid->save();
		}
	}

	$counter++;
}



/// jedziemy po aktywnych licytacjach bez komentarzy i odbieramy komentarze
$auctions = M('AllegroWebApiBid')->getShopIdCountryIdAuctionIdWithoutFeedbackGrouped();
$auctionCountr = count($auctions);
$counter = 1;

foreach($auctions as $auction)
{
	$shop_id = $auction['shop_id'];
	$country_id = $auction['country_id'];
	$auction_id = $auction['auction_id'];

	$shopAuction = $auction->AllegroWebApiShopAuction;

	$shopsAR = M('Shop')->find($shop_id);
	if ($shopsAR)
	{
		if ($shopsAR[0]['shop_active'] == 0)
			continue;
	}

	$user_id = (int)$shopAuction['user_id'];

	$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
	if ($shopSettings)
		$shopSettings = $shopSettings[0];

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

		/// pobranie komentarzy otrzymanych
		$offset = 0;
		$feedbackRecvd = 0;
		while($offset == 0 || count($feedbacksRecvd) == 25)
		{
			consoleLog("Pobranie komentarzy otrzymanych (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$auction_id}) ($counter z $auctionCountr).");
			$feedbacksRecvd = $client->doMyFeedback2($session['session-handle-part'], "fb_recvd",  $offset, 0, array($auction_id));

			if (is_array($feedbacksRecvd))
			{
				foreach($feedbacksRecvd as $feedbackRecvd)
				{
					$feedback = $feedbackRecvd->{'feedback-array'};
					$bids = M('AllegroWebApiBid')->find(sql(array("
							auction_id = %auction_id
							AND user_id = %user_id
						",
						"auction_id" => $auction_id,
						"user_id" => $feedback[0]
						)));
// 					print_r($bids[0]->asArray());
// 					print_r($feedback);
					if ($bids)
					{
						$bids[0]['fb_recvd'] = 1;
						$bids[0]['fb_recvd_type'] = $feedback[3];
						$bids[0]->save();
					}
				}
			}
			$offset++;
		}
	}
	catch(SoapFault $soapFault)
	{
		consoleLog("Pobieranie komentarzy otrzymanych ERROR: {$soapFault->faultcode} (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$auction_id}) ".$soapFault->faultstring);

		if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED')
		{
			$shopSettings['error_counter'] += 1;
			$shopSettings->save();

			$skip_shop[$shop_id][$user_id] = true;
		}
	}

	try
	{
		/// pobranie komentarzy wystawionych
		$offset = 0;
		while($offset == 0 || count($feedbacksGave) == 25)
		{
			consoleLog("Pobranie komentarzy wystawionych (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$auction_id}) ($counter z $auctionCountr).");
			$feedbacksGave = $client->doMyFeedback2($session['session-handle-part'], "fb_gave",  $offset, 0, array($auction_id));
			if (is_array($feedbacksGave))
			{
				foreach($feedbacksGave as $feedbackGave)
				{
					$feedback = $feedbackGave->{'feedback-array'};
					$bids = M('AllegroWebApiBid')->find(sql(array("
							auction_id = %auction_id
							AND user_id = %user_id
						",
						"auction_id" => $auction_id,
						"user_id" => $feedback[1]
						)));
					if ($bids)
					{
						$bids[0]['fb_gave'] = 1;
						$bids[0]->save();
					}
				}
			}
			$offset++;
		}
	}
	catch(SoapFault $soapFault)
	{
		consoleLog("Pobieranie komentarzy wystawionych ERROR: {$soapFault->faultcode} (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$auction_id}) {$soapFault->faultstring}");

		if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED')
		{
			$shopSettings['error_counter'] += 1;
			$shopSettings->save();

			$skip_shop[$shop_id][$user_id] = true;
		}
	}

	$counter++;
}

