<?php

class Theme_AllegroForm extends Core_Template
{
	public function ajaxView($args)
	{
		//die("ERROR: Testing.");

		foreach($_POST as $k => $v)
			if (!is_array($_POST[$k]))
				$_POST[$k] = trim(strip_tags($_POST[$k]));

		if ($_POST['transport'] == '' || $_POST['payment'] == '')
			die("ERROR: Wybierz formę transportu i płatności.");
		if ($_POST['entry_firstname'] == "")
			die("ERROR: Podaj imię.");
		if ($_POST['entry_lastname'] == "")
			die("ERROR: Podaj nazwisko.");
		if ($_POST['customers_email_address'] == "")
			die("ERROR: Podaj adres e-mail.");
		if ($_POST['entry_street_address'] == "")
			die("ERROR: Podaj nazwę ulicy.");
		if ($_POST['entry_postcode'] == "")
			die("ERROR: Podaj kod pocztowy.");
		if ($_POST['entry_city'] == "")
			die("ERROR: Podaj nazwę miasta.");
		if ($_POST['entry_country'] == "")
			die("ERROR: Podaj nazwę kraju.");
		if ($_POST['customers_telephone'] == "")
			die("ERROR: Podaj numer telefonu.");
		if (!is_array($_POST['abid']) || count($_POST['abid']) == 0)
			die("ERROR: Wszystkie Twoje zamówienia zostały przekazane do sklepu internetowego.");
		if ((int)$args['shop_id'] == 0)
			die("ERROR: Wystąpił problem z określeniem sprzedawcy (odśwież stronę i spróbuj ponownie).");

		foreach($_POST['abid'] as $ab_id)
		{
			$bids = M('AllegroWebApiBid')->find($ab_id);
			if (!$bids || $bids[0]['ab_sended_to_shop'] == 1)
				die("ERROR: Próbujesz po raz drugi przesłać te same zamwówienia do sklepu.");
		}

		// zapisanie informacji, które podał kupujący poprzez formularz
		$order = M('AllegroWebApiOrder')->create();
		$order['shop_id'] = (int)$args['shop_id'];
		$order['ao_transport'] = $_POST['transport'];
		$order['ao_payment'] = $_POST['payment'];
		$order['ao_comment'] = $_POST['comment'];
		$order['ao_firstname'] = $_POST['entry_firstname'];
		$order['ao_lastname'] = $_POST['entry_lastname'];
		$order['ao_email'] = $_POST['customers_email_address'];
		$order['ao_company'] = $_POST['entry_company'];
		$order['ao_nip'] = $_POST['entry_nip'];
		$order['ao_street'] = $_POST['entry_street_address'];
		$order['ao_postcode'] = $_POST['entry_postcode'];
		$order['ao_city'] = $_POST['entry_city'];
		$order['ao_country'] = $_POST['entry_country'];
		$order['ao_telephone'] = $_POST['customers_telephone'];
		$order['ao_fax'] = $_POST['customers_fax'];
		$order->save();

		// oznaczenie bids'ów jako wysłanych do sklepu
		foreach($_POST['abid'] as $ab_id)
		{
			$bids = M('AllegroWebApiBid')->find($ab_id);
			$bids[0]['ao_id'] = $order['ao_id'];
			$bids[0]['ab_sended_to_shop'] = 1;
			$bids[0]->save();
		}

		die("OK");
// 		print_r($args);
// 		print_r($_POST);
// 		die;
	}

	public function defaultView($args)
	{
 		$this->addJs($args, "jquery");
 		$this->addJs($args, "jquery.form");
 		$this->addJs($args, "jquery.validate");
 		$this->addJs($args, "AllegroForm");
 		$this->addCss($args, "main");
//
 		$this->addBox('header', 'Header', 'defaultView', $args);
// 		$this->addBox('mainPage', 'Main10Offers', 'defaultView', $args);

		$shop_id = $args['shop_id'] = (int)$args['shop_id'];

		$shops = M('Shop')->find($shop_id);
		if ($shops)
		{
			$args['shop_name_full'] = $shops[0]['shop_name_full'];
			$args['shop_url'] = $shops[0]['shop_url'];
			$shopSettings = $shops[0]->AllegroWebApiShopSettings;
			$args['shop_user_id'] = $shopSettings['user_id'];
			$args['shop_rating'] = $shopSettings['user_rating'];

			$users = M('AllegroWebApiUser')->findByUserHash($args['user_hash']);
			if ($users)
			{
				$user_id = $users[0]['user_id'];
				$args['user'] = $users[0]->asArray();

				$country_id = $args['country_id'] = $users[0]['country_id'];

				if ($country_id == 228)
				{
					$args['allegro_url'] = 'www.testwebapi.pl';
					$args['allegro_login'] = $shopSettings['login_testwebapi'];
				}
				if ($country_id == 1)
				{
					$args['allegro_url'] = 'www.allegro.pl';
					$args['allegro_login'] = $shopSettings['login_allegro'];
				}

				$bids = M('AllegroWebApiBid')->findByUserIdNotSendedToShop($shop_id, $user_id);
				if ($bids)
				{
					$args['bids'] = array();
					$i = 0;
					$args['sum'] = 0;

					$auction_payment = 1+2+4+8+16+32+64+128+256+512+1024+2048;
					$auction_transport = 1+2+4+8+16+32+64+128+256+512+1024+2048;
					foreach($bids as $bid)
					{
						$auction = $bid->AllegroWebApiShopAuction->asArray();
						$args['bids'][$i]['abid'] = $bid['ab_id'];
						$args['bids'][$i]['auction_name'] = $auction['auction_name'];
						$args['bids'][$i]['auction_id'] = $auction['auction_id'];
						$args['bids'][$i]['price'] = strtr($bid['ab_price'],'.',',');
						$args['bids'][$i]['quantity'] = $bid['ab_quantity'];
						$args['sum'] += $bid['ab_price']*$bid['ab_quantity'];

						$auction_payment = $auction_payment & $auction['auction_payment'];
						$auction_transport = $auction_transport & $auction['auction_transport'];

						$i++;
					}
					$args['sum'] = round($args['sum'], 2);

					//l($auction_payment);
					//l($auction_transport);

					$args['transport'] = array();

					foreach(M('AllegroWebApiSellFormOption')->findByCountryAndSellFormId($country_id, 13) as $transport)
					{
						if (($transport['option_id'] & $auction_transport) == $transport['option_id'])
						{
							$args['transport'][] = array(
									"id" => $transport['option_id'],
									"name" => $transport['option_name']
								);
						}
					}
					foreach(M('AllegroWebApiSellFormOption')->findByCountryAndSellFormId($country_id, 14) as $transport)
					{
						if (($transport['option_id'] & $auction_payment) == $transport['option_id'])
						{
							$args['payment'][] = array(
									"id" => $transport['option_id'],
									"name" => $transport['option_name']
								);
						}
					}
					//l(M('AllegroWebApiSe

				}
			}
		}

 		return $args;
	}
}

?>