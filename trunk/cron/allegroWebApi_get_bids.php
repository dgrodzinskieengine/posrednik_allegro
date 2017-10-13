<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

/**
	Etapy występujące w procesie:
	1. Wyłapywanie wszystkich aukcji nie wystawiony bezpośrednio ze sklepów
	2. Jedziemy po aukcjach, aby pobrać dla nich wpisane FT i FP, bo te dane później się moga przydać
	3. Jedziemy po aktywnych aukcjach, aby pobrać licytacje
	4. Jedziemy po aukcjach, w których pojawiły się jakieś nowe oferty od ostatniego pobierania, aby pobrać dane o licytujących
	5. Pobieranie nowych PzA
	6. Powiadomienie sklepów, że mają co ściągać
*/


$validation = array(
	"--findUFO" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Wyłapywanie wszystkich aukcji nie wystawiony bezpośrednio ze sklepów"
				),
	"--getFTiFP" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Jedziemy po aukcjach, aby pobrać dla nich wpisane FT i FP"
				),
	"--getBids" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Jedziemy po aktywnych aukcjach, aby pobrać licytacje + Jedziemy po aukcjach, w których pojawiły się jakieś nowe oferty od ostatniego pobierania, aby pobrać dane o licytujących"
				),
	"--getPzA" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Pobieranie nowych PzA + Powiadomienie sklepów, że mają co ściągać"
				),
	"--getPayment" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Pobiera fizyczne płatności dokonywane przez PzA"
				),
	"--shopNotice" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Wymusza w sklepach pobranie zamwień"
				),
	"--getMissingBuyersInfo" => array(
							"required" => false,
							"type" => "boolean",
							"description" => "Wymusza pobranie brakujących informacji o kontaktach"
				),
	"--all" => array(
					"required" => false,
					"type" => "boolean",
					"description" => "Wszystkie powyższe opcje razem."
				),
	"--ufoAndMissingBuyers" => array(
					"required" => false,
					"type" => "boolean",
					"description" => "Uruchamia findUfo oraz getMissingBuyersInfo dla osobnego watku"
				),
	"--shopId" => array(
					"required" => false,
					"type" => "integer",
					"description" => "Wywołanie dla konkretnego shop.shop_id"
				),
	"--checkEndingDateOfActiveAuctions" => array(
					"required" => false,
					"type" => "boolean",
					"description" => "Weryfikacja daty zakończenia dla trwających aukcji. Mechanizm dodany pod kątem aukcji trwających do wyczerpania zapasów"
				),
			);
include dirname(__FILE__) . "/base_cli.php";

consoleLog("START");

$czas = array();
$czas[0] = time();


$mailer = new Core_Mailer();

$versions = array();
$sessions = array();

// tablica sklepków, w których wykryto błąd logowania i trzeba skipnąć, aby nie zatykać
$skip_shop = array();


/// Pobiera fizyczne płatności dokonywane przez PzA
if ($all || $getPayment)
{
	consoleLog("START - getPayment");
	$czas['getPayment'] = time();
consoleLog("Memory usage: ".memory_get_usage(true));
	$condition = 'error_counter < 5';
	if($shopId)
	{
		$condition .= " AND shop_id = {$shopId}";
	}

	$shopSettings_ = M('AllegroWebApiShopSettings')->find('error_counter < 5', array('order' => 'shop_id'));
	if ($shopSettings_)
	foreach($shopSettings_ as $shopSettings)
	{
		if(!$shopSettings['user_id']) {
			consoleLog("Brak user_id dla shop_id: {$shopSettings['shop_id']}, login: {$shopSettings['login_allegro']}");
			continue;
		}

		$shop_id = $shopSettings['shop_id'];
		$user_id = $shopSettings['user_id'];


		$country_id = 1;//$shop['country_id'];

		$shopsAR = M('Shop')->find($shop_id);
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;
		}

		if ($skip_shop[$shop_id][$user_id])
			continue;

		$web_api_code = $shopSettings['web_api_code'];

		$allegroLogin = '';
		$allegroPassword = '';
		if ($country_id == 1)
		{
			$allegroLogin = $shopSettings['login_allegro'];
			$allegroPassword = $shopSettings['password_allegro'];
		}

		if (trim($allegroLogin) == '' || trim($allegroPassword) == '' || trim($web_api_code) == '')
		{
			consoleLog("ERROR (shop_id: {$shop_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
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

			$offset = 0;

			$item_ids = array();
			do {
// 				print_r($session);die;
				$dogetmyincomingpayments_request = array(
						'session-handle' => $session['session-handle-part'],
						'buyer-id' => 0,
						'item-id' => 0,
						'trans-recv-date-from' => time()-60*60*24*5,
						'trans-recv-date-to' => 0,
						'trans-page-limit' => 0,
						'trans-offset' => $offset
					);

				consoleLog("Pobieranie informacji o płatnościach (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, offset: $offset)");
				$payments = $client->doGetMyIncomingPayments($session['session-handle-part'], 0, 0, time()-60*60*24*2, 0, 0, $offset);
consoleLog("Memory usage (incomingPayments loaded: ".memory_get_usage(true));
				if ($payments && count($payments) > 0)
				{
					foreach($payments as $payment)
					{
						$postbuyform_id = (int)$payment->{'pay-trans-id'};
						$postbuyformpayment_type = $payment->{'pay-trans-type'};
						$postbuyformpayment_status = $payment->{'pay-trans-status'};
						$postbuyformpayment_amount = (float)$payment->{'pay-trans-amount'};
						$postbuyformpayment_recive_date = date("Y-m-d H:i:s", $payment->{'pay-trans-recv-date'});

						/// tak, takie dziwadła też się zdażają, że ID PzA się zmienia pomiędzy wypełnieniem formularza i dokonaniem płatności
//						$postbuyform_id = (int)M('AllegroWebApiPostbuyformdata')->db->getField("SELECT postbuyform_id FROM allegro_postbuyformdata WHERE postbuyform_id = '{$postbuyform_id}'");
                        $postbuyform_id = (int)M('AllegroWebApiPostbuyformdata')->db->getField("SELECT postbuyform_id FROM allegro_postbuyformdata WHERE postbuyform_id = '{$postbuyform_id}' AND shop_id ={$shop_id} AND user_id = {$user_id}");
                        $ufoPostbuyform_id=0;

                        if($postbuyform_id==0)
                        {
                            $ufoPostbuyform_id=(int)$payment->{'pay-trans-id'};
                            //Sprawdzamy czy id transakcji nie jest doublem
                            $postbuyform_id = (int)M('AllegroWebApiPostbuyformdata')->db->getField("SELECT postbuyform_id FROM allegro_postbuyformdata WHERE postbuyform_id_double = '{$ufoPostbuyform_id}'");
                        }



                        if($postbuyform_id==0)
                        {
                            $ufoPostbuyform_id=(int)$payment->{'pay-trans-id'};

                            $_paymentsAR=M('AllegroWebApiPostbuyformpayment')->find("used_postbuyform_id = {$ufoPostbuyform_id}");
                            if(!$_paymentsAR||count($payment->{'pay-trans-details'}) > 0)
                            {
                                //$payment->{'pay-trans-details'} Tablica struktur zawierających informacje dot. ofert składających się na wpłatę łączną.
                                if ($postbuyform_id == 0 && count($payment->{'pay-trans-details'}) > 0)
                                {
                                    foreach($payment->{'pay-trans-details'} as $paymentDetail)
                                    {
                                        $postbuyform_id = (int)M('AllegroWebApiPostbuyformdata')->db->getField("SELECT pbfd.postbuyform_id FROM allegro_postbuyformitem pbfi LEFT JOIN allegro_postbuyformdata pbfd USING(postbuyform_id) WHERE pbfi.postbuyformit_auction_id = '".$paymentDetail->{'pay-trans-details-it-id'}."' AND pbfd.postbuyform_buyer_id = '".$payment->{'pay-trans-buyer-id'}."';");

                                        if ($postbuyform_id > 0)
                                        {
                                            $postbuyformpayment_amount = (float)$payment->{'pay-trans-details-price'};
                                            break;
                                        }
                                    }
                                }

                                if ($postbuyform_id == 0 && count($payment->{'pay-trans-details'}) == 0)
                                {
                                    $postbuyform_id = (int)M('AllegroWebApiPostbuyformdata')->db->getField("SELECT pbfd.postbuyform_id FROM allegro_postbuyformitem pbfi LEFT JOIN allegro_postbuyformdata pbfd USING(postbuyform_id) WHERE pbfi.postbuyformit_auction_id = '".$payment->{'pay-trans-it-id'}."' AND pbfd.postbuyform_buyer_id = '".$payment->{'pay-trans-buyer-id'}."';");
                                }
                            }
                        }


						if ($postbuyform_id > 0)
						{
							$paymentsAR = M('AllegroWebApiPostbuyformpayment')->find("postbuyform_id = '{$postbuyform_id}' AND postbuyformpayment_status = '{$postbuyformpayment_status}' AND postbuyformpayment_recive_date = '{$postbuyformpayment_recive_date}'");
							if (!$paymentsAR)
							{
								$paymentAR = M('AllegroWebApiPostbuyformpayment')->create();
								$paymentAR['postbuyform_id'] = $postbuyform_id;
								$paymentAR['postbuyformpayment_type'] = $postbuyformpayment_type;
								$paymentAR['postbuyformpayment_status'] = $postbuyformpayment_status;
								$paymentAR['postbuyformpayment_amount'] = $postbuyformpayment_amount;
								$paymentAR['postbuyformpayment_recive_date'] = $postbuyformpayment_recive_date;
                                $paymentAR['used_postbuyform_id']=$ufoPostbuyform_id;
								$paymentAR->save();

							}
						}
					}
				}

				$offset++;
			}
			while($payments && count($payments) == 25);
		}
		catch(SoapFault $soapFault)
		{
			consoleLog("ERROR: Pobieranie informacji o płatnościach (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) ".$soapFault->faultcode." : ".$soapFault->faultstring);
			usleep(10000);


// 			$do = "pawel@walaszek.pl";
// 			$temat = "Posrednik Allegro";
// 			$tresc = "ERROR: Pobranie informacji o zaginionych aukcji (shop_id: {$shop_id}, shop: ".M('Shop')->getShopName($shop_id).", country_id: {$country_id})\n\n".$soapFault->faultcode."\n".$soapFault->faultstring;
// 			mail($do, $temat, $tresc);
//
//
			if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED')
			{
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();

				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
			}
		}
	}
consoleLog("Memory usage (end getPayment): ".memory_get_usage(true));
	$czas['getPayment'] = time() - $czas['getPayment'];
	consoleLog("STOP - getPayment ({$czas['getPayment']} sekund)");
}

/// Wyłapywanie wszystkich aukcji nie wystawiony bezpośrednio ze sklepów
/// a przy okazji licytacji, które się w międzyczasie pojawiły
if ($findUFO || $ufoAndMissingBuyers)
{
	consoleLog("START - findUFO");
	$czas['findUFO'] = time();

	$condition = 'error_counter < 5  AND get_other_bids = 1';
	if($shopId)
	{
		$condition .= " AND shop_id = {$shopId}";
	}

	// $condition = 'shop_id = 9'; //COMMENT

	$shopSettings_ = M('AllegroWebApiShopSettings')->find($condition, array('order' => 'shop_id'));
	// $shopSettings_ = M('AllegroWebApiShopSettings')->find('error_counter < 5 AND get_other_bids = 1 AND shop_id = 646', array('order' => 'shop_id'));
	if ($shopSettings_)
	foreach($shopSettings_ as $shopSettings)
	{
		$shop_id = $shopSettings['shop_id'];
		$country_id = 1;//$shop['country_id'];
		$user_id = (int)$shopSettings['user_id'];

// 		if ($shop_id != 802)
// 			continue;

		if ($skip_shop[$shop_id][$user_id])
			continue;

		$shopsAR = M('Shop')->find($shop_id);
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;
		}

		$web_api_code = $shopSettings['web_api_code'];

		$allegroLogin = '';
		$allegroPassword = '';
		if ($country_id == 1)
		{
			$allegroLogin = $shopSettings['login_allegro'];
			$allegroPassword = $shopSettings['password_allegro'];
		}

		if (trim($allegroLogin) == '' || trim($allegroPassword) == '' || trim($web_api_code) == '')
		{
			consoleLog("ERROR (shop_id: {$shop_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
			$shopSettings['error_counter'] += 1;
			$shopSettings->save(); //UNCOMMENT

			continue;
		}

		try
		{
			$client = new Core_AllegroWebApiSoapClient(false, false);

			/// pobieranie wersji WebAPI
			if (!isset($versions[$country_id][$web_api_code]))
			{
				consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");

				$doQuerySysStatus_request = array(
					'sysvar' => 1,
				   	'countryId' => $country_id,
				   	'webapiKey' => $web_api_code
			   	);

				$versions[$country_id][$web_api_code] = $client->doQuerySysStatus($doQuerySysStatus_request);
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

				$doLogin_request = array(
				   'userLogin' => $allegroLogin,
				   'userPassword' => $allegroPassword,
				   'countryCode' => $country_id,
				   'webapiKey' => $web_api_code,
				   'localVersion' => $version->{'verKey'}
				);

				$sessions[$shop_id][$user_id][$country_id] = $client->doLogin($doLogin_request);

				if ($sessions[$shop_id][$user_id][$country_id]->{'user-id'} > 0)
				{
					$shopSettings['user_id'] = $user_id = $sessions[$shop_id][$user_id][$country_id]->{'user-id'};
					$shopSettings['error_counter'] = 0;
					$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
					$shopSettings->save(); //UNCOMMENT
				}
			}
			$session = $sessions[$shop_id][$user_id][$country_id];

			$offset = 0;
			$result_size = 50;

			$item_ids = array();
			do {
				consoleLog("Poszukiwanie aukcji wystawionych spoza sklepu (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}).");

				$doGetItemsList_request = array(
				   'webapiKey' => $web_api_code,
				   'countryId' => $country_id,
				   'filterOptions' => array(
		     			array(
			      			'filterId' => 'userId',
			      			'filterValueId' => array($user_id)
	      				)
      				),
				   'resultOffset' => $offset,
				   'resultSize' => $result_size
				);

				$userItems = $client->doGetItemsList($doGetItemsList_request);

				$time_now = time();
	 	//		print_r($userItems);var_dump($userItems->it-time-left);
				if(empty($userItems->{'itemsList'}->{'item'})) {
					continue;
				}
				
				foreach($userItems->{'itemsList'}->{'item'} as $userItem)
				{
					$auction_id = $userItem->{'itemId'};
					$item_ids[$auction_id] = $userItem;
					
					$photo = '';
					if(!empty($userItem->{'photosInfo'}->{'item'})) {
						foreach($userItem->{'photosInfo'}->{'item'} as $photoInfo) {
							if($photoInfo->{'photoSize'} == 'large') {
								$photo = $photoInfo->{'photoUrl'};	
							}
						}
					}
//print_r(array($photo, $userItem->{'photosInfo'}));die;

					$auction = M('AllegroWebApiShopAuction')->first('auction_id = '.$auction_id);
					if($auction)
						{
							if(!$item->{'endingTime'}) {
								$date_stop = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);
							} else {
								$date_stop = date('Y-m-d H:i:s', strtotime($item->{'endingTime'}));	
							}
							
							if(empty($auction['auction_image_url'])) {
								$auction['auction_image_url'] = $photo;
							}							
							$auction['date_stop'] = $date_stop;
							$auction->save(); //UNCOMMENT
					
						}

					$liczba_ofert = M('AllegroWebApiBid')->db->getValue("SELECT count(ab_quantity) FROM allegro_bid WHERE auction_id = '{$auction_id}';");

					if ($liczba_ofert != $userItem->{'biddersCount'})
					{
						consoleLog("Wg różnicy w liczbie ofert (shop_id: {$shop_id}, user_id: {$user_id}, auction_id: {$auction_id})");
						$allegro_shop_auction_do_analizy = M('AllegroWebApiShopAuctionDoAnalizy')->findOrCreate("shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND auction_id = '{$auction_id}'");
						$allegro_shop_auction_do_analizy[0]['shop_id'] = $shop_id;
						$allegro_shop_auction_do_analizy[0]['user_id'] = $user_id;
						$allegro_shop_auction_do_analizy[0]['auction_id'] = $auction_id;
						$allegro_shop_auction_do_analizy[0]->save(); //UNCOMMENT
					}

					$offset++;
				}
	// 			print_r($userItems);
				


			} while(count($userItems->{'itemsList'}->{'item'}) == $result_size);

			/// teraz trzeba wypierniczyć te aukcje, które mamy w bazie
			foreach($item_ids as $item_id => $item)
			{
				$auctions = M('AllegroWebApiShopAuction')->findByAuctionId((int)$item_id);
				if ($auctions) {
					unset($item_ids[$item_id]); //UNCOMMENT
				}
			}

			/// jeżeli pozostaną jakieś niezidentyfikowane aukcje
			if ($item_ids)
			{
				foreach($item_ids as $item_id => $item)
				{
	// 				$auctionsError = M('AllegroWebApiShopAuctionSellError')->find(sql(array(
	// 						'shop_id = %shop_id AND country_id = %country_id AND auction_name = %auction_name AND asa_id IS NULL',
	// 						'shop_id' => $shop_id,
	// 						'country_id' => $country_id,
	// 						'auction_name' => $item->{'it-name'}
	// 					)), array('order' => 'asase_id desc', 'limit' => 1));
	// 				if ($auctionsError)
	// 				{
	// 					$auctionError = $auctionsError[0];

						/// zapisanie odnalezionej aukcji w bazie
						$shopAuction = M('AllegroWebApiShopAuction')->create();
						$shopAuction['shop_id'] = $shop_id;
						$shopAuction['user_id'] = $user_id;

						///poniższy kawałek zakomentowany ponieważ powodował niezawsze prawidłowe matchowanie z produktami w sklepie
						// $shopAuction['product_id'] = (int)M('AllegroWebApiShopAuction')->db->getField(sql(array("SELECT product_id FROM allegro_shop_auction WHERE shop_id = '{$shop_id}' AND auction_name = %auction_name;", 'auction_name' => $item->{'it-name'})));
						// if ($shopAuction['product_id'] == 0)
								$shopAuction['product_id'] = null;

						$shopAuction['country_id'] = $country_id;
						$shopAuction['auction_id'] = $item_id;
						$shopAuction['auction_name'] = $item->{'itemTitle'};

						$price = 0;
						foreach($item->{'priceInfo'}->{'item'} as $priceInfo) {
							if($priceInfo->{'priceType'} == 'buyNow') {
								$price = strtr($priceInfo->{'priceValue'}, ',', '.');
							}
						}

						if(!$item->{'endingTime'}) {
							$date_stop = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);
						} else {
							$date_stop = date('Y-m-d H:i:s', strtotime($item->{'endingTime'}));	
						}

						$shopAuction['auction_price'] = $price;
						//$shopAuction['auction_image_url'] = '';
						$photo = '';
                                        	if($item->{'photosInfo'}) {
                                                	foreach($item->{'photosInfo'}->{'item'} as $photoInfo) {
                                                        	if($photoInfo->{'photoSize'} == 'large') {
                                                                	$photo = $photoInfo->{'photoUrl'};
                                                       		}
                                               		}
                                        	}
						$shopAuction['auction_image_url'] = $photo;
						$shopAuction['auction_payment'] = 0;
						$shopAuction['auction_transport'] = 0;
						$shopAuction['auction_items'] = 0;
						$shopAuction['date_start'] = date("Y-m-d H:i:s", time());
						$shopAuction['date_stop'] = $date_stop;
						$shopAuction['other_system'] = 1;
						$shopAuction->save(); //UNCOMMENT

						$asa_id = (int)$shopAuction['asa_id'];

						if ($asa_id > 0)
						{
	// 						$auctionError['asa_id'] = $asa_id;
	// 						$auctionError->save();

							consoleLog("Zidentyfikowano aukcje wystawioną spoza sklepu (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$item_id}).");
						}

	// 					print_r($auctionError->asArray());
	// 				}
				}
			}

		}
		catch(SoapFault $soapFault)
		{
			consoleLog("ERROR: Pobranie informacji o zaginionych aukcji (shop_id: {$shop_id}, user_id: {$user_id}, allegro_login: {$allegroLogin}, country_id: {$country_id}) ".$soapFault->faultstring);
			usleep(10000);

// 			$do = "pawel@walaszek.pl";
// 			$temat = "Posrednik Allegro";
// 			$tresc = "ERROR: Pobranie informacji o zaginionych aukcji (shop_id: {$shop_id}, shop: ".M('Shop')->getShopName($shop_id).", country_id: {$country_id})\n\n".$soapFault->faultcode."\n".$soapFault->faultstring;
// 			mail($do, $temat, $tresc);

	// 		print_r($soapFault);

			if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD')
			{
				$shopSettings['error_counter'] += 1;
				$shopSettings->save(); //UNCOMMENT

				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
			}
		}
	}

	$czas['findUFO'] = time() - $czas['findUFO'];
	consoleLog("STOP - findUFO ({$czas['findUFO']} sekund)");
}


/// jedziemy po aukcjach, aby pobrać dla nich wpisane FT i FP, bo te dane później się moga przydać
if ($all || $getFTiFP)
{
	consoleLog("START - getFTiFP");
	$czas['getFTiFP'] = time();
consoleLog("Memory usage: ".memory_get_usage(true));
	// $auctionParams_ = M('AllegroWebApiShopAuction')->db->getAssoc("SELECT DISTINCT auction_id FROM allegro_shop_auction_params;");
	// consoleLog("Memory usage (shop auction params loaded): ".memory_get_usage(true));
	// foreach($auctionParams_ as $i => $row)
	// {
	// 	$auctionParams[$row['auction_id']] = $row['auction_id'];
	// 	unset($auctionParams_[$i]);
	// }

	// $shopAuctions = M('AllegroWebApiShopAuction')->findBySql('SELECT * FROM allegro_shop_auction WHERE date_stop > NOW() - INTERVAL 1 DAY ORDER BY shop_id, country_id, auction_id;');
	$shopAuctions = M('AllegroWebApiShopAuction')->findBySql('SELECT asa.*, asap.auction_id AS params_auction_id FROM allegro_shop_auction asa LEFT JOIN allegro_shop_auction_params asap USING(auction_id) WHERE asa.date_stop > NOW() - INTERVAL 5 DAY AND asap.asap_id IS NULL ORDER BY asa.shop_id, asa.country_id, asa.auction_id;');
consoleLog("Memory usage (shop auctions loaded): ".memory_get_usage(true));
	$auctionCountr = count($shopAuctions);
	$counter = 1;
	foreach($shopAuctions as $shopAuction)
	{
		$shop_id = $shopAuction['shop_id'];
		$country_id = $shopAuction['country_id'];
		$auction_id = $shopAuction['auction_id'];
		$user_id = $shopAuction['user_id'];

		$shopsAR = M('Shop')->find($shop_id);
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;
		}

		if ($skip_shop[$shop_id][$user_id])
			continue;

		// if (isset($auctionParams[$auction_id]))
		// 	continue;

		if($shopAuction['params_auction_id'] > 0)
		{
			continue;
		}

		$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
		if ($shopSettings && (int)$shopSettings[0]['error_counter'] < 5)
			$shopSettings = $shopSettings[0];
		else
		{
			consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
			continue;
		}

		if ($shopSettings['error_counter'] >= 5)
			continue;

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
			$shopSettings['error_counter'] += 1;
			$skip_shop[$shop_id][$user_id] = 'Brak danych dostępowych';
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

				$shopSettings['error_counter'] = 0;
				$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
				$shopSettings->save();
			}

			$session = $sessions[$shop_id][$user_id][$country_id];

			/// pobranie licytacji dla aukcji
			consoleLog("Pobranie informacji o aukcji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, auction_id: {$auction_id}) ({$counter}).");
			$itemInfo = $client->doGetItemsInfo($session['session-handle-part'], array($auction_id), 0, 0, 0, 1, 1);
consoleLog("Memory usage (doGetItemsInfo loaded): ".memory_get_usage(true));
			if ($itemInfo['array-item-list-info'])
			{
				$itemInfo1 = $itemInfo['array-item-list-info'][0];

				$shopAuction['auction_items'] = (int)$itemInfo1->{'item-info'}->{'it-starting-quantity'};
				if ($shopAuction['auction_items'] == 0)
					$shopAuction['auction_items'] = (int)$itemInfo1->{'item-info'}->{'it-quantity'};

				$shopAuction->save();

// 				print_r($itemInfo1);break;

				foreach($itemInfo1->{'item-postage-options'} as $itemInfoPostageOption)
				{
					$asap = M('AllegroWebApiShopAuctionParam')->create();
					$asap['auction_id'] = $auction_id;
					$asap['asap_type'] = 'postage';
// 					$asap['asap_name'] = $itemInfoPostageOption->{'postage-name'};

					$shipments = M('AllegroWebApiShipment')->find("country_id = '1' AND shipment_id = '".$itemInfoPostageOption->{'postage-id'}."'");
					if ($shipments)
						$asap['asap_name'] = $shipments[0]['shipment_name'];
					else
						$asap['asap_name'] = "Brak danych";

					$asap['asap_price'] = $itemInfoPostageOption->{'postage-amount'};
					$asap['asap_price_add'] = $itemInfoPostageOption->{'postage-amount-add'};
					$asap->save();
				}

				if($itemInfo1->{'item-payment-options'}->{'pay-option-transfer'})
				{
					$asap = M('AllegroWebApiShopAuctionParam')->create();
					$asap['auction_id'] = $auction_id;
					$asap['asap_type'] = 'payment';
					$asap['asap_name'] = 'Płatność przelewem';
					$asap['asap_price'] = 0;
					$asap->save();
				}

				if($itemInfo1->{'item-payment-options'}->{'pay-option-on-delivery'})
				{
					$asap = M('AllegroWebApiShopAuctionParam')->create();
					$asap['auction_id'] = $auction_id;
					$asap['asap_type'] = 'payment';
					$asap['asap_name'] = 'Płatność przy odbiorze';
					$asap['asap_price'] = 0;
					$asap->save();
				}

				if($itemInfo1->{'item-payment-options'}->{'pay-option-allegro-pay'})
				{
					$asap = M('AllegroWebApiShopAuctionParam')->create();
					$asap['auction_id'] = $auction_id;
					$asap['asap_type'] = 'payment';
					$asap['asap_name'] = 'Płatność Allegro';
					$asap['asap_price'] = 0;
					$asap->save();
				}

				if($itemInfo1->{'item-payment-options'}->{'pay-option-payu'})
				{
					$asap = M('AllegroWebApiShopAuctionParam')->create();
					$asap['auction_id'] = $auction_id;
					$asap['asap_type'] = 'payment';
					$asap['asap_name'] = 'Płatność PayU';
					$asap['asap_price'] = 0;
					$asap->save();
				}
			}
		}
		catch(SoapFault $soapFault)
		{
			consoleLog("ERROR: Pobranie informacji o aukcji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) {$soapFault->faultcode}: ".$soapFault->faultstring);
// 			consoleLog("FAULTCODE:". $soapFault->faultcode);
			// die;
			usleep(10000);

			if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD' || $soapFault->faultcode == 'ERR_WEBAPI_KEY')
			{
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();

				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
			}
		}
		$counter++;
	}
consoleLog("Memory usage end getFTiFP): ".memory_get_usage(true));
	unset($auctionParams);

	$czas['getFTiFP'] = time() - $czas['getFTiFP'];
	consoleLog("STOP - getFTiFP ({$czas['getFTiFP']} sekund)");
}

/// jedziemy po aktywnych aukcjach, aby pobrać licytacje

if ($all || $getBids)
{

	consoleLog("START - getBids");
	$czas['getBids'] = time();
	consoleLog("Memory usage: ".memory_get_usage(true));
	$auctionCountr = count($shopAuctions);
	$journal = array();
	$wszystkie = array();
	$counter = 1;
	$user_id = 0;
	$shop_id = 19;
	$siteJournal = 0;
	$shopSettings = array();

	if($shopId)
	{
		$shopAuctions = M('AllegroWebApiShopAuction')->findActive($shopId);
	}
	else
	{
		$shopAuctions = M('AllegroWebApiShopAuction')->findActive();
	}
	//$shopAuctions = M('AllegroWebApiShopAuction')->find("(auction_active = 1 OR date_stop > NOW() - INTERVAL 10 DAY) AND shop_id = '{$shop_id}'", array('order' => 'shop_id ASC, country_id ASC'));
	//var_dump($shopAuctions[0]);//die();
consoleLog("Memory usage (loaded active auctions): ".memory_get_usage(true));
	foreach($shopAuctions as $shopAuction)
	{
		$shop_id = $shopAuction['shop_id'];
		$user_id = $shopAuction['user_id'];

		$country_id = $shopAuction['country_id'];
		$auction_id = $shopAuction['auction_id'];

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
			$skip_shop[$shop_id][$user_id] = 'Brak danych dostępowych';
			continue;
		}

		if ($shopSettings['sitejournal_row_id'] == 0)
			$wszystkie[$shop_id][$user_id] = true;

		if (!isset($journal[$shop_id][$user_id]))
			$journal[$shop_id][$user_id] = false;

		if ($shopSettings['error_counter'] >= 5)
			continue;

		$web_api_code = $shopSettings['web_api_code'];

		$shopsAR = M('Shop')->find($shop_id);
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;
		}

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
				$skip_shop[$shop_id][$user_id] = 'Brak danych dostępowych';
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();
			}
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

				$shopSettings['error_counter'] = 0;
				$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
				$shopSettings->save();
			}
			$session = $sessions[$shop_id][$user_id][$country_id];

			if ($journal[$shop_id][$user_id] === false)
			{
				/// pobranie listy aukcji dla sklepu, które uległy aktualizacji
				/// zapamiętywanie żyrnala
				$start_time = microtime();
				$start_time = explode(' ',$start_time);
				$zapytan = 0;
				$rows = true;
				$siteJournal = 0;
				while($rows === true || count($rows) == 100)
				{
					consoleLog("Pobranie Journala (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}, row-id: {$shopSettings['sitejournal_row_id']}).");
					$zapytan++;
					$koniec_time = microtime();
					$koniec_time = explode(' ', $koniec_time);
					$roznica_time = (($koniec_time[0] + $koniec_time[1]) - ($start_time[0] + $start_time[1]));
					
					if($roznica_time < 0.8 && $zapytan > 50)
					{
						usleep(10000);
						echo "\n Czekam 1 sekunde -  przekroczono limit zapytan na sekunde. \n";
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 1;
					}
					if($roznica_time >= 0.8)
					{
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}
					$rows = $client->doGetSiteJournal($session['session-handle-part'], $shopSettings['sitejournal_row_id']);
					

					if ($rows)
					{
						consoleLog("Memory usage (getSiteJournal): ".memory_get_usage(true));
						foreach($rows as $row)
						{
	// 						$siteJournalShopAuctions[$shop_id][$user_id][$row->{'item-id'}] = $row->{'item-id'};
							$auction_id_ = $row->{'item-id'};

							consoleLog("Wg żurnala (shop_id: {$shop_id}, user_id: {$user_id}, auction_id: {$auction_id_})");
							$allegro_shop_auction_do_analizy = M('AllegroWebApiShopAuctionDoAnalizy')->findOrCreate("shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND auction_id = '{$auction_id_}'");
							$allegro_shop_auction_do_analizy[0]['shop_id'] = $shop_id;
							$allegro_shop_auction_do_analizy[0]['user_id'] = $user_id;
							$allegro_shop_auction_do_analizy[0]['auction_id'] = $auction_id_;
							$allegro_shop_auction_do_analizy[0]->save();

							if ($row->{'row-id'} > $siteJournal)
							{//echo 'row: '.$row->{'row-id'}."\n";
								$siteJournal = $row->{'row-id'};
							}
						}

						$shopSettings['sitejournal_row_id'] = $siteJournal;
						$shopSettings->save();
					}
				}

				$journal[$shop_id][$user_id] = true;
			}

			if ($wszystkie[$shop_id][$user_id])
			{
				consoleLog("Wszystkie (shop_id: {$shop_id}, user_id: {$user_id}, auction_id: {$auction_id})");
				$allegro_shop_auction_do_analizy = M('AllegroWebApiShopAuctionDoAnalizy')->findOrCreate("shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND auction_id = '{$auction_id_}'");
				$allegro_shop_auction_do_analizy[0]['shop_id'] = $shop_id;
				$allegro_shop_auction_do_analizy[0]['user_id'] = $user_id;
				$allegro_shop_auction_do_analizy[0]['auction_id'] = $auction_id;
				$allegro_shop_auction_do_analizy[0]->save();
			}
		}
		catch(SoapFault $soapFault)
		{
			consoleLog("ERROR: Pobranie licytacji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) {$soapFault->faultcode}: ".$soapFault->faultstring);
// 			consoleLog("FAULTCODE:". $soapFault->faultcode);
			usleep(10000);
// 			$skip_shop[$shop_id][$user_id] = true;

			if ($soapFault->faultcode == 'ERR_AUCTION_KILLED')
			{
				$shopAuction['auction_active'] = 0;
				$shopAuction->save();
			}
			if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD' || $soapFault->faultcode == 'ERR_WEBAPI_KEY_INACTIVE' || $soapFault->faultcode == 'ERR_WEBAPI_KEY')
			{
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();
// 				$skip_shop[$shop_id] = true;
				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
			}

			if($soapFault->faultcode == 'ERR_NO_SESSION')
				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
		}
		$counter++;
	}

	$shopAuctions = M('AllegroWebApiShopAuction')->findBySql("SELECT asa.* FROM allegro_shop_auction asa INNER JOIN allegro_shop_auction_do_analizy asada ON asada.shop_id = asa.shop_id AND asada.user_id = asa.user_id AND asada.auction_id = asa.auction_id ORDER BY asa.shop_id, asa.user_id, asa.auction_id");
consoleLog("Memory usage (AllegroWebApiShopAuction loaded): ".memory_get_usage(true));
	usleep(10000);
	$auctionCountr = count($shopAuctions);
	$counter = 1;
	$start_time = microtime();
	$start_time = explode(' ',$start_time);
	$zapytan = 0;
	foreach($shopAuctions as $shopAuction)
	{
		$shop_id = $shopAuction['shop_id'];
		$user_id = $shopAuction['user_id'];

		$country_id = $shopAuction['country_id'];
		$auction_id = $shopAuction['auction_id'];

		if ($skip_shop[$shop_id][$user_id])
			continue;

		$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
		if ($shopSettings && (int)$shopSettings[0]['error_counter'] < 5)
			$shopSettings = $shopSettings[0];
		else
		{
			consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
			$skip_shop[$shop_id][$user_id] = 'Brak danych dostępowych';
			continue;
		}

		if ($shopSettings['error_counter'] >= 5)
			continue;

		$web_api_code = $shopSettings['web_api_code'];

		$shopsAR = M('Shop')->find($shop_id);
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;
		}

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
				$skip_shop[$shop_id][$user_id] = 'Brak danych dostępowych';
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();
			}
			continue;
		}

		try
		{
			$client = new Core_AllegroWebApiSoapClient();

			/// pobieranie wersji WebAPI
			if (!isset($versions[$country_id][$web_api_code]))
			{
				consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");

				$koniec_time = microtime();
				$koniec_time = explode(' ', $koniec_time);
				$roznica_time = (($koniec_time[0] + $koniec_time[1]) - ($start_time[0] + $start_time[1]));
					
					if($roznica_time < 0.8 && $zapytan > 50)
					{
						usleep(10000);
						echo "\n Czekam 1 sekunde -  przekroczono limit zapytan na sekunde.  pobieranie Api {$roznica_time}, {$zapytan} \n";
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}
					if($roznica_time >= 0.8)
					{
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}

				$versions[$country_id][$web_api_code] = $client->doQuerySysStatus(1, $country_id, $web_api_code);
				$zapytan++;
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
				$koniec_time = microtime();
				$koniec_time = explode(' ', $koniec_time);
				$roznica_time = (($koniec_time[0] + $koniec_time[1]) - ($start_time[0] + $start_time[1]));
					
					if($roznica_time < 0.8 && $zapytan > 50)
					{
						usleep(10000);
						echo "\n Czekam 1 sekunde -  przekroczono limit zapytan na sekunde.  logowanie {$roznica_time}, {$zapytan} \n";
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}
					if($roznica_time >= 0.8)
					{
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}

				$sessions[$shop_id][$user_id][$country_id] = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $web_api_code, $version['ver-key']);
				$zapytan++;

				$shopSettings['error_counter'] = 0;
				$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
				$shopSettings->save();
			}
			$session = $sessions[$shop_id][$user_id][$country_id];

			/// pobranie licytacji dla aukcji
			$counter++;
			consoleLog("Pobranie licytacji (shop_id: $shop_id, user_id: {$user_id}, country_id: $country_id, auction_id: $auction_id) ($counter z $auctionCountr).");

			$koniec_time = microtime();
			$koniec_time = explode(' ', $koniec_time);
			$roznica_time = (($koniec_time[0] + $koniec_time[1]) - ($start_time[0] + $start_time[1]));
					
			if($roznica_time < 0.8 && $zapytan > 50)
			{
				usleep(10000);
				echo "\n Czekam 1 sekunde -  przekroczono limit zapytan na sekunde. bids {$roznica_time}, {$zapytan} \n";
				$start_time = microtime();
				$start_time = explode(' ',$start_time);
				$zapytan = 0;
			}
			if($roznica_time >= 0.8)
					{
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}

			$bids = $client->doGetBidItem2($session['session-handle-part'], $auction_id);
			$zapytan++;
			$newOffersCounter = 0;
			$delete_do_analizy = true;
			foreach($bids as $bid)
			{
				// 4 - czy zablokowany użytkownik Allegro
				// 8 - czy wygrał / kupił
				$b_u_id = (int)$bid->{'bids-array'}[1];
				if($b_u_id == 0)
				{
					$delete_do_analizy = false;
					continue;
				}
				if ($bid->{'bids-array'}[4] == 0 && $bid->{'bids-array'}[8] == 1)
				{
					$bid_user_id = $bid->{'bids-array'}[1];
					$quantity = $bid->{'bids-array'}[5];												// ilość sztuk
					$price = $bid->{'bids-array'}[6];														// za 1 sztukę
					$bidDate = date("Y-m-d H:i:s", $bid->{'bids-array'}[7]);		// data licytacji
	// 				print_r($bid->{'bids-array'});

					$bids = M('AllegroWebApiBid')->findByAuctionIdAndUserId($auction_id, $bid_user_id);
					if ($bids)
						$bid = $bids[0];
					else
						$bid = M('AllegroWebApiBid')->create();

					// wykrycie, że dana licytacja jest nowa/zaktualizowana (zmiana łącznej wartości)
					if ($quantity*$price != $bid['ab_quantity']*$bid['ab_price'])
					{
						$bid['shop_id'] = $shop_id;
						$bid['country_id'] = $country_id;
						$bid['auction_id'] = $auction_id;
						$bid['user_id'] = $bid_user_id;
						$bid['ab_quantity'] = $quantity;
						$bid['ab_price'] = $price;
						$bid['ab_sended_to_shop'] = 0;
						$bid['ab_bid_date'] = $bidDate;
						$bid->save();

						$newOffersCounter++;

						$users = M('AllegroWebApiUser')->findByUserId($bid_user_id);
						$user = $users[0];
						if(!$user)
						{
							consoleLog("Pobranie porcji danych kontaktowych (shop_id: {$shop_id}, user_id: {$bid_user_id}, country_id: {$country_id}, auction_id: {$auction_id}.");
								if (isset($contacts)) unset($contacts);
								$koniec_time = microtime();
								$koniec_time = explode(' ', $koniec_time);
								$roznica_time = (($koniec_time[0] + $koniec_time[1]) - ($start_time[0] + $start_time[1]));
										
								if($roznica_time < 0.8 && $zapytan > 50)
								{
									usleep(10000);
									echo "\n Czekam 1 sekunde -  przekroczono limit zapytan na sekunde. bids {$roznica_time}, {$zapytan} \n";
									$start_time = microtime();
									$start_time = explode(' ',$start_time);
									$zapytan = 0;
								}
								if($roznica_time >= 0.8)
										{
											$start_time = microtime();
											$start_time = explode(' ',$start_time);
											$zapytan = 0;
										}
								$contacts = $client->doMyContact($session['session-handle-part'], array($auction_id));
								$zapytan++;
								foreach($contacts as $contactNo => $contact)
								{
									if($contact->{'contact-user-id'} == $bid_user_id)
									{

										$user = M('AllegroWebApiUser')->create();
										$user['user_hash'] = md5(uniqid(rand(), true));
						

										$user['update_date'] = date('Y-m-d H:i:s');
										$user['user_id'] = $contact->{'contact-user-id'};
										$user['nick'] = $contact->{'contact-nick'};
										$user['first_name'] = $contact->{'contact-first-name'};
										$user['last_name'] = $contact->{'contact-last-name'};
										$user['company'] = $contact->{'contact-company'};
										$user['email'] = $contact->{'contact-email'};
										$user['street'] = $contact->{'contact-street'};
										$user['postcode'] = $contact->{'contact-postcode'};
										$user['city'] = $contact->{'contact-city'};
										$user['country_id'] = $contact->{'contact-country'};
										$user['phone'] = $contact->{'contact-phone'};
										$user['phone2'] = $contact->{'contact-phone2'};
										$user['rating'] = $contact->{'contact-rating'};
										$user['blocked'] = $contact->{'contact-blocked'};

										consoleLog("Zapisanie danych użytkownika w bazie (pos: {$contactNo}, bid_user_id: {$user['user_id']}, nick: {$user['nick']}).");

										$user->save();
									}

								}
						}
					}
				}
			}
consoleLog("Memory usage (after get bids and missing user info): ".memory_get_usage(true));
			/// czy aukcja została zakończona

			$koniec_time = microtime();
			$koniec_time = explode(' ', $koniec_time);
			$roznica_time = (($koniec_time[0] + $koniec_time[1]) - ($start_time[0] + $start_time[1]));
					
			if($roznica_time < 0.8 && $zapytan > 50)
			{
				usleep(10000);
				echo "\n Czekam 1 sekunde -  przekroczono limit zapytan na sekunde.  itemInfoExt {$roznica_time}, {$zapytan} \n";
				$start_time = microtime();
				$start_time = explode(' ',$start_time);
				$zapytan = 0;
			}
			if($roznica_time >= 0.8)
					{
						$start_time = microtime();
						$start_time = explode(' ',$start_time);
						$zapytan = 0;
					}

			$auctionInfo = $client->doShowItemInfoExt($session['session-handle-part'], $auction_id, 1, 0, 0, 0, 0);
			$zapytan++;

			$price = $auctionInfo['item-list-info-ext']->{'it-price'};
			if ($price <= 0)
				$price = $auctionInfo['item-list-info-ext']->{'it-buy-now-price'};

			if ($auctionInfo['item-list-info-ext']->{'it-ending-time'} - time() <= 0)
			{
				if ($auctionInfo['item-list-info-ext']->{'it-ending-time'} > 1000000000)	/// just in case
				{
					$shopAuction['date_stop'] = date("Y-m-d H:i:s", $auctionInfo['item-list-info-ext']->{'it-ending-time'});
					$shopAuction['auction_active'] = 0;

					$shopAuctionRenew = $shopAuction->AllegroWebApiShopAuctionRenew;
					if ($shopAuctionRenew)
					{
						if ($shopAuctionRenew['asar_renew'] > 0)
						{
							$shopAuctionRenew['asar_renew'] = 0;
							$shopAuctionRenew['asar_renew_queue'] = 1;
							$shopAuctionRenew['update_timestamp'] = date('Y-m-d H:i:s');
							$shopAuctionRenew->save();
						}
					}
				}
			}
			$shopAuction['auction_price'] = $price;
			$shopAuction->save();
			if($delete_do_analizy)
			M('AllegroWebApiShopAuctionDoAnalizy')->db->execQuery("DELETE FROM allegro_shop_auction_do_analizy WHERE shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND auction_id = '{$auction_id}';");
		}
		catch(SoapFault $soapFault)
		{
			consoleLog("ERROR: Pobranie licytacji (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) {$soapFault->faultcode}: {$soapFault->faultstring}");
			usleep(10000);

			if ($soapFault->faultcode == 'ERR_AUCTION_KILLED' || $soapFault->faultcode == 'ERR_INVALID_ITEM_ID')
			{
				M('AllegroWebApiShopAuctionDoAnalizy')->db->execQuery("DELETE FROM allegro_shop_auction_do_analizy WHERE shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND auction_id = '{$auction_id}';");

				$shopAuction['auction_active'] = 0;
				$shopAuction->save();
			}

			if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD' || $soapFault->faultcode == 'ERR_CAPTCHA_REQUIRED')
			{
				$shopSettings['error_counter'] += 1;
				$shopSettings->save();
				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
			}
			if ($soapFault->faultcode == 'ERR_NO_SESSION')
				$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
		}
	}

consoleLog("Memory usage (end getBids): ".memory_get_usage(true));
	$czas['getBids'] = time() - $czas['getBids'];
	consoleLog("STOP - getBids ({$czas['getBids']} sekund)");
}

/// Pobieranie nowych PzA
if ($all || $getPzA)
{
	consoleLog("START - getPzA");
	$czas['getPzA'] = time();
consoleLog("Memory usage: ".memory_get_usage(true));
	$bids = M('AllegroWebApiBid')->findNotPayed();
	consoleLog("Memory usage (AllegroWebApiBid): ".memory_get_usage(true));
	if ($bids)
	{
		$auctionCountr = count($bids);
// 		var_dump($auctionCountr);die;
		$counter = 1;
		foreach($bids as $bid)
		{
			$shop_id = $bid['shop_id'];
			$country_id = $bid['country_id'];
			$auction_id = $bid['auction_id'];

			$shopAuction = $bid->AllegroWebApiShopAuction;

			$user_id = (int)$shopAuction['user_id'];

			$shopsAR = M('Shop')->find($shop_id);
			if ($shopsAR)
			{
				if ($shopsAR[0]['shop_active'] == 0)
					continue;
			}

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND user_id = '{$user_id}'");
			if ($shopSettings && (int)$shopSettings[0]['error_counter'] < 5)
				$shopSettings = $shopSettings[0];
			else
			{
				consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
				continue;
			}

			if ($skip_shop[$shop_id][$user_id])
				continue;

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

				/// właściwe logowanie do serwisu
				if (!isset($sessions[$shop_id][$user_id]))
				{
					$sessions[$shop_id][$user_id] = array();
				}
				if (!isset($sessions[$shop_id][$user_id][$country_id]))
				{
					consoleLog("Logowanie do WebApi (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}).");
					$sessions[$shop_id][$user_id][$country_id] = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $web_api_code, $version['ver-key']);

					$shopSettings['error_counter'] = 0;
					$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
					$shopSettings->save();
				}
				$session = $sessions[$shop_id][$user_id][$country_id];

				/// pobieranie IDków transakcji dla danej aukcji
				consoleLog("doGetTransactionsIDs(shop_id: {$shop_id}, user_id: {$user_id}, auction_id: {$auction_id}) [$counter z $auctionCountr].");
				$transactionIds = $client->call("doGetTransactionsIDs", array(
						'session-handle' => $session['session-handle-part'],
						'items-id-array' => array($auction_id),
						'user-role' => 'seller'
					));

// 				if ($auction_id == 1887731618)
// 					print_r($transactionIds);
				$counter++;
				if ($transactionIds && is_array($transactionIds))
				foreach($transactionIds as $transaction_id)
				{
					/// tylko jeżeli dana transakcja jeszcze nie została zapisana
                    $postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find("postbuyform_id = {$transaction_id} AND shop_id = {$shop_id} AND user_id = {$user_id}");
//					$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find($transaction_id);
					if (!$postbuyformdatas)
					{
						/// pobieranie danych z formularza pozakupowego
						consoleLog("doGetPostBuyFormsDataForSellers (shop_id: {$shop_id}, user_id: {$user_id}, auction_id: {$auction_id}, transaction_id: {$transaction_id})");
						$postBuyDatas = $client->doGetPostBuyFormsDataForSellers($session['session-handle-part'], array($transaction_id));

						/// sprawdzenie czy dany BID już istnieje, bo jeżeli nie to skip (do następnego razu) ORAZ czy nie ma już wypełnionego starego FODa
						$wszystkoOK = true;
						if (is_array($postBuyDatas[0]->{'post-buy-form-items'}))
						{
							foreach($postBuyDatas[0]->{'post-buy-form-items'} as $item)
							{
								$bidForFormdatas = M('AllegroWebApiBid')->findByAuctionIdAndUserId($item->{'post-buy-form-it-id'}, $postBuyDatas[0]->{'post-buy-form-buyer-id'});
								if ($bidForFormdatas)
								{
									if($bidForFormdatas[0]['ab_quantity_payed'] == $bidForFormdatas[0]['ab_quantity'])
									{
										consoleLog("doGetPostBuyFormsDataForSellers (shop_id: {$shop_id}, auction_id: {$auction_id}, user_id: ".$postBuyDatas[0]->{'post-buy-form-buyer-id'}.") => pominięto, istnieje stary FOD");
										$wszystkoOK = false;
									}
								}
								else
								{
									consoleLog("doGetPostBuyFormsDataForSellers (shop_id: {$shop_id}, auction_id: {$auction_id}, transaction_id: {$transaction_id}) => pominięto, nie istnieje BID");
									$wszystkoOK = false;
								}
							}
						}
						else
						{
							consoleLog("doGetPostBuyFormsDataForSellers (shop_id: {$shop_id}, auction_id: {$auction_id}, transaction_id: {$transaction_id}) => pusta tablica 'post-buy-form-items'");
							$wszystkoOK = false;
						}

						if ($wszystkoOK == false)
							continue;

                        $postbuyformdatas=M('AllegroWebApiPostbuyformdata')->find($postBuyDatas[0]->{'post-buy-form-id'});
                        if($postbuyformdatas)
                        {
                            $primaryPostBuyFormId=M('AllegroWebApiPostbuyformdata')->getNext_postbuyformadr_id();
                            $doublePostBuyFormId=$postBuyDatas[0]->{'post-buy-form-id'};
                        }
                        else
                        {
                            $primaryPostBuyFormId=$postBuyDatas[0]->{'post-buy-form-id'};
                            $doublePostBuyFormId=0;
                        }

						/// zapisanie formularza pozakupowego (bez itemsów i adresów)
						$postbuyformdata = M('AllegroWebApiPostbuyformdata')->create();
						$postbuyformdata['postbuyform_id'] =$primaryPostBuyFormId; //$postBuyDatas[0]->{'post-buy-form-id'};
                        $postbuyformdata['postbuyform_id_double'] = $doublePostBuyFormId;
						$postbuyformdata['shop_id'] = $shop_id;
                        $postbuyformdata['user_id'] = $user_id;
						$postbuyformdata['postbuyform_buyer_id'] = $postBuyDatas[0]->{'post-buy-form-buyer-id'};
						$postbuyformdata['postbuyform_amount'] = $postBuyDatas[0]->{'post-buy-form-amount'};
						$postbuyformdata['postbuyform_postage_amount'] = $postBuyDatas[0]->{'post-buy-form-postage-amount'};
// 						$postbuyformdata['postbuyform_shipment_title'] = $postBuyDatas[0]->{'post-buy-form-shipment-title'};	// allegro wycofało się z tego
// 						$postbuyformdata['postbuyform_shipment_type'] = $postBuyDatas[0]->{'post-buy-form-shipment-type'};		// allegro wycofało się z tego
						$postbuyformdata['shipment_id'] = $postBuyDatas[0]->{'post-buy-form-shipment-id'};
						
						if(M('AllegroWebApiShipment')->isPaczkomatyShipping($postbuyformdata['shipment_id']))
						{
							if (isset($postBuyDatas[0]->{'post-buy-form-gd-address'}->{'post-buy-form-adr-full-name'}) && !empty($postBuyDatas[0]->{'post-buy-form-gd-address'}->{'post-buy-form-adr-full-name'}))
							{
								$postbuyformdata['postbuyform_shipment_title'] = trim(str_replace("Paczkomat", '', $postBuyDatas[0]->{'post-buy-form-gd-address'}->{'post-buy-form-adr-full-name'}));
							}
						}
						$postbuyformdata['postbuyform_invoice_option'] = $postBuyDatas[0]->{'post-buy-form-invoice-option'};
						$postbuyformdata['postbuyform_msg_to_seller'] = $postBuyDatas[0]->{'post-buy-form-msg-to-seller'};
						$postbuyformdata['postbuyform_pay_type'] = $postBuyDatas[0]->{'post-buy-form-pay-type'};
						$postbuyformdata['postbuyform_pay_id'] = $postBuyDatas[0]->{'post-buy-form-pay-id'};
						$postbuyformdata['postbuyform_pay_status'] = $postBuyDatas[0]->{'post-buy-form-pay-status'};
						$postbuyformdata['postbuyform_date_init'] = $postBuyDatas[0]->{'post-buy-form-date-init'};
						$postbuyformdata['postbuyform_date_recv'] = $postBuyDatas[0]->{'post-buy-form-date-recv'};
						$postbuyformdata['postbuyform_date_cancel'] = $postBuyDatas[0]->{'post-buy-form-date-cancel'};
						$postbuyformdata->save();

						if ($postBuyDatas[0]->{'post-buy-form-invoice-option'} > 0)
						{
							$postbuyformadr = M('AllegroWebApiPostbuyformadr')->create();
							$postbuyformadr['postbuyform_id'] = $primaryPostBuyFormId;//$postBuyDatas[0]->{'post-buy-form-id'}; //$primaryPostBuyFormId
							$postbuyformadr['postbuyformadr_type'] = 'invoice';
							$postbuyformadr['postbuyformadr_country'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-country'};
							$postbuyformadr['postbuyformadr_street'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-street'};
							$postbuyformadr['postbuyformadr_postcode'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-postcode'};
							$postbuyformadr['postbuyformadr_city'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-city'};
							$postbuyformadr['postbuyformadr_full_name'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-full-name'};
							$postbuyformadr['postbuyformadr_company'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-company'};
							$postbuyformadr['postbuyformadr_phone'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-phone'};
							$postbuyformadr['postbuyformadr_nip'] = $postBuyDatas[0]->{'post-buy-form-invoice-data'}->{'post-buy-form-adr-nip'};
							$postbuyformadr->save();
						}

						$postbuyformadr = M('AllegroWebApiPostbuyformadr')->create();
						$postbuyformadr['postbuyform_id'] = $primaryPostBuyFormId;//$postBuyDatas[0]->{'post-buy-form-id'};//$primaryPostBuyFormId
						$postbuyformadr['postbuyformadr_type'] = 'shipment';
						$postbuyformadr['postbuyformadr_country'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-country'};
						$postbuyformadr['postbuyformadr_street'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-street'};
						$postbuyformadr['postbuyformadr_postcode'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-postcode'};
						$postbuyformadr['postbuyformadr_city'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-city'};
						$postbuyformadr['postbuyformadr_full_name'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-full-name'};
						$postbuyformadr['postbuyformadr_company'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-company'};
						$postbuyformadr['postbuyformadr_phone'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-phone'};
						$postbuyformadr['postbuyformadr_nip'] = $postBuyDatas[0]->{'post-buy-form-shipment-address'}->{'post-buy-form-adr-nip'};
						$postbuyformadr->save();

						foreach($postBuyDatas[0]->{'post-buy-form-items'} as $item)
						{
							$postbuyformitem = M('AllegroWebApiPostbuyformitem')->create();
							$postbuyformitem['postbuyform_id'] = $primaryPostBuyFormId;//$postBuyDatas[0]->{'post-buy-form-id'};//$primaryPostBuyFormId
							$postbuyformitem['postbuyformit_quantity'] = $item->{'post-buy-form-it-quantity'};
							$postbuyformitem['postbuyformit_amount'] = $item->{'post-buy-form-it-amount'};
							$postbuyformitem['postbuyformit_auction_id'] = $item->{'post-buy-form-it-id'};
							$postbuyformitem['postbuyformit_title'] = $item->{'post-buy-form-it-title'};
							$postbuyformitem['postbuyformit_country'] = $item->{'post-buy-form-it-country'};
                            $postbuyformitem['postbuyformit_price'] = $item->{'post-buy-form-it-price'};
                            //Sprawdzanie czy ceny sa poprawne - usterka 8029
                            $postbuyformit_amount=$item->{'post-buy-form-it-amount'};
                            $postbuyformit_price=$item->{'post-buy-form-it-price'};
                            $postbuyformit_quantity=$item->{'post-buy-form-it-quantity'};
                            $countedAmount=$postbuyformit_price*$postbuyformit_quantity;
                            if($countedAmount!=$postbuyformit_amount)
                            {
                                $postbuyformit_price=$postbuyformit_amount/$postbuyformit_quantity;
                                $postbuyformitem['postbuyformit_price']=$postbuyformit_price;
                            }

							$postbuyformitem->save();

							$bidForFormdatas = M('AllegroWebApiBid')->findByAuctionIdAndUserId($item->{'post-buy-form-it-id'}, $postBuyDatas[0]->{'post-buy-form-buyer-id'});
							$bidForFormdatas[0]['ab_quantity_payed'] += $item->{'post-buy-form-it-quantity'};
							$bidForFormdatas[0]->save();
						}

						consoleLog("doGetPostBuyFormsDataForSellers (shop_id: {$shop_id}, user_id: {$user_id}, auction_id: {$auction_id}, transaction_id: {$transaction_id}) => SAVED");
					}
				}
			}
			catch(SoapFault $soapFault)
			{
				// print_r($soapFault);
				consoleLog("ERROR {$soapFault->faultcode}: ".$soapFault->faultstring);
				usleep(10000);

				if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD' || $soapFault->faultcode == 'ERR_CAPTCHA_REQUIRED')
				{
					$shopSettings['error_counter'] += 1;
					$shopSettings->save();

					$skip_shop[$shop_id][$user_id] = $soapFault->faultcode;
				}
			}
		}
	}
consoleLog("Memory usage (end getPzA): ".memory_get_usage(true));
	$czas['getPzA'] = time() - $czas['getPzA'];
	consoleLog("STOP - getPzA ({$czas['getPzA']} sekund)");
}

/// Powiadamianie sklepow
if ($all || $shopNotice)
{
	consoleLog("START - shopNotice");
	$czas['shopNotice'] = time();

	/// powiadomienie sklepów, że mają co ściągać
	$shops = M('AllegroWebApiPostbuyformdata')->getShopsNotNotices();
	/// powiadomienie sklepów, że sa dla nich płatności
	$shops2 = M('AllegroWebApiPostbuyformpayment')->getShopsNotNotices();
	consoleLog("Memory usage (loaded data): ".memory_get_usage(true));
	if ($shops2)
	foreach($shops2 as $shop2)
		$shops[] = $shop2;

#	$shops = array(array("shop_id" => 19));


	$shopCounter = count($shops);
	$counter = 1;
	if ($shops)
	foreach($shops as $shop)
	{
		$shop_id = (int)$shop['shop_id'];

		// if (isset($skip_shop[$shop_id]) && $skip_shop[$shop_id])
		// {
		// 	consoleLog("Pominiecie sklepu {$shop_id}");
		// 	print_r(array($skip_shop[$shop_id]));
		// 	continue;
		// }

		$shopsAR = M('Shop')->find($shop_id);
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;

			consoleLog("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter).");

			$shop_url = $shopsAR[0]['shop_url'] . "/allegro.php?key=1";
			$ret = @file_get_contents($shop_url);

			if ($ret !== false)
				$redPards = explode("|", $ret);
#print_r($redPards);
			if ($ret !== false && $redPards[0] == "OK")
			{
				$postbuyform_ids = json_decode($redPards[1]);
				consoleLog("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => OK (count: ".count($postbuyform_ids).").");
				if (is_array($postbuyform_ids))
				foreach($postbuyform_ids as $postbuyform_id)
				{
					$afds = M('AllegroWebApiPostbuyformdata')->find((int)$postbuyform_id);
					if ($afds)
					{
						foreach($afds as $afd)
						{
							$afd['postbuyform_get_by_shop'] = 1;
							$afd->save();
						}
					}
				}

				if ($redPards[2] == "PAYED")
				{
					$postbuyform_ids = json_decode($redPards[3]);

					consoleLog("Oznaczenie płatności jako zapłaconej (shop_id: {$shop_id}) ($counter z $shopCounter) => OK (count: ".count($postbuyform_ids).").");
					if (is_array($postbuyform_ids))
					foreach($postbuyform_ids as $postbuyform_id)
					{
						$postbuyform_id = (int)$postbuyform_id;
						$afps = M('AllegroWebApiPostbuyformpayment')->find("postbuyform_id = '{$postbuyform_id}'");
						if ($afps)
						{
							foreach($afps as $afp)
							{
								$afp['postbuyformpayment_get_by_shop'] = 1;
								$afp->save();
							}
						}
					}
				}
			}
			else
			{
				consoleLog("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => ERROR ({$shop_url})!");

				#$mailer->notifyAboutProblem("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => ERROR ({$shop_url})! <br /><br /><pre>".print_r($ret, true)."</pre>");
				$do = "pawel@walaszek.pl";
				$temat = "Posrednik Allegro";
				$tresc = "Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => ERROR ({$shop_url})! <br /><br /><pre>".print_r($ret, true)."</pre>";
				mail($do, $temat, $tresc);

				$do = "lukasz.koska@ecommerce24h.pl";
				mail($do, $temat, $tresc);

				var_dump($ret);
			}
		}
		$counter++;
	}
consoleLog("Memory usage (end shopNotice): ".memory_get_usage(true));
	$czas['shopNotice'] = time() - $czas['shopNotice'];
	consoleLog("STOP - shopNotice ({$czas['shopNotice']} sekund)");
}

if($getMissingBuyersInfo || $ufoAndMissingBuyers)
{
	$sessions = null;
	$versions = null;

	$missing_buyers = M('AllegroWebApiUser')->findBySql("SELECT ab.shop_id, ab.auction_id, ab.insert_timestamp, ab.user_id, ab.country_id FROM allegro_bid ab LEFT JOIN allegro_user au ON ( ab.user_id = au.user_id ) WHERE au.user_id IS NULL  AND DATEDIFF(now(),ab.insert_timestamp) <= 30");//  AND ab.shop_id = '851'");//AND ab_quantity - ab_quantity_payed = 0");

	$mb = array();
	foreach ($missing_buyers as $m_buyer)
	{
		$mb[$m_buyer['shop_id']]['auction_ids'][] = $m_buyer['auction_id'];
		$mb[$m_buyer['shop_id']]['user_ids'][] = array('user_id' => $m_buyer['user_id'], 'country_id' => $m_buyer['country_id']);
	}//var_dump($mb);//die();
	//$mb[$m_buyer['shop_id']]['auction_ids'][] = '3213397792';
	$country_id = 1;
	foreach ($mb as $shop_id => $missing_buyer)
	{
		//$shop_id = $missing_buyer['shop_id'];
		//$auction_info = M("AllegroWebApiShopAuction")->first("auction_id = {$missing_buyer['auction_ids'][0]}");
		//$user_id = $auction_info['user_id'];var_dump($auction_info);
		//$country_id = $missing_buyer['country_id'];var_dump($missing_buyer);die('t');
		//if($country_id != 1)
		//	continue;

		$shopsAR = M('Shop')->find($shop_id);//die(print_r($shopsAR));
		if ($shopsAR)
		{
			if ($shopsAR[0]['shop_active'] == 0)
				continue;
		}//var_dump($shopAr);
		$shopSettingsArr = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}'");
		foreach ($shopSettingsArr as $shopSettings)
		{
				if ($shopSettingsArr && (int)$shopSettings['error_counter'] < 5)
				{
						//$shopSettings = $shopSettings[0];
					$user_id = $shopSettings['user_id'];
				}
				else
				{
					consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) Pośrednik nie posiada danych dostępowych.");
					continue;
				}

				$web_api_code = $shopSettings['web_api_code'];
				$allegroLogin = $shopSettings['login_allegro'];
				$allegroPassword = $shopSettings['password_allegro'];	
				$auctions_ids_string = implode(",", $missing_buyer['auction_ids']);
				$user_ids = array();

				foreach ($missing_buyer['user_ids'] as $u_id)
				{
					if($u_id['country_id'] != 1)
						continue;
					$user_ids[] = (int)$u_id['user_id'];
				}

				if (trim($allegroLogin) == '' || trim($allegroPassword) == '' || trim($web_api_code) == '')
				{
					consoleLog("ERROR (shop_id: {$shop_id}, user_id: {$user_id}) Pośrednik nie posiada danych dostępowych.");
					// $shopSettings['error_counter'] += 1;
					// $shopSettings->save();

					continue;
				}

				try
				{
					$client = new Core_AllegroWebApiSoapClient();

					/// pobieranie wersji WebAPI
					if (!isset($versions[$country_id][$web_api_code]))
					{
						consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");
						$versions[$country_id][$web_api_code] = $client->doQuerySysStatus(1, $country_id, $web_api_code);//var_dump($versions);
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

					foreach($missing_buyer['auction_ids'] as $auction_id)
					{
						//var_dump($auction_id);
					

					if (isset($contacts)) unset($contacts);

					$offset = 0;

					while($offset == 0 || count($contacts) == 25)
					{
						// print_r(array($session, $auction_id, is_object($session)));
					$contacts = $client->doMyContact($session['session-handle-part'], array($auction_id), $offset*25, 0);

					foreach ($contacts as $contactNo => $contact)
					{
						if(!in_array($contact->{'contact-user-id'},$user_ids))
						{
							//echo "nie ma \n";
							//continue;
						}
						else
						{
							//echo "jest \n";
						}	
						//var_dump($contact);die('t');
						$users = M('AllegroWebApiUser')->findByUserId($contact->{'contact-user-id'});
						
									if (!$users)
									{
										
										$user = M('AllegroWebApiUser')->create();
										$user['user_hash'] = md5(uniqid(rand(), true));

										$user['update_date'] = date('Y-m-d H:i:s');
										$user['user_id'] = $contact->{'contact-user-id'};
										$user['nick'] = $contact->{'contact-nick'};
										$user['first_name'] = $contact->{'contact-first-name'};
										$user['last_name'] = $contact->{'contact-last-name'};
										$user['company'] = $contact->{'contact-company'};
										$user['email'] = $contact->{'contact-email'};
										$user['street'] = $contact->{'contact-street'};
										$user['postcode'] = $contact->{'contact-postcode'};
										$user['city'] = $contact->{'contact-city'};
										$user['country_id'] = $contact->{'contact-country'};
										$user['phone'] = $contact->{'contact-phone'};
										$user['phone2'] = $contact->{'contact-phone2'};
										$user['rating'] = $contact->{'contact-rating'};
										$user['blocked'] = $contact->{'contact-blocked'};

										consoleLog("Zapisanie danych użytkownika w bazie (pos: {$contactNo}, bid_user_id: {$user['user_id']}, nick: {$user['nick']}).");

										if($user->save())
										{
											
											consoleLog("Wskazanie aukcji kupującego {$user['user_id']}, nick: {$user['nick']} do próby ponownego pobrania");
											// $test = M("AllegroWebApiPostbuyformdata")->findBySql("SELECT * FROM allegro_postbuyformdata WHERE postbuyform_buyer_id = {$user['user_id']} AND (postbuyform_pay_type = 'collect_on_delivery' OR postbuyform_pay_id > 0)");
											// if($test)
											// 	print_r($test);
											M("AllegroWebApiPostbuyformdata")->db->execQuery("UPDATE allegro_postbuyformdata SET postbuyform_get_by_shop = 0 WHERE postbuyform_buyer_id = {$user['user_id']}");
										}

										
									}//else{var_dump('user jest');;}//die();
					}
					$offset++;
					}
					}
				}
				catch(SoapFault $soapFault)
				{
					// print_r($soapFault);
					consoleLog("ERROR: Pobranie porcji danych kontaktowych (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) {$soapFault->faultcode}: ".$soapFault->faultstring);
					usleep(10000);

					if ($soapFault->faultcode == 'ERR_USER_PASSWD' || $soapFault->faultcode == 'ERR_WEBAPI_EXPIRED' || $soapFault->faultcode == 'ERR_BLOCKED_USER_CANT_INVOKE_METHOD')
					{
						// $shopSettings['error_counter'] += 1;
						// $shopSettings->save();

						//$skip_shop[$shop_id][$user_id] = true;
					}
				}
			}
	}
}

if($checkEndingDateOfActiveAuctions) {
	$date = new DateTime('now');
	$date->modify('last day of this month');
	$last_day_of_month = $date->format('Y-m-d');

	$shops = M('AllegroWebApiShopAuction')->findBySql('SELECT DISTINCT shop_id FROM allegro_shop_auction WHERE auction_active = 1 AND DATE( date_stop ) <= DATE( NOW( ) ) + INTERVAL 2 DAY');

	foreach($shops as $shop) {

		$sql = sql(array('shop_id = %shop_id AND error_counter < 5', 'shop_id' => $shop['shop_id']));

		$shopSettingsArr = M('AllegroWebApiShopSettings')->find($sql);

		foreach ($shopSettingsArr as $shopSettings) {
			$user_id = $shopSettings['user_id'];
			$web_api_code = $shopSettings['web_api_code'];
			$allegroLogin = $shopSettings['login_allegro'];
			$allegroPassword = $shopSettings['password_allegro'];
			$shop_id = $shopSettings['shop_id'];
			$country_id = 1;

			$sql = sql(array('shop_id = %shop_id AND auction_active = 1 AND DATE( date_stop ) <= DATE( NOW( ) ) + INTERVAL 2 DAY', 'shop_id' => $shopSettings['shop_id']));

			$shopAuctions = M('AllegroWebApiShopAuction')->find($sql);

			foreach($shopAuctions as $auction) {

				if(isset($skip_shop[$shop_id][$user_id])) {
					break;
				}
				
				try {
					$client = new Core_AllegroWebApiSoapClient();

					/// pobieranie wersji WebAPI
					if (!isset($versions[$country_id][$web_api_code])) {
						consoleLog("Pobieranie wersji API dla shop_id: $shop_id i kraju: $country_id");
						$versions[$country_id][$web_api_code] = $client->doQuerySysStatus(1, $country_id, $web_api_code);//var_dump($versions);
					}

					$version = $versions[$country_id][$web_api_code];

					// właściwe logowanie do serwisu
					if (!isset($sessions[$shop_id][$user_id])) {
						$sessions[$shop_id][$user_id] = array();
					}

					if (!isset($sessions[$shop_id][$user_id][$country_id])) {
						consoleLog("Logowanie do WebApi (shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}).");
						$sessions[$shop_id][$user_id][$country_id] = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $web_api_code, $version['ver-key']);

						if ($sessions[$shop_id][$user_id][$country_id]['user-id'] > 0) {
							$shopSettings['user_id'] = $user_id = $sessions[$shop_id][$user_id][$country_id]['user-id'];
							$shopSettings['error_counter'] = 0;
							$shopSettings['last_correct_login'] = date('Y-m-d H:i:s');
							$shopSettings->save();
						}
					}

					$session = $sessions[$shop_id][$user_id][$country_id];

					$auctionInfo = $client->doShowItemInfoExt($session['session-handle-part'], $auction['auction_id'], 1, 0, 0, 0, 0);

					if($auctionInfo['item-list-info-ext']->{'it-ending-time'} == 0) {
						$auction['date_stop'] = preg_replace('`^[0-9]{4}\-[0-9]{2}\-[0-9]{2}`', $last_day_of_month, $auction['date_stop']);
						$auction->save();

						consoleLog("Zaktualizowano datę zakończenia (auction_id: {$auction['auction_id']}, shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id})");
					}

				} catch(SoapFault $soapFault) {
					if($soapFault->faultcode == 'ERR_INVALID_ITEM_ID') {
						$auction['auction_active'] = 0;
						$auction->save();
					}

					$skip_shop[$shop_id][$user_id] = true;

					consoleLog("ERROR: (auction_id: {$auction['auction_id']}, shop_id: {$shop_id}, user_id: {$user_id}, country_id: {$country_id}) {$soapFault->faultcode}: ".$soapFault->faultstring);
					usleep(10000);
				}
			}
		}
	}	
}

$czas[0] = time() - $czas[0];
consoleLog("STOP ({$czas[0]} sekund)");
