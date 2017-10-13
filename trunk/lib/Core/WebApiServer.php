<?php

/**
 * Klasa obsłująca serwer Soap do komunikacji ze sklepami dla Allegro i Świstak
 */
class Core_WebApiServer
{
	private $authenticated = false;
	private $shop_id = null;
	private $shop_name = null;

	/**
	 * Służy do logowania, gdy metody wymagają logowania
	 */
	public function doLogin($params)
	{
		if (isset($params['login']) && isset($params['password']))
		{
			$login = $params['login'];
			$password = $params['password'];

			$shops = M('Shop')->findByShopName($login);
			if ($shops && $shops[0]['shop_password'] == $password)
			{
				$this->authenticated = true;
				$this->shop_id = $shops[0]['shop_id'];
				$this->shop_name = $login;
				return true;
			}
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Funkcja do testowania autentykacji
	 */
	public function whoLogin()
	{
		if ($this->authenticated)
			return $this->shop_id;
		else
			return false;
	}

	/**
	 * Do sprawdzania czy serwer żyje
	 */
	public function ping($params = false)
	{
		if (isset($params[0]) && $params[0] === true)
			return date("Y-m-d H:i:s");
		else
			return time();
	}

	public function getCategoryByParentId($params)
	{
		if (isset($params['parent_id']) && is_numeric($params['parent_id']))
		{
			$parent_id = (int)$params['parent_id'];
			$country_id = 0;
			if (isset($params['country_id']) && is_numeric($params['country_id']))
				$country_id = $params['country_id'];

			$categories = M('AllegroWebApiCategory')->find(sql(array("
					parent_id = %parent_id
					".($country_id > 0 ? "AND country_id = %country_id" : "")."
					AND approved = 1
					",
					"parent_id" => $parent_id,
					"country_id" => $country_id
				)), array('order' => 'category_position'));

			if ($categories)
				return $categories->asArray();
			else
				return array();
		}
		else
			return array();
	}

	/**
	 * Zwraca listę dostępnych krajów
	 */
	public function getCountries()
	{
		$countries = M('AllegroWebApiCountry')->findByApproved(1, array('order' => 'country_id'));
		if ($countries)
			return $countries->asArray();
		else
			return array();
	}

	/**
	 * Zwraca informację o konkretnym kraju
	 */
	public function getCountry($params)
	{
		if (isset($params['country_id']) && is_numeric($params['country_id']))
		$country_id = (int)$params['country_id'];
		$countries = M('AllegroWebApiCountry')->find($country_id);
		if ($countries)
			return $countries[0]->asArray();
		else
			return array();
	}

	/**
	 * Zwraca ścieżkę kategorii dla zadanej kategorii (Allegro)
	 * (taką do wyświetlenia w listach rozwijanych wyboru kategorii)
	 */
	public function getCategoryPath($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			$category_id = (int)$params['category_id'];
			$category_path = array();

			$i = 0;
			while(true)
			{
				$categories = M('AllegroWebApiCategory')->find(sql(array("
					category_id = %category_id
					AND approved = 1
					",
					"category_id" => $category_id
				)), array('order' => 'category_position'));
				if ($categories)
				{
					$parent_id = $categories[0]['parent_id'];
					$country_id = $categories[0]['country_id'];
					$categories = M('AllegroWebApiCategory')->find(sql(array("
							parent_id = %parent_id
							".($country_id > 0 ? "AND country_id = %country_id" : "")."
							",
							"parent_id" => $parent_id,
							"country_id" => $country_id
						)), array('order' => 'category_position'));
					if ($categories)
					{
						$category_path[$i] = $categories->asArray();
						$category_path[$i]['category_id'] = $category_id;
						$category_id = $categories[0]['parent_id'];
						$i++;
					}
					else
						break;
				}
				else
					break;
			}
			return array_reverse($category_path);
		}
		else
			return array();
	}

	/**
	 * Zwraca string reprezentujący ścieżke kategorii dla zadanej kategorii
	 */
	public function getCategoryPathString($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			$imploder = " > ";
			if (isset($params['imploder']))
				$imploder = $params['imploder'];

			$category_path = $this->getCategoryPath($params);
			$category_path_string = array();
			foreach($category_path as $categories)
				foreach($categories as $category)
					if ($category != $categories['category_id'] && $category['category_id'] == $categories['category_id'])
						$category_path_string[] = $category['category_name'];
			return implode($imploder, $category_path_string);
		}
		else
			return '';
	}

	/**
	 * Zwraca string reprezentujący ścieżke kategorii dla zadanej kategorii
	 */
	public function getCategoryPathArray($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			if (isset($params['imploder']))
				$imploder = $params['imploder'];

			$category_path = $this->getCategoryPath($params);
			$category_path_array = array();
			foreach($category_path as $categories)
				foreach($categories as $category)
					if ($category != $categories['category_id'] && $category['category_id'] == $categories['category_id'])
						$category_path_array[$category['category_id']] = $category['category_name'];
			return $category_path_array;
		}
		else
			return array();
	}

	public function getSellformFieldDescription($params)
	{
		if (isset($params['sellform_id']) && is_numeric($params['sellform_id']))
		{
			$sellform_id = (int)$params['sellform_id'];

			$sellforms = M('AllegroWebApiSellForm')->find($sellform_id);
			if ($sellforms)
			{
				return $sellforms[0]['sellform_field_desc'];
			}
			else
				return "Brak opisu do danego pola formularza.";
		}
		else
			return "Błędnie zdefiniowano zapytanie.";
	}

	public function getSellform($params)
	{
		if (isset($params['country_id']) && is_numeric($params['country_id']))
		{
			$country_id = (int)$params['country_id'];

			$category_id = 0;
			$category_ids = '0';
			if (isset($params['category_id']))
			{
				$category_id = (int)$params['category_id'];
				if ($category_id > 0 && $category_ids_array = array_keys($this->getCategoryPathArray(array('category_id' => $category_id))))
					$category_ids = implode(", ", $category_ids_array);
			}

			$sellforms = M('AllegroWebApiSellForm')->find(sql(array("
					country_id = %country_id
					AND category_id IN (0, {$category_ids})
					AND approved = 1
					AND sellform_parent_id = 0
					",
					"country_id" => $country_id,
				)), array('order' => 'category_id, sellform_position, sellform_id'));

			/// To takie częściowe rozwiązanie, bo nie powinno być warunku na sellform_parent_id (patrz kategoria 629 i marki samochodów, które podzielone są na modele)

			if ($sellforms)
			{
				$out = array();
				$names = array();
				foreach($sellforms as $k => $sellform)
				{
					/// to jest trochę kretyńskie rozwiązanie, bo zakłada unikatowość nazwy, ale w przypadku "Odzież, Obuwie, Dodatki > Odzież damska > Odzież > Bluzki > Długi rękaw" i parametru "Rozmiar" był dylemat bo ten jest w sellform zdefiniowany dla "Odzież" i dla "Długi rękaw"...
					if (isset($names[$sellform['sellform_title']]))
						$k = $out[$names[$sellform['sellform_title']]];

					$names[$sellform['sellform_title']] = $k;

					$out[$k] = $sellform->asArray();
					$options = M('AllegroWebApiSellFormOption')->find(sql(array("
							country_id = %country_id
							AND sellform_id = %sellform_id
							",
							"country_id" => $country_id,
							"sellform_id" => $sellform['sellform_id']
						)), array('order' => 'option_position'));
					if ($options)
						$out[$k]['options'] = $options->asArray();
					else
						$out[$k]['options'] = array();

					if ($sellform['sellform_id'] == 9)
					{
// 						$sellforms[$k]['form_field_type'] = 'select/option';

						$out[$k] = $sellform->asArray();
						$options = M('AllegroWebApiCountry')->db->getAssoc("SELECT country_id AS option_id, country_name AS option_name FROM `allegro_webapi_country` ORDER BY country_id");
						if ($options)
							$out[$k]['options'] = $options;
					}
				}
				return $out;
			}
			else
				return array();
		}
		else
			return array();
	}

	public function setConfiguration($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$webApiCode = $params['webApiCode'];
			$allegroLogin = $params['allegroLogin'];
			$allegroPassword = $params['allegroPassword'];
			$get_other_bids = $params['getOtherBids'];
			$comment = $params['comment'];

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
				$shopSetting = $shopSettings[0];
			else
				$shopSetting = M('AllegroWebApiShopSettings')->create();

			$shopSetting['shop_id'] = $shop_id;
			$shopSetting['nr_konfiguracji'] = $nr_konfiguracji;
			$shopSetting['web_api_code'] = $webApiCode;
			$shopSetting['login_allegro'] = $allegroLogin;
			$shopSetting['password_allegro'] = $allegroPassword;
			$shopSetting['get_other_bids'] = $get_other_bids;
			$shopSetting['comment'] = $comment;
			$shopSetting['error_counter'] = 0;

// 			return $shopSetting->asArray();

			return $shopSetting->save();
		}
		else
			return 'ERROR: Wystąpił problem z autoryzacją w Allegro Pośredniku [#319].';
	}

	public function deleteConfiguration($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			if ($nr_konfiguracji > 1)
			{
				$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
				if ($shopSettings)
				{
					$shopSetting = $shopSettings[0];

					$shopSetting['shop_id'] = $shop_id;
					$shopSetting['nr_konfiguracji'] = $nr_konfiguracji;
					$shopSetting['web_api_code'] = '';
					$shopSetting['password_allegro'] = '';
					$shopSetting['error_counter'] = 5;

					$shopSetting->save();
				}
			}
		}
	}

	public function sell($params)
	{
// 		return 'ERROR: Przerwa techniczna eCommerce24h.pl [#381].';

		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$values = $params['values'];
			$country_id = $params['country_id'];
			$product_id = (int)$params['product_id'];
			
			$products_variant_base_id = null;
			if(isset($params['products_variant_base_id']) && (int)$params['products_variant_base_id'] > 0) {
				$products_variant_base_id = (int)$params['products_variant_base_id'];
			}

			$kit_id = (int)$params['kit_id'];
			$renew = (int)$params['renew'];
			$debug = $params['debug'];

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
				$shopSettings = $shopSettings[0];

			$webApiCode = $shopSettings['web_api_code'];
			$allegroLogin = $shopSettings['login_allegro'];
			$allegroPassword = $shopSettings['password_allegro'];

			if (
					!isset($params['values'])
					|| !isset($params['country_id'])
					|| strlen(trim($webApiCode)) < 8
					|| strlen(trim($allegroLogin)) < 3
					|| strlen(trim($allegroPassword)) < 3
				)
				return 'ERROR: Nie skonfigurowano poprawnie Allegro WebAPI [#396].';

			$empty = new stdClass();
			$empty->{'fvalue-string'} = '';
			$empty->{'fvalue-int'} = '';
			$empty->{'fvalue-float'} = '';
			$empty->{'fvalue-image'} = ' ';
			$empty->{'fvalue-datetime'} = '';
// 			$empty->{'fvalue-boolean'} = false;
			$empty->{'fvalue-date'} = false;
			$empty->{'fvalue-range-int'} = array(
					'fvalue-range-int-min' => 0,
					'fvalue-range-int-max' => 0
				);
			$empty->{'fvalue-range-float'} = array(
					'fvalue-range-float-min' => 0,
					'fvalue-range-float-max' => 0
				);
			$empty->{'fvalue-range-date'} = array(
					'fvalue-range-date-min' => 0,
					'fvalue-range-date-max' => 0
				);

			$form = array();
			$image_url = '';
			foreach($values as $k => $value)
			{
				$ignore = false;

				list($tmp, $fid) = explode(":", $k);
				$field = clone $empty;
				$field->{'fid'} = (int)$fid;

				$allegroWebapiSellforms = M('AllegroWebApiSellForm')->find(sql(array(
						"country_id = %country_id
						AND sellform_id = %sellform_id
					",
						"country_id" => $country_id,
						"sellform_id" => $fid
					)));

				if ($allegroWebapiSellforms)
				{
					$sellform_res_type = $allegroWebapiSellforms[0]['sellform_res_type'];
					$form_field_type = $allegroWebapiSellforms[0]['form_field_type'];
					$sellform_min_value = $allegroWebapiSellforms[0]['sellform_min_value'];

					switch ($sellform_res_type) {
						case 1:	// input
						case 8:	// text
							$value = preg_replace('`\s+`', ' ', $value);
							$field->{'fvalue-string'} = $value;
							break;
						case 2:	// integer
							if ($form_field_type == "checkbox/option")
							{
								/// w zasadzie dane powinny być przekazywane przygotowane do użycia, ale...

								/// jeżeli przekazane są w tabelce to trzeba je posumować
								if (is_array($value))
									$value = array_sum($value);

								/// jeżeli przekazywane są po przecinku to trzeba je też posumować
								if (strpos($value, ",") !== false)
									$value = array_sum(explode(",", $value));

								if (trim($value) == "")
									$value = 0;
							}

							if ($form_field_type == "input")
							{
								if (trim($value) == "")
									$ignore = true;
							}

							$field->{'fvalue-int'} = $value;
							break;
						case 9:	// datetime
							$field->{'fvalue-datetime'} = $value;
							break;
						case 3:	// price
							if (trim($value) == "")
								$ignore = true;
							$field->{'fvalue-float'} = $value;
							break;
						case 7:	//image
							$field->{'fvalue-image'} = Core_AllegroWebApiSoapClient::resize($value);
							if (trim($value) != '' && $field->{'fvalue-image'} === false)
								return "ERROR: Błąd zdjęcia - podano adres URL do zdjęcia, który jest błędny [#411]";

							if ($image_url == '') $image_url = $value;
							break;
					}
				}
				if (!$ignore)
					$form[] = $field;
			}

// 			if ($this->shop_id == 649)
// 				return 1338316857;
// return "ERROR: " . print_r($form[12],true);
			try {
				$client = new Core_AllegroWebApiSoapClient($debug);

				webApiLog("sell", "doQuerySysStatus", array(1, $country_id, $webApiCode));
				$version = $client->doQuerySysStatus(1, $country_id, $webApiCode);

				webApiLog("sell", "doLogin", array($allegroLogin, $allegroPassword, $country_id, $webApiCode, $version['ver-key']));
				$session = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $webApiCode, $version['ver-key']);

				if ($session['user-id'] > 0)
				{
					$shopSettings['user_id'] = $user_id = $session['user-id'];
					$shopSettings['error_counter'] = 0;
					$shopSettings->save();
				}

				$local = rand();
				$private = 0; // nie prywatna

				$itemTemplateCreate = new stdClass();
				$itemTemplateCreate->{'item-template-option'} = 0;
				$itemTemplateCreate->{'item-template-name'} = '';

				$dataform = serialize($form);
				$t = array(
						'session-handle' => $session['session-handle-part'],
						'fields' => $form,
						'item-template-id' => 0,
						'local-id' => $local,
						'item-template-create' => $itemTemplateCreate
					);
				$item = $client->__soapCall('doNewAuctionExt', $t);
				webApiLog("sell", "doNewAuctionExt", array($session['session-handle-part'], $form, $private, $local));

				$t = array(
						'session-handle' => $session['session-handle-part'],
						'local-id' => $local
					);
				$check = $client->__soapCall('doVerifyItem', $t);
				webApiLog("sell", "doVerifyItem", array($session['session-handle-part'], $local));

				/// vvv logowanie prób wystawienia aukcji
				$log = array("method" => "sell", "timestamp" => date("Y-m-d H:i:s"), "sellform" => $form, "return" => $item);
				foreach($log['sellform'] as $i => $logPos)
				{
					if ($logPos->{'fid'} == 16 && $logPos->{'fvalue-image'} != '')
						$log['sellform'][$i]->{'fvalue-image'} = '... jest ...';
					if ($logPos->{'fid'} == 24 && $logPos->{'fvalue-string'} != '')
						$log['sellform'][$i]->{'fvalue-string'} = strtr($log['sellform'][$i]->{'fvalue-string'}, array("\n" => " "));

				}
				file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($log, true), FILE_APPEND);
				/// ^^^ logowanie prób wystawienia aukcji

				//if(isset($item) && isset($check) && $item['item-id'] == $check)
				if(isset($item) && isset($check) && isset($item['item-id']))
				{
					// zapisanie w bazie Allegro Pośrednika informacji o wystawionych aukcjach
					$shopAuction = M('AllegroWebApiShopAuction')->create();
					$shopAuction['shop_id'] = $shop_id;
					$shopAuction['user_id'] = $user_id;
					$shopAuction['product_id'] = $product_id;
					$shopAuction['products_variant_base_id'] = $products_variant_base_id;
					$shopAuction['kit_id'] = $kit_id;
					$shopAuction['country_id'] = $country_id;
					$shopAuction['auction_id'] = $item['item-id'];
					$shopAuction['auction_name'] = $values["fid:1"];
					$shopAuction['auction_allegro_price'] = strtr($item['item-info'], array(',' => '.', 'zł' => '', ' ' => ''));

					$price = strtr($values["fid:7"], ",", ".");
					if ($price <= 0)
						$price = strtr($values["fid:8"], ",", ".");

					$shopAuction['auction_price'] = $price;
					$shopAuction['auction_image_url'] = $image_url;
					$shopAuction['auction_payment'] = $values["fid:14"];
					$shopAuction['auction_transport'] = $values["fid:13"];
					$shopAuction['auction_items'] = $values["fid:5"];

					if ((int)$values["fid:3"] < 1000000000)	/// just in case
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
					if ($renew > 0)
					{

						$shopAuctionRenew = M('AllegroWebApiShopAuctionRenew')->create();

						$shopAuctionRenew['shop_id'] = $shop_id;
						$shopAuctionRenew['auction_id'] = $item['item-id'];
// 						$shopAuctionRenew['asar_renew'] = 1;
// 						$shopAuctionRenew['asar_renew_queue'] = 0;
// 						$shopAuctionRenew['asar_renew_repeats'] = 0;
						$shopAuctionRenew['asar_dataform'] = $dataform;

						$shopAuctionRenew->save();
					}

					return $item['item-id'];
				}
				else
				{
					file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($check, true), FILE_APPEND);
					file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($item, true), FILE_APPEND);
					return "ERROR: Bliżej nie określony błąd [#315]";
				}
			}
			catch(SoapFault $soapFault)
			{
				/// vvv logowanie prób wystawienia aukcji
				$log = array("method" => "sell", "timestamp" => date("Y-m-d H:i:s"), "sellform" => $form, "return" => $soapFault);
				foreach($log['sellform'] as $i => $logPos)
				{
					if ($logPos->{'fid'} == 16 && $logPos->{'fvalue-image'} != '')
						$log['sellform'][$i]->{'fvalue-image'} = '... jest ...';
					if ($logPos->{'fid'} == 24 && $logPos->{'fvalue-string'} != '')
						$log['sellform'][$i]->{'fvalue-string'} = strtr($log['sellform'][$i]->{'fvalue-string'}, array("\n" => " "));

				}
				file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($log, true), FILE_APPEND);
				/// ^^^ logowanie prób wystawienia aukcji

				$shopAuctionSellError = M('AllegroWebApiShopAuctionSellError')->create();
				$shopAuctionSellError['shop_id'] = $this->shop_id;
				$shopAuctionSellError['product_id'] = $product_id;
				$shopAuctionSellError['country_id'] = $country_id;
				$shopAuctionSellError['auction_name'] = $values["fid:1"];
				$shopAuctionSellError['asase_error_message'] = $soapFault->getMessage();
				$shopAuctionSellError->save();

				$errorMessage = $soapFault->getMessage();

				$return['error_text'] = $errorMessage;//." || ".print_r($soapFault,true);

				preg_match('`.*fid:\s(?<fid>[0-9]+)$`iU', $errorMessage, $out);
				if (isset($out['fid']) && (int)$out['fid'] > 0)
				{
					$fid = (int)$out['fid'];
					$return['error_fid'] = $fid;
					$fids = M('AllegroWebApiSellForm')->find("country_id = '{$country_id}' AND sellform_id = '{$fid}'");
					if ($fids)
					{
// 						$return['error_text'] = strtr($return['error_text'], array(
// 								" - fid: {$fid}" => " {$fids[0]['sellform_title']}"
// 							));
						$return['error_text'] .= "<br />Sprawdź pole: {$fids[0]['sellform_title']}.";
// 							));

						switch($fids[0]['sellform_type'])
						{
							case '1':	// input
								if (strlen($values["fid:{$fid}"]) > $fids[0]['sellform_length'])
									$return['error_text'] .= " - przekroczona maksymalna liczba znaków (wprowadzono ".strlen($values["fid:{$fid}"])." a maksimum to {$fids[0]['sellform_length']})";
								break;
						}
					}
				}

				if (trim($return['error_text']) == '')
					$return['error_text'] .= $soapFault->faultactor;

				return "ERROR: ".$return['error_text'];
			}
		}
		else
			return 'ERROR: Wystąpił problem z autoryzacją w Allegro Pośredniku [#319].';
	}

	public function selltest($params)
	{
// 		return 'ERROR: Przerwa techniczna eCommerce24h.pl [#381].';

		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$values = $params['values'];
			$country_id = $params['country_id'];
			$product_id = (int)$params['product_id'];
			$kit_id = (int)$params['kit_id'];
			$renew = (int)$params['renew'];

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
				$shopSettings = $shopSettings[0];

			$webApiCode = $shopSettings['web_api_code'];
			$allegroLogin = $shopSettings['login_allegro'];
			$allegroPassword = $shopSettings['password_allegro'];

			if (
					!isset($params['values'])
					|| !isset($params['country_id'])
					|| strlen(trim($webApiCode)) < 8
					|| strlen(trim($allegroLogin)) < 3
					|| strlen(trim($allegroPassword)) < 3
				)
				return 'ERROR: Nie skonfigurowano poprawnie Allegro WebAPI [#396].';

			$empty = new stdClass();
			$empty->{'fvalue-string'} = '';
			$empty->{'fvalue-int'} = '';
			$empty->{'fvalue-float'} = '';
			$empty->{'fvalue-image'} = ' ';
			$empty->{'fvalue-datetime'} = '';
// 			$empty->{'fvalue-boolean'} = false;
			$empty->{'fvalue-date'} = false;
			$empty->{'fvalue-range-int'} = array(
					'fvalue-range-int-min' => 0,
					'fvalue-range-int-max' => 0
				);
			$empty->{'fvalue-range-float'} = array(
					'fvalue-range-float-min' => 0,
					'fvalue-range-float-max' => 0
				);
			$empty->{'fvalue-range-date'} = array(
					'fvalue-range-date-min' => 0,
					'fvalue-range-date-max' => 0
				);

			$form = array();
			$image_url = '';
			foreach($values as $k => $value)
			{
				$ignore = false;

				list($tmp, $fid) = explode(":", $k);
				$field = clone $empty;
				$field->{'fid'} = (int)$fid;

				$allegroWebapiSellforms = M('AllegroWebApiSellForm')->find(sql(array(
						"country_id = %country_id
						AND sellform_id = %sellform_id
					",
						"country_id" => $country_id,
						"sellform_id" => $fid
					)));

				if ($allegroWebapiSellforms)
				{
					$sellform_res_type = $allegroWebapiSellforms[0]['sellform_res_type'];
					$form_field_type = $allegroWebapiSellforms[0]['form_field_type'];
					$sellform_min_value = $allegroWebapiSellforms[0]['sellform_min_value'];

					switch ($sellform_res_type) {
						case 1:	// input
						case 8:	// text
							$value = preg_replace('`\s+`', ' ', $value);
							$field->{'fvalue-string'} = $value;
							break;
						case 2:	// integer
							if ($form_field_type == "checkbox/option")
							{
								/// w zasadzie dane powinny być przekazywane przygotowane do użycia, ale...

								/// jeżeli przekazane są w tabelce to trzeba je posumować
								if (is_array($value))
									$value = array_sum($value);

								/// jeżeli przekazywane są po przecinku to trzeba je też posumować
								if (strpos($value, ",") !== false)
									$value = array_sum(explode(",", $value));

								if (trim($value) == "")
									$value = 0;
							}

							if ($form_field_type == "input")
							{
								if (trim($value) == "")
									$ignore = true;
							}

							$field->{'fvalue-int'} = $value;
							break;
						case 9:	// datetime
							$field->{'fvalue-datetime'} = $value;
							break;
						case 3:	// price
							if (trim($value) == "")
								$ignore = true;
							$field->{'fvalue-float'} = $value;
							break;
						case 7:	//image
							$field->{'fvalue-image'} = Core_AllegroWebApiSoapClient::resize($value);

							if (trim($value) != '' && $field->{'fvalue-image'} === false)
								return "ERROR: Błąd zdjęcia - podano adres URL do zdjęcia, który jest błędny [#411]";

							if ($image_url == '') $image_url = $value;
							break;
					}
				}
				if (!$ignore)
					$form[] = $field;
			}/*
			$itemTemplateCreate = new stdClass();
				$itemTemplateCreate->{'item-template-option'} = 0;
				$itemTemplateCreate->{'item-template-name'} = '';

				$dataform = serialize($form);
				$t = array(
						'session-handle' => $session['session-handle-part'],
						'fields' => $form,
						'item-template-id' => 0,
						'local-id' => $local,
						'item-template-create' => $itemTemplateCreate
					);var_dump($t);die('ttt');*/
//var_dump($form);die('t');
// 			if ($this->shop_id == 649)
// 				return 1338316857;
// return "ERROR: " . print_r($form[12],true);
			try {
				$client = new Core_AllegroWebApiSoapClient();

				webApiLog("sell", "doQuerySysStatus", array(1, $country_id, $webApiCode));
				$version = $client->doQuerySysStatus(1, $country_id, $webApiCode);

				webApiLog("sell", "doLogin", array($allegroLogin, $allegroPassword, $country_id, $webApiCode, $version['ver-key']));
				$session = $client->doLogin($allegroLogin, $allegroPassword, $country_id, $webApiCode, $version['ver-key']);

				if ($session['user-id'] > 0)
				{
					$shopSettings['user_id'] = $user_id = $session['user-id'];
					$shopSettings['error_counter'] = 0;
					$shopSettings->save();
				}

				$local = rand();
				$private = 0; // nie prywatna

				$itemTemplateCreate = new stdClass();
				$itemTemplateCreate->{'item-template-option'} = 0;
				$itemTemplateCreate->{'item-template-name'} = '';

				$dataform = serialize($form);
				$t = array(
						'session-handle' => $session['session-handle-part'],
						'fields' => $form,
						'item-template-id' => 0,
						'local-id' => $local,
						'item-template-create' => $itemTemplateCreate
					);//var_dump($t);die('ttt');
				$item = $client->__soapCall('doCheckNewAuctionExt', $t);
				//webApiLog("sell", "doCheckNewAuctionExt", array($session['session-handle-part'], $form, $private, $local));
				//var_dump($item);die('test');
				$t = array(
						'session-handle' => $session['session-handle-part'],
						'local-id' => $local
					);
				$check = $client->__soapCall('doVerifyItem', $t);
				webApiLog("sell", "doVerifyItem", array($session['session-handle-part'], $local));

				/// vvv logowanie prób wystawienia aukcji
				$log = array("method" => "sell", "timestamp" => date("Y-m-d H:i:s"), "sellform" => $form, "return" => $item);
				foreach($log['sellform'] as $i => $logPos)
				{
					if ($logPos->{'fid'} == 16 && $logPos->{'fvalue-image'} != '')
						$log['sellform'][$i]->{'fvalue-image'} = '... jest ...';
					if ($logPos->{'fid'} == 24 && $logPos->{'fvalue-string'} != '')
						$log['sellform'][$i]->{'fvalue-string'} = strtr($log['sellform'][$i]->{'fvalue-string'}, array("\n" => " "));

				}
				file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($log, true), FILE_APPEND);
				/// ^^^ logowanie prób wystawienia aukcji
return $item;
				//if(isset($item) && isset($check) && $item['item-id'] == $check)
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
					$shopAuction['auction_allegro_price'] = strtr($item['item-info'], array(',' => '.', 'zł' => '', ' ' => ''));

					$price = strtr($values["fid:7"], ",", ".");
					if ($price <= 0)
						$price = strtr($values["fid:8"], ",", ".");

					$shopAuction['auction_price'] = $price;
					$shopAuction['auction_image_url'] = $image_url;
					$shopAuction['auction_payment'] = $values["fid:14"];
					$shopAuction['auction_transport'] = $values["fid:13"];
					$shopAuction['auction_items'] = $values["fid:5"];

					if ((int)$values["fid:3"] < 1000000000)	/// just in case
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

					//$shopAuction->save();

					//M('AllegroWebApiShopAuction')->db->execQuery("UPDATE allegro_shop_auction SET auction_hidden = 1 WHERE shop_id = {$shop_id} AND auction_active = 0 AND product_id = '{$product_id}' AND auction_id <> '{$shopAuction['auction_id']}';");

					/// Tutan zapisywane są dane w związku z RENEW
					if ($renew > 0)
					{

						$shopAuctionRenew = M('AllegroWebApiShopAuctionRenew')->create();

						$shopAuctionRenew['shop_id'] = $shop_id;
						$shopAuctionRenew['auction_id'] = $item['item-id'];
// 						$shopAuctionRenew['asar_renew'] = 1;
// 						$shopAuctionRenew['asar_renew_queue'] = 0;
// 						$shopAuctionRenew['asar_renew_repeats'] = 0;
						$shopAuctionRenew['asar_dataform'] = $dataform;

						//$shopAuctionRenew->save();
					}

					return $item['item-id'];
				}
				else
				{
					file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($check, true), FILE_APPEND);
					file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($item, true), FILE_APPEND);
					return "ERROR: Bliżej nie określony błąd [#315]";
				}
			}
			catch(SoapFault $soapFault)
			{ //var_dump($soapFault);
				/// vvv logowanie prób wystawienia aukcji
				$log = array("method" => "sell", "timestamp" => date("Y-m-d H:i:s"), "sellform" => $form, "return" => $soapFault);
				foreach($log['sellform'] as $i => $logPos)
				{
					if ($logPos->{'fid'} == 16 && $logPos->{'fvalue-image'} != '')
						$log['sellform'][$i]->{'fvalue-image'} = '... jest ...';
					if ($logPos->{'fid'} == 24 && $logPos->{'fvalue-string'} != '')
						$log['sellform'][$i]->{'fvalue-string'} = strtr($log['sellform'][$i]->{'fvalue-string'}, array("\n" => " "));

				}
				file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($log, true), FILE_APPEND);
				/// ^^^ logowanie prób wystawienia aukcji

				$shopAuctionSellError = M('AllegroWebApiShopAuctionSellError')->create();
				$shopAuctionSellError['shop_id'] = $this->shop_id;
				$shopAuctionSellError['product_id'] = $product_id;
				$shopAuctionSellError['country_id'] = $country_id;
				$shopAuctionSellError['auction_name'] = $values["fid:1"];
				$shopAuctionSellError['asase_error_message'] = $soapFault->getMessage();
				//$shopAuctionSellError->save();

				$errorMessage = $soapFault->getMessage();

				$return['error_text'] = $errorMessage;//." || ".print_r($soapFault,true);

				preg_match('`.*fid:\s(?<fid>[0-9]+)$`iU', $errorMessage, $out);
				if (isset($out['fid']) && (int)$out['fid'] > 0)
				{
					$fid = (int)$out['fid'];
					$return['error_fid'] = $fid;
					$fids = M('AllegroWebApiSellForm')->find("country_id = '{$country_id}' AND sellform_id = '{$fid}'");
					if ($fids)
					{
// 						$return['error_text'] = strtr($return['error_text'], array(
// 								" - fid: {$fid}" => " {$fids[0]['sellform_title']}"
// 							));
						$return['error_text'] .= "<br />Sprawdź pole: {$fids[0]['sellform_title']}.";
// 							));

						switch($fids[0]['sellform_type'])
						{
							case '1':	// input
								if (strlen($values["fid:{$fid}"]) > $fids[0]['sellform_length'])
									$return['error_text'] .= " - przekroczona maksymalna liczba znaków (wprowadzono ".strlen($values["fid:{$fid}"])." a maksimum to {$fids[0]['sellform_length']})";
								break;
						}
					}
				}

				if (trim($return['error_text']) == '')
					$return['error_text'] .= $soapFault->faultactor;

				return "ERROR: ".$soapFault;//$return['error_text'];
			}
		}
		else
			return 'ERROR: Wystąpił problem z autoryzacją w Allegro Pośredniku [#319].';
	}

	public function getNewPayments()
	{
		if ($this->authenticated)
		{
			$out = array();

			$postbuyformpayments = M('AllegroWebApiPostbuyformpayment')->findBySql(sql(array("
					SELECT
						allegro_postbuyformpayment.*
					FROM
						allegro_postbuyformpayment
							INNER JOIN allegro_postbuyformdata USING(postbuyform_id)
					WHERE
						allegro_postbuyformdata.shop_id = %shop_id
						AND allegro_postbuyformpayment.postbuyformpayment_get_by_shop = 0
						AND postbuyformpayment_recive_date > NOW() - INTERVAL 10 day;
					",
					"shop_id" => $this->shop_id
					)));

			if ($postbuyformpayments)
			{
				foreach($postbuyformpayments as $i => $postbuyformpayment)
				{
					$out[$i] = array(
							'postbuyform_id' => $postbuyformpayment['postbuyform_id'],
							'postbuyformpayment_type' => $postbuyformpayment['postbuyformpayment_type'],
							'postbuyformpayment_status' => $postbuyformpayment['postbuyformpayment_status'],
							'postbuyformpayment_amount' => $postbuyformpayment['postbuyformpayment_amount'],
							'postbuyformpayment_recive_date' => $postbuyformpayment['postbuyformpayment_recive_date']
						);

					$postbuyformpayment['postbuyformpayment_get_by_last_datetime'] = date('Y-m-d H:i:s');
					$postbuyformpayment->save();
				}
			}

			return $out;
		}
		else
			return false;
	}

	public function getNewOrders($params)
	{
		if ($this->authenticated)
		{
			$out = array(); $dbg = false;

			// if(!isset($params['debug'])) {
			// 	die;
			// }

			$postbuyform_id = $params['postbuyform_id'];

			/// standardowo ta funkcja wywoływana jest bez parametrów, a możliwość wywołania jej z parametrem jest dorobiona tylko dla celów odpluskwiania
			if ($postbuyform_id == 0)
			{
				$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find(sql(array("
							shop_id = %shop_id
							AND postbuyform_get_by_shop = 0
						",
						"shop_id" => $this->shop_id
						)));
			}
			else
			{	$dbg = true;
				$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find(sql(array("
							shop_id = %shop_id
							AND postbuyform_id = %postbuyform_id
						",
						"shop_id" => $this->shop_id,
						"postbuyform_id" => $postbuyform_id
						)));//var_dump($postbuyformdatas,'postbuyformdatas');$out['t']=$postbuyformdatas;
			}
			if(isset($params['dbg']) && $params['dbg'] == 1)
			{
				$dbg = true;
				
				$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find(sql(array("
							shop_id = %shop_id
							AND postbuyform_get_by_last_datetime > '2013-04-28 10:00:00'
							AND postbuyform_get_by_last_datetime < '2013-04-30 10:00:00'
							
						",
						"shop_id" => $this->shop_id//,
						//"postbuyform_id" => $postbuyform_id
						)));
				
			}

			$countries = array();

			if ($postbuyformdatas)
			{
// 				return $postbuyformdatas->asArray();
				foreach($postbuyformdatas as $i => $postbuyformdata)
				{
					$postbuyformadrs = $postbuyformdata->AllegroWebApiPostbuyformadr;
					if (!$postbuyformadrs)
						continue;

					$get_other_bids = 1;

					$seller_user_id = 0;

					if($postbuyformdata['user_id'] > 0) {
						$seller_user_id = $postbuyformdata['user_id'];						
					} else {
						$AllegroWebApiPostbuyformitem = $postbuyformdata->AllegroWebApiPostbuyformitem;
						$auction_id = 0;
						if($AllegroWebApiPostbuyformitem) {
							$auction_id = $AllegroWebApiPostbuyformitem[0]['postbuyformit_auction_id'];

							if($auction_id) {
								$auction = M('AllegroWebApiShopAuction')->findByAuctionId($auction_id);
								
								if($auction) {
									$seller_user_id = $auction[0]['user_id'];
								}
							}
						}
					}
					

					if($seller_user_id > 0) {
						$shopSettings = M('AllegroWebApiShopSettings')->first(sql(array('shop_id = %shop_id  AND user_id = %user_id', 'shop_id' => $postbuyformdata['shop_id'], 'user_id' => $seller_user_id)));	
					} else {
						$shopSettings = M('AllegroWebApiShopSettings')->first(sql(array('shop_id = %shop_id', 'shop_id' => $postbuyformdata['shop_id'])));
					}
					
					if ($shopSettings['get_other_bids'] == 0)
						$get_other_bids = 0;

					// if($params['debug']) {
					// 	return array($shopSettings->asArray(), $postbuyformdata['shop_id'], $seller_user_id);
					// }

					$out[$i] = $postbuyformdata->asArray();

					if (trim($out[$i]['postbuyform_shipment_title']) == '' || M('AllegroWebApiShipment')->isPaczkomatyShipping($postbuyformdata['shipment_id'])) {
						if ((int)$postbuyformdata['shipment_id'] > 0) {
							$shipments = M('AllegroWebApiShipment')->find("country_id = '1' AND shipment_id = '{$postbuyformdata['shipment_id']}'");
							if ($shipments) {
								$out[$i]['postbuyform_shipment_title'] = $shipments[0]['shipment_name'];
								$out[$i]['postbuyform_shipment_type'] = $shipments[0]['shipment_type'];
								if(M('AllegroWebApiShipment')->isPaczkomatyShipping($postbuyformdata['shipment_id']) && trim($postbuyformdata['postbuyform_shipment_title']) != '') {
									$out[$i]['postbuyform_shipment_title'] = $shipments[0]['shipment_name'].' ['.trim($postbuyformdata['postbuyform_shipment_title']).']';
								}
							}
						} else {
							$out[$i]['postbuyform_shipment_title'] = "Brak danych";
						}
					}
					/*

					if (trim($out[$i]['postbuyform_shipment_title']) == '')
					{
						if ((int)$postbuyformdata['shipment_id'] > 0)
						{
							$shipments = M('AllegroWebApiShipment')->find("country_id = '1' AND shipment_id = '{$postbuyformdata['shipment_id']}'");
							if ($shipments)
							{
								$out[$i]['postbuyform_shipment_title'] = $shipments[0]['shipment_name'];
								$out[$i]['postbuyform_shipment_type'] = $shipments[0]['shipment_type'];
							}
						}
						else
						{
							$out[$i]['postbuyform_shipment_title'] = "Brak danych";
						}
					}*/

					$out[$i]['shipment']  = array();
					$out[$i]['invoice']  = array();

					foreach($postbuyformadrs as $postbuyformadr)
					{
						if (!isset($countries[$postbuyformadr['postbuyformadr_country']]))
							$countries[$postbuyformadr['postbuyformadr_country']] = M('AllegroWebApiCountry')->getCountryName($postbuyformadr['postbuyformadr_country']);

						if ($postbuyformadr['postbuyformadr_type'] == 'shipment')
						{
							$out[$i]['shipment'] = $postbuyformadr->asArray();
							$out[$i]['shipment']['postbuyformadr_country'] = $countries[$postbuyformadr['postbuyformadr_country']];
						}

						if ($postbuyformadr['postbuyformadr_type'] == 'invoice')
						{
							$out[$i]['invoice'] = $postbuyformadr->asArray();
							$out[$i]['invoice']['postbuyformadr_country'] = $countries[$postbuyformadr['postbuyformadr_country']];
						}
					}

					$out[$i]['payment']  = array();
					$paymentform = $postbuyformdata->AllegroWebApiPostbuyformpayment;
					if ($paymentform)
						$out[$i]['payment'] = $paymentform->asArray();

					$postbuyformitems = $postbuyformdata->AllegroWebApiPostbuyformitem;

					$postbuyformitems = $postbuyformdata->AllegroWebApiPostbuyformitem;
					if (!$postbuyformitems)
					{
						$postbuyformdata['postbuyform_get_by_shop'] = 2; if(!$dbg || $params['api'] != 'v2') $postbuyformdata->save();
						unset($out[$i]);
						continue;
					}

					$there_are_from_our_system = false;
					foreach($postbuyformitems as $j => $postbuyformitem)
					{
						if ($get_other_bids == 0)
						{
							$auctions = M('AllegroWebApiShopAuction')->findByAuctionId($postbuyformitem['postbuyformit_auction_id']);
							if ($auctions && $auctions[0]['other_system'] == 0)
								$there_are_from_our_system = true;
						}


						$out[$i]['items'][$j]['item'] = $postbuyformitem->asArray();

						$bids = M('AllegroWebApiBid')->findByAuctionIdAndUserId($postbuyformitem['postbuyformit_auction_id'], $postbuyformdata['postbuyform_buyer_id']);
						$bid = $bids[0];

						$shopAuction =  $bid->AllegroWebApiShopAuction;
						$user = $bid->AllegroWebApiUser;
						if ($shopAuction && $user)
						{
							$out[$i]['items'][$j]['auction'] = $shopAuction->asArray();
							$out[$i]['user'] = $user->asArray();
						}
						else
						{
							
							if(!$user)
								$postbuyformdata['postbuyform_get_by_shop'] = 6; 
							if(!$shopAuction)
								$postbuyformdata['postbuyform_get_by_shop'] = 3; 

							if(!$postbuyformitem['postbuyformit_auction_id'])
								$postbuyformdata['postbuyform_get_by_shop'] = 7; 

							if(!$dbg || $params['api'] != 'v2') $postbuyformdata->save();
							unset($out[$i]);
							continue 2;
						}


						if (!isset($countries[$out[$i]['user']['country_id']]))
							$countries[$out[$i]['user']['country_id']] = M('AllegroWebApiCountry')->getCountryName($out[$i]['user']['country_id']);

						$out[$i]['user']['country'] = $countries[$out[$i]['user']['country_id']];
					}

					if ($get_other_bids == 0 && $there_are_from_our_system == false)
					{
						$postbuyformdata['postbuyform_get_by_shop'] = 4; if(!$dbg) $postbuyformdata->save();
						unset($out[$i]);
					}

					$postbuyformdata['postbuyform_get_by_last_datetime'] = date('Y-m-d H:i:s');
					if(!$dbg || $params['api'] != 'v2') $postbuyformdata->save();
				}
			}

			return $out;
		}
		else
			return false;
	}

	public function setPostbuyformAsSent($params){
        if ($this->authenticated){

           return M('AllegroWebApiPostbuyformdata')->setPostbuyformStatus($params['postbuyform_id'], $params['shop_id'], 1);
        }
    }

	/**
	 * Ustawia domyślny komentarz dla skelpu (komentarz będący odpowiedzią na pozytywny komentarz od klienta)
	 */
	public function setDefaultComment($params)
	{
		if ($this->authenticated)
		{
			$params['comment'] = trim($params['comment']);
			if ($params['comment'] != '')
			{
				$shop_id = $this->shop_id;

				$nr_konfiguracji = (int)$params['nrKonfiguracji'];
				if ($nr_konfiguracji <= 0)
					$nr_konfiguracji = 1;

				$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
				if ($shopSettings)
					$shopSetting = $shopSettings[0];
				else
					$shopSetting = M('AllegroWebApiShopSettings')->create();

				$shopSetting['comment'] = $params['comment'];
				$shopSetting['shop_id'] = $shop_id;
				$shopSetting['nr_konfiguracji'] = $nr_konfiguracji;

				$shopSetting->save();
			}
			else
				return false;

			return true;
		}
		else
			return false;
	}

	/**
	 * Ukrywa (takie prawie usuwa) aukcje, których użytkownik, już nie chce oglądać
	 */
	public function setHiddenAuctions($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			if (isset($params['auction_ids']) && is_array($params['auction_ids']))
				$auction_ids = $params['auction_ids'];
			else
				return false;

			foreach($auction_ids as $i => $auction_id)
				$auction_ids[$i] = $auction_id;

			 M('AllegroWebApiShopAuction')->db->execQuery("UPDATE allegro_shop_auction SET auction_hidden = 1 WHERE shop_id = {$shop_id} AND auction_id IN (".implode(',', $auction_ids).");");
			return true;
		}
		else
			return false;
	}

	/**
	 * Pobiera listę trwających aukcji
	 */
	public function getAuctionList($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$active = 1;
			if (isset($params['active']))
				$active = (int)$params['active'];

			$limit = 500;
			if (isset($params['limit']))
				$limit = (int)$params['limit'];

			$hidden = 0;
			if (isset($params['hidden']))
				$hidden = (int)$params['hidden'];

			$date_start = '';
			if (isset($params['date_start']) && strtotime($params['date_start']) > 0)
				$date_start = date("Y-m-d H:i:s", strtotime($params['date_start']));

			/// Chodzi o to, aby jeżeli ktoś sobie zrobi dłuższą przerwę w korzystaniu z naszego softu, to żeby nie pojawiło się dużo starych wiszących aukcji w historii, których nijak nie da się usunąć.
			M('AllegroWebApiShopAuction')->db->execQuery(sql(array("UPDATE allegro_shop_auction SET auction_active = 0 WHERE shop_id = %shop_id AND date_stop < now() - interval 3 day AND auction_active = 1;", "shop_id" => $shop_id)));

			$sql = sql(array("
					SELECT
						asa.asa_id,
						asa.country_id,
						asa.product_id,
						asa.kit_id,
						asa.auction_id,
						asa.auction_name,
						asa.auction_items,
						asa.date_start,
						asa.date_stop,
						COUNT(ab.ab_id) AS auction_bid_count
					FROM
						allegro_shop_auction AS asa
							LEFT JOIN allegro_bid AS ab ON ab.country_id = asa.country_id AND ab.auction_id = asa.auction_id
					WHERE
						asa.auction_active = %active
						AND asa.shop_id = %shop_id
						AND asa.user_id = %user_id
						".($hidden >= 0 ? "AND asa.auction_hidden = %auction_hidden" : "")."
						".($date_start != '' ? "AND asa.date_start >= %date_start" : "")."
					GROUP BY
						asa.asa_id,
						asa.country_id,
						asa.product_id,
						asa.kit_id,
						asa.auction_id,
						asa.auction_name,
						asa.date_start,
						asa.date_stop
					ORDER BY
						asa.date_start DESC
					LIMIT
						%limit
				;",
					"shop_id" => $shop_id,
					"user_id" => $user_id,
					"limit" => $limit,
					"active" => $active,
					"auction_hidden" => $hidden,
					"date_start" => $date_start
				));
// return $sql;
			$auctions = M('AllegroWebApiShopAuction')->db->getAssoc($sql);

			if ($auctions)
				return $auctions;
			else
				return array();
		}
		else
			return false;
	}

	/**
	 * Pobiera listę wskazanych aukcji
	 */
	public function getAuctionListForProducts($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$products_ids = $params['products_ids'];
			if (!$products_ids || !is_array($products_ids))
				$products_ids = array();

			$_products_ids = array();
			foreach($products_ids as $products_id)
			{
				$_products_ids[] = (int)$products_id;
			}

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$sql = sql(array("
				SELECT
					*
				FROM
				(
					SELECT
						product_id,
						asa_id,
						shop_id,
						country_id,
						auction_id,
						auction_name,
						auction_image_url,
						auction_payment,
						auction_transport,
						auction_items,
						date_start,
						date_stop,
						auction_active,
						auction_price,
						auction_hidden
					FROM
						allegro_shop_auction
					WHERE
						shop_id = %shop_id
						AND user_id = %user_id
						AND product_id IN (".implode(', ', $_products_ids).")
					ORDER BY
						asa_id DESC
				) t
					GROUP BY
						product_id;
				",
					"shop_id" => $shop_id,
					"user_id" => $user_id
				));

			$auctions = M('AllegroWebApiShopAuction')->db->getIdAssoc($sql);

			if ($auctions)
				return $auctions;
			else
				return array();
		}
		else
			return false;
	}

	/**
	 * Pobiera listę wskazanych aukcji
	 */
	public function getAuctionListForProductsWithKit($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$products_ids = $params['products_ids'];
			if (!$products_ids || !is_array($products_ids))
				$products_ids = array();

			if (isset($params['active']))
				$active = (int)$params['active'];

			$_products_ids = array();
			foreach($products_ids as $products_id)
			{
				$_products_ids[] = (int)$products_id;
			}

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$sql = sql(array("
				SELECT
					*
				FROM
				(
					SELECT
						product_id,
						kit_id,
						asa_id,
						shop_id,
						country_id,
						auction_id,
						auction_name,
						auction_image_url,
						auction_payment,
						auction_transport,
						auction_items,
						date_start,
						date_stop,
						auction_active,
						auction_price,
						auction_hidden
					FROM
						allegro_shop_auction
					WHERE
						shop_id = %shop_id
						AND user_id = %user_id
						".($_products_ids ? "AND product_id IN (".implode(', ', $_products_ids).")" : "")."
						".(isset($active) ? "AND auction_active = {$active}" : "")."
					ORDER BY
						date_stop DESC
				) t
					GROUP BY
						product_id,
						kit_id
					;
				",
					"shop_id" => $shop_id,
					"user_id" => $user_id
				));

			$auctions = M('AllegroWebApiShopAuction')->db->getAssoc($sql);

			if ($auctions)
				return $auctions;
			else
				return array();
		}
		else
			return false;
	}

	/**
	 * Pobiera listę wznawianych aukcji
	 */
	public function getAuctionRenewList($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$sql = sql(array("
				SELECT
					asar.auction_id,
					asar.asar_counter AS counter,
					asa.product_id,
					asa.country_id,
					asa.auction_name,
					asa.date_start,
					asa.date_stop
				FROM
					allegro_shop_auction_renew AS asar
						INNER JOIN allegro_shop_auction AS asa USING(auction_id)
				WHERE
					asar.shop_id = %shop_id
					AND asa.user_id = %user_id
					AND (asar.asar_renew = 1 OR asar.asar_renew_queue = 1)
				ORDER BY
					asar_id DESC;
				",
					"shop_id" => $shop_id,
					"user_id" => $user_id
				));

			$auctions = M('AllegroWebApiShopAuction')->db->getIdAssoc($sql);

			if ($auctions)
				return $auctions;
			else
				return array();
		}
		else
			return false;
	}

	/**
	 * Pobiera listę wznawianych aukcji (błędów z ostatnich 30dni)
	 */
	public function getAuctionRenewErrorsList($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$sql = sql(array("
				SELECT
					asar.auction_id,
					asar.asar_counter AS counter,
					asa.product_id,
					asa.country_id,
					asa.auction_name,
					asa.date_start,
					asa.date_stop
				FROM
					allegro_shop_auction_renew AS asar
						INNER JOIN allegro_shop_auction AS asa USING(auction_id)
				WHERE
					asar.shop_id = %shop_id
					AND asa.user_id = %user_id
					AND asar.asar_renew_repeats > 0
					AND asar.update_timestamp > NOW() - INTERVAL 30 DAY
				ORDER BY
					asar_id DESC;
				",
					"shop_id" => $shop_id,
					"user_id" => $user_id
				));

			$auctions = M('AllegroWebApiShopAuction')->db->getIdAssoc($sql);

			if ($auctions)
				return $auctions;
			else
				return array();
		}
		else
			return false;
	}

	/**
	 * Pobiera listę wznawianych aukcji (błędów z ostatnich 30dni)
	 */
	public function stopRenew($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$auction_ids = $params['auction_ids'];
			if (!$auction_ids || !is_array($auction_ids))
				$auction_ids = array();

			$_auction_ids = array();
			foreach($auction_ids as $auction_id)
			{
				$_auction_ids[] = $auction_id;
			}

			$sql = sql(array("
				UPDATE
					allegro_shop_auction_renew
				SET
					asar_renew = 0,
					update_timestamp = NOW(),
					asar_renew_queue = 0
				WHERE
					shop_id = %shop_id
					AND auction_id IN (".implode(', ', $_auction_ids).");
				",
					"shop_id" => $shop_id
				));

			$auctions = M('AllegroWebApiShopAuctionRenew')->db->execQuery($sql);

			return true;
		}
		else
			return false;
	}

	/**
	 * Służy do oznaczania zakupów niepotwierdzonych jako ignorowane
	 */
	public function deleteNotConfirmedBids($params)
	{
		$ab_id = $params['ab_id'];

		if ($this->authenticated && $ab_id > 0)
		{
			$shop_id = $this->shop_id;

			$bids = M('AllegroWebApiBid')->find("ab_id = {$ab_id} AND shop_id = {$shop_id}");
			if ($bids)
			{
				$bids[0]['ab_ignore'] = 1;
				$bids[0]->save();
			}
		}
		else
			return false;
	}

	/**
	 * Zaznacza w bazie pośrednika, że dany produkt skończył się już w sklepie i trzeba zakończyć dla niego aukcje
	 */
	public function setFinishProduct($params)
	{		
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;
			$product_id = (int)$params['products_id'];
			$kit_id = (int)$params['kit_id'];

			if ($product_id > 0)
			{
				$productsFinish = M('ShopProductFinish')->findOrCreate("shop_id = '{$shop_id}' AND product_id = '{$product_id}' AND kit_id = '{$kit_id}' AND spf_used = 0");
				$productsFinish[0]['shop_id'] = $shop_id;
				$productsFinish[0]['product_id'] = $product_id;
				$productsFinish[0]['kit_id'] = $kit_id;
				$productsFinish[0]->save();

				return true;
			}
		}

		return false;
	}

	/**
	 * Pobiera listę zakupów, które jeszcze nie zostały potwierdzone (nie zeszły do sklepów)
	 */
	public function getUnconfirmedBids($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$out = array();

			$bids = M('AllegroWebApiBid')->find(sql(array(
				'ab_quantity - ab_quantity_payed > 0 AND shop_id = %shop_id AND ab_ignore = 0 AND ab_bid_date > NOW() - INTERVAL 3 MONTH', "shop_id" => $shop_id)),
				array('order' => 'ab_bid_date'));
// 			return $bids->asArray();
			if ($bids)
			{
				$out = $bids->asArray();
				foreach($bids as $k => $bid)
				{
					$auction = $bid->AllegroWebApiShopAuction;
					if ($auction)
						$out[$k]['auction'] = $auction->asArray();
					else
						$out[$k]['auction'] = array();

					$user = $bid->AllegroWebApiUser;
					if ($user)
						$out[$k]['user'] = $user->asArray();
					else
						$out[$k]['user'] = array();

					$params = $auction->AllegroWebApiShopAuctionParam;
					if ($params)
						$out[$k]['params'] = $params->asArray();
					else
						$out[$k]['params'] = array();

					if (!$out[$k]['auction'] || !$out[$k]['user'] || !$out[$k]['params'] || $auction['user_id'] != $user_id)
						unset($out[$k]);
				}
			}

			return $out;
		}
		else
			return false;
	}

	public function getUnconfirmedBids222()
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$out = array();

			$bids = M('AllegroWebApiBid')->find(sql(array('ao_id IS NULL AND shop_id = %shop_id', "shop_id" => $shop_id)), array('order' => 'ab_bid_date', 'limit' => 40));
			if ($bids)
			{
				$out = $bids->asArray();
				$out['sql'] = sql(array('ao_id IS NULL AND shop_id = %shop_id', "shop_id" => $shop_id));
				foreach($bids as $k => $bid)
				{
					$auction = $bid->AllegroWebApiShopAuction;
					$out[$k]['auction'] = $auction->asArray();
					$out[$k]['user'] = $bid->AllegroWebApiUser->asArray();
					$out[$k]['params'] = $auction->AllegroWebApiShopAuctionParam->asArray();
				}
			}

			return $out;
		}
		else
			return false;
	}

	/**
	 * Symuluje potwierdzenie zakupu przez klienta (symuluje wypełnienie FOD)
	 */
	public function confirmForCustomer($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			if (isset($params['ab_id']))
				$ab_id = $params['ab_id'];

			if (isset($params['postage']))
				$postage = (int)$params['postage'];

			if (isset($params['payment']))
				$payment = (int)$params['payment'];

			if ($ab_id > 0)
			{
				$allegroBids = M('AllegroWebApiBid')->find($ab_id);
				if ($allegroBids && $allegroBids[0]['shop_id'] == $shop_id && (int)$allegroBids[0]['ab_quantity'] > $allegroBids[0]['ab_quantity_payed'])
				{
					$allegroBid = $allegroBids[0];
					$auction = $allegroBid->AllegroWebApiShopAuction;
					$user = $allegroBid->AllegroWebApiUser;
					if ($user)
					{
						$postages = M('AllegroWebApiShopAuctionParam')->find($postage);
						if ($postages)
							$postage = $postages[0];
						else
						{
							$postage['asap_name'] = "brak danych";
							$postage['asap_price'] = 0;
						}

						$payments = M('AllegroWebApiShopAuctionParam')->find($payment);
						if ($payments)
						{
							$payment = $payments[0];
						}
						else
						{
							$payment['asap_name'] = "brak danych";
						}

						$user_id = (int)$user['user_id'];

						$data = M('AllegroWebApiPostbuyformdata')->create();
						$postbuyform_id = $data['postbuyform_id'] = M('AllegroWebApiPostbuyformdata')->getNext_postbuyformadr_id();
						$data['shop_id'] = $shop_id;
						$data['postbuyform_buyer_id'] = $user_id;
						$data['postbuyform_amount'] = $auction['auction_price'] * ($allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed']) + $postage['asap_price'];
						$data['postbuyform_postage_amount'] = $postage['asap_price'];
						$data['postbuyform_shipment_title'] = $postage['asap_name'];

						if ($payment['asap_name'] == 'Płatność przy odbiorze')
							$data['postbuyform_shipment_type'] = 2;
						else
							$data['postbuyform_shipment_type'] = 0;

						$data['postbuyform_invoice_option'] = 0;
						$data['postbuyform_msg_to_seller'] = 'Brak PzA! Potwierdzone przez obsługę sklepu!';
						$data['postbuyform_pay_type'] = $payment['asap_name'];
						$data->save();

						$item = M('AllegroWebApiPostbuyformitem')->create();
						$item['postbuyform_id'] = $postbuyform_id;
						$item['postbuyformit_amount'] = $auction['auction_price'] * ($allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed']);
						$item['postbuyformit_quantity'] = $allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed'];
						$item['postbuyformit_auction_id'] = $allegroBid['auction_id'];
						$item['postbuyformit_title'] = $auction['auction_name'];
						$item['postbuyformit_country'] = $auction['country_id'];
						$item['postbuyformit_price'] = $auction['auction_price'];
						$item->save();

						$allegroBid['ab_quantity_payed'] += $allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed'];
						$allegroBid->save();

						$adr = M('AllegroWebApiPostbuyformadr')->create();
						$adr['postbuyform_id'] = $postbuyform_id;
						$adr['postbuyformadr_type'] = 'shipment';
						$adr['postbuyformadr_country'] = $user['country_id'];
						$adr['postbuyformadr_street'] = $user['street'];
						$adr['postbuyformadr_postcode'] = $user['postcode'];
						$adr['postbuyformadr_city'] = $user['city'];
						$adr['postbuyformadr_full_name'] = "{$user['first_name']} {$user['last_name']}";
						$adr['postbuyformadr_company'] = $user['company'];

						$phone = array();
						if (trim($user['phone']) != '')
							$phone[] = $user['phone'];
						if (trim($user['phone2']) != '')
							$phone[] = $user['phone2'];

						$adr['postbuyformadr_phone'] = implode(",", $phone);
						$adr['postbuyformadr_nip'] = '';
						$adr->save();

						return true;
					}
					else
						return 'Brak danych licytującego - poczekaj godzinę przed ponowieniem próby';
				}
				else
					return 'Brak uprawnień lub próba ponownego zatwierdzenia zamówienia';
			}
			else
				return 'Nie znaleziono oferty';
		}
		else
			return 'Brak uprawnień';
	}

	/**
	 * Symuluje potwierdzenie zakupu przez klienta (z uwzględnieniem łączenia w zestawy)
	 */
	public function confirmForCustomerPackage($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			if (!isset($params['bids']) || !is_array($params['bids']))
				return 'Przekazana tablica <strong>bids</strong> nie istnieje lub jest pusta!';

			$bids = array();
			foreach($params['bids'] as $bid)
			{
				$user_id = M('AllegroWebApiBid')->db->getValue("SELECT user_id FROM allegro_bid WHERE ab_id = ".$bid['ab_id'].";");
				$bids["{$user_id}-{$bid['ab_id']}"] = $bid;
			}

			ksort($bids);
// 			return $bids;

			$old_user_id = 0;

			foreach($bids as $bid)
			{
				if (isset($bid['ab_id']))
					$ab_id = $bid['ab_id'];

				if (isset($bid['postage']))
					$postage = (int)$bid['postage'];

				if (isset($bid['payment']))
					$payment = (int)$bid['payment'];

				if ($ab_id > 0)
				{
					$allegroBids = M('AllegroWebApiBid')->find($ab_id);
					if ($allegroBids && $allegroBids[0]['shop_id'] == $shop_id && (int)$allegroBids[0]['ab_quantity'] > $allegroBids[0]['ab_quantity_payed'])
					{
						$allegroBid = $allegroBids[0];
						$auction = $allegroBid->AllegroWebApiShopAuction;
						$user = $allegroBid->AllegroWebApiUser;
						if ($user)
						{
							$postages = M('AllegroWebApiShopAuctionParam')->find($postage);
							if ($postages)
								$postage = $postages[0];
							else
							{
								$postage['asap_name'] = "brak danych";
								$postage['asap_price'] = 0;
							}

							$payments = M('AllegroWebApiShopAuctionParam')->find($payment);
							if ($payments)
							{
								$payment = $payments[0];
							}
							else
							{
								$payment['asap_name'] = "brak danych";
							}

							$user_id = (int)$user['user_id'];

							if ($old_user_id != $user_id)
							{
								$data = M('AllegroWebApiPostbuyformdata')->create();
								$postbuyform_id = $data['postbuyform_id'] = M('AllegroWebApiPostbuyformdata')->getNext_postbuyformadr_id();
								$data['shop_id'] = $shop_id;
								$data['postbuyform_buyer_id'] = $user_id;
								$data['postbuyform_amount'] = $auction['auction_price'] * ($allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed']) + $postage['asap_price'];

								// $asap['asap_price_add'] = $itemInfoPostageOption->{'postage-amount-add'};

								$postbuyform_postage_amount = 0;

								$postbuyform_postage_amount = $postage['asap_price'];
								if($postage['asap_price_add'] > 0 && $allegroBid['ab_quantity'] > 1) {
									$asap_price_add = ($allegroBid['ab_quantity'] - 1) * $postage['asap_price_add'];
									$postbuyform_postage_amount = $postbuyform_postage_amount + $asap_price_add;
								}								

								$data['postbuyform_postage_amount'] = $postbuyform_postage_amount;
								$data['postbuyform_shipment_title'] = $postage['asap_name'];

								if ($payment['asap_name'] == 'Płatność przy odbiorze')
									$data['postbuyform_shipment_type'] = 2;
								else
									$data['postbuyform_shipment_type'] = 0;

								$data['postbuyform_invoice_option'] = 0;
								$data['postbuyform_msg_to_seller'] = 'Brak PzA! Potwierdzone przez obsługę sklepu!';
								$data['postbuyform_pay_type'] = $payment['asap_name'];
								$data->save();
							}
							else
							{
								$datas = M('AllegroWebApiPostbuyformdata')->find($postbuyform_id);
								$data = $datas[0];
								$data['postbuyform_amount'] += $auction['auction_price'] * ($allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed']);
								$data->save();
							}

							$item = M('AllegroWebApiPostbuyformitem')->create();
							$item['postbuyform_id'] = $postbuyform_id;
							$item['postbuyformit_amount'] = $auction['auction_price'] * ($allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed']);
							$item['postbuyformit_quantity'] = $allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed'];
							$item['postbuyformit_auction_id'] = $allegroBid['auction_id'];
							$item['postbuyformit_title'] = $auction['auction_name'];
							$item['postbuyformit_country'] = $auction['country_id'];
							$item['postbuyformit_price'] = $auction['auction_price'];
							$item->save();

							$allegroBid['ab_quantity_payed'] += $allegroBid['ab_quantity'] - $allegroBid['ab_quantity_payed'];
							$allegroBid->save();

							if ($old_user_id != $user_id)
							{
								$adr = M('AllegroWebApiPostbuyformadr')->create();
								$adr['postbuyform_id'] = $postbuyform_id;
								$adr['postbuyformadr_type'] = 'shipment';
								$adr['postbuyformadr_country'] = $user['country_id'];
								$adr['postbuyformadr_street'] = $user['street'];
								$adr['postbuyformadr_postcode'] = $user['postcode'];
								$adr['postbuyformadr_city'] = $user['city'];
								$adr['postbuyformadr_full_name'] = "{$user['first_name']} {$user['last_name']}";
								$adr['postbuyformadr_company'] = $user['company'];

								$phone = array();
								if (trim($user['phone']) != '')
									$phone[] = $user['phone'];
								if (trim($user['phone2']) != '')
									$phone[] = $user['phone2'];

								$adr['postbuyformadr_phone'] = implode(",", $phone);
								$adr['postbuyformadr_nip'] = '';
								$adr->save();
							}

							$old_user_id = $user_id;
						}
						else
							continue;
					}
					else
						continue;
				}
				else
					continue;
			}
		}
		else
			return 'Brak uprawnień';

		return true;
	}

	/**
	 * Wyszukaj aukcje
	 */
	public function searchAuction($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$nr_konfiguracji = (int)$params['nrKonfiguracji'];
			if ($nr_konfiguracji <= 0)
				$nr_konfiguracji = 1;

			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}' AND nr_konfiguracji = '{$nr_konfiguracji}'");
			if ($shopSettings)
					$shopSetting = $shopSettings[0];

			$user_id = $shopSetting['user_id'];

			$search = '';
			if (isset($params['search']))
				$search = strtr($params['search'], array("'" => "", '"' => ""));

			if (strlen($search) < 3)
				return false;

			$sql = sql(array("
					SELECT t.* FROM
					(
						SELECT
							d.postbuyform_id as allegro_ao_id,
							i.postbuyformit_auction_id as auction_id,
							u.user_id,
							u.nick,
							u.first_name,
							u.last_name,
							u.email,
							b.ab_bid_date,
							d.postbuyform_get_by_last_datetime as get_by_last_datetime
						FROM
							allegro_postbuyformitem i
								LEFT JOIN
									allegro_postbuyformdata d using(postbuyform_id)
								LEFT JOIN
									allegro_user u ON u.user_id = d.postbuyform_buyer_id
								LEFT JOIN
									allegro_bid b ON b.auction_id = i.postbuyformit_auction_id AND b.user_id = u.user_id
						WHERE
							d.shop_id = %shop_id
							AND (
								i.postbuyformit_auction_id = '{$search}'
								OR u.nick like '{$search}%'
								OR u.email like '{$search}'
								OR u.last_name like '{$search}%'
							)
					UNION
						SELECT
							NULL as allegro_ao_id,
							b.auction_id as auction_id,
							u.user_id,
							u.nick,
							u.first_name,
							u.last_name,
							u.email,
							b.ab_bid_date,
							NULL as get_by_last_datetime
						FROM
							allegro_bid b
							LEFT JOIN allegro_user u ON u.user_id = b.user_id
						WHERE
							b.shop_id = %shop_id
							AND (
								b.auction_id = '{$search}'
								OR u.nick like '{$search}%'
								OR u.email like '{$search}'
								OR u.last_name like '{$search}%'
							)
							AND b.ab_quantity - b.ab_quantity_payed > 0
					) t
					INNER JOIN
						allegro_shop_auction asa ON asa.auction_id = t.auction_id
					WHERE
						asa.user_id = %user_id
					ORDER BY
						t.auction_id DESC, ab_bid_date DESC
				;",
					"shop_id" => $shop_id,
					"user_id" => $user_id
				));

			$auctions = M('AllegroWebApiShopAuction')->db->getAssoc($sql);

			$auction_id = preg_replace('`[^0-9]`', '', $search);
			if (strlen($auction_id) > 8)
			{
				$allegro_shop_auction_do_analizy = M('AllegroWebApiShopAuctionDoAnalizy')->findOrCreate("shop_id = '{$shop_id}' AND user_id = '{$user_id}' AND auction_id = '{$auction_id}'");
				$allegro_shop_auction_do_analizy[0]['shop_id'] = $shop_id;
				$allegro_shop_auction_do_analizy[0]['user_id'] = $user_id;
				$allegro_shop_auction_do_analizy[0]['auction_id'] = $auction_id;
				$allegro_shop_auction_do_analizy[0]->save();
			}

			if ($auctions)
			{
			//$auctions['sql']=$sql;
				return $auctions;
			}
			else
			{
				return array();
			}		
		}

		else
			return false;
	}

	/**
	 * Wyszukaj aukcje (stare z wypełnionym FOD)
	 */
	public function searchAuctionOld($params)
	{
		if ($this->authenticated)
		{
			$shop_id = $this->shop_id;

			$search = '';
			if (isset($params['search']))
				$search = strtr($params['search'], array("'" => "", '"' => ""));

			if (strlen($search) < 3)
				return false;

			$sql = sql(array("
					SELECT
						*,
						ao_get_by_last_datetime as get_by_last_datetime,
						ao_id as allegro_ao_id
					FROM
						allegro_fod_result
					WHERE
						shop_id = %shop_id
						AND (
							auction_id = '{$search}'
							OR nick like '{$search}%'
							OR email like '{$search}'
							OR last_name like '{$search}%'
						)
					ORDER BY
						auction_id DESC
				;",
					"shop_id" => $shop_id,
				));

			$auctions = M('AllegroWebApiShopAuction')->db->getAssoc($sql);

			if ($auctions)
				return $auctions;
			else
				return array();
		}
		else
			return false;
	}

	/**
	 * Zwraca ścieżkę kategorii dla zadanej kategorii (Świstak)
	 * (taką do wyświetlenia w listach rozwijanych wyboru kategorii)
	 */
	public function getSwistakCategoryPath($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			$category_id = (int)$params['category_id'];
			$category_path = array();

			$i = 0;
			while(true)
			{
				$categories = M('SwistakCategory')->find(sql(array("
					category_id = %category_id
					AND approved = 1
					",
					"category_id" => $category_id
				)), array('order' => 'category_path'));
				if ($categories)
				{
					$parent_id = $categories[0]['parent_id'];
					$categories = M('SwistakCategory')->find(sql(array("
							parent_id = %parent_id
							",
							"parent_id" => $parent_id
						)), array('order' => 'category_path'));
					if ($categories)
					{
						$category_path[$i] = $categories->asArray();
						$category_path[$i]['category_id'] = $category_id;
						$category_id = $categories[0]['parent_id'];
						$i++;
					}
					else
						break;
				}
				else
					break;
			}
			return array_reverse($category_path);
		}
		else
			return array();
	}

	/**
	 * Zwraca string reprezentujący ścieżke kategorii dla zadanej kategorii (Świstak)
	 */
	public function getSwistakCategoryPathString($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			$imploder = " > ";
			if (isset($params['imploder']))
				$imploder = $params['imploder'];

			$category_path = $this->getSwistakCategoryPath($params);
			$category_path_string = array();
			foreach($category_path as $categories)
				foreach($categories as $category)
					if ($category != $categories['category_id'] && $category['category_id'] == $categories['category_id'])
						$category_path_string[] = $category['category_name'];
			return implode($imploder, $category_path_string);
		}
		else
			return '';
	}

	public function getSwistakCategoryByParentId($params)
	{
		if (isset($params['parent_id']) && is_numeric($params['parent_id']))
		{
			$parent_id = (int)$params['parent_id'];

			$categories = M('SwistakCategory')->find(sql(array("
					parent_id = %parent_id
					AND approved = 1
					",
					"parent_id" => $parent_id
				)), array('order' => 'category_path'));

			if ($categories)
				return $categories->asArray();
			else
				return array();
		}
		else
			return array();
	}

	public function getSwistakCategoryPathArray($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			$category_path = $this->getSwistakCategoryPath($params);
			$category_path_array = array();
			foreach($category_path as $categories)
				foreach($categories as $category)
					if ($category != $categories['category_id'] && $category['category_id'] == $categories['category_id'])
						$category_path_array[$category['category_id']] = $category['category_name'];
			return $category_path_array;
		}
		else
			return array();
	}

	public function getSwistakUnit()
	{
		$out = array();

		$units = M('SwistakUnit')->find('true', array('order' => 'unit_id'));
		if ($units)
		{
			foreach($units as $unit)
			{
				$out[] = array(
						'option_id' => $unit['unit_id'],
						'option_name' => $unit['unit_name'],
					);
			}
		}

		return $out;
	}

	public function getSwistakDelivery()
	{
		$out = array();

		$units = M('SwistakDelivery')->find('true', array('order' => 'delivery_id'));
		if ($units)
		{
			foreach($units as $unit)
			{
				$out[] = array(
						'option_id' => $unit['delivery_id'],
						'option_name' => $unit['delivery_name'],
					);
			}
		}

		return $out;
	}

	public function getSwistakProvince()
	{
		$out = array();

		$units = M('SwistakProvince')->find('true', array('order' => 'province_id'));
		if ($units)
		{
			foreach($units as $unit)
			{
				$out[] = array(
						'option_id' => $unit['province_id'],
						'option_name' => $unit['province_name'],
					);
			}
		}

		return $out;
	}

	public function getSwistakParamForCategory($params)
	{
		if (isset($params['category_id']) && is_numeric($params['category_id']))
		{
			$category_id = (int)$params['category_id'];

			$out = array();

			$categoriesPath = $this->getSwistakCategoryPathArray($params);
			if ($categoriesPath)
			{
				foreach($categoriesPath as $category_id => $category_name)
				{
					$categories = M('SwistakCategory')->find("category_id = '{$category_id}'");
					if ($categories)
					{
						$category = $categories[0];
						$parametersToCategory = $category->SwistakParameterToCategory;
						if ($parametersToCategory)
						{
							foreach($parametersToCategory as $parameterToCategory)
							{
								$parameter = $parameterToCategory->SwistakParameter;
								if ($parameter)
								{
									$out[$parameter['parameter_id']] = array(
											"parameter_id" => $parameter['parameter_id'],
											"parameter_name" => $parameter['parameter_name'],
											"parameter_type" => $parameter['parameter_type'],
											"parameter_unit" => $parameter['parameter_unit'],
										);

									if ($parameter['parameter_type'] == "set" || $parameter['parameter_type'] == "enum")
									{
										$parameterValues = $parameter->SwistakParameterValue;
										if ($parameterValues)
										{
											foreach($parameterValues as $parameterValue)
											{
												$out[$parameter['parameter_id']]['values'][$parameterValue['spv_id']] = array(
														'label' => $parameterValue['label'],
														'value' => $parameterValue['value'],
													);
											}
										}
									}
								}
							}
						}
					}
				}
			}

			return $out;
		}
	}

	public function setSwistakDefaultComment($params)
	{
		if ($this->authenticated)
		{
			$params['comment'] = trim($params['comment']);
			if ($params['comment'] != '')
			{
				$shop_id = $this->shop_id;

				$shopSettings = M('SwistakShopSettings')->findByShopId($shop_id);
				if ($shopSettings)
					$shopSetting = $shopSettings[0];
				else
					$shopSetting = M('SwistakShopSettings')->create();

				$shopSetting['comment'] = $params['comment'];
				$shopSetting['shop_id'] = $shop_id;

				$shopSetting->save();
			}
			else
				return false;

			return true;
		}
		else
			return false;
	}

	public function sellSwistak($params_)
	{
		if ($this->authenticated)
		{
			//return print_r($params, true);
			if (
					!isset($params_['params'])
					|| !isset($params_['swistakLogin'])
					|| !isset($params_['swistakPassword'])
				)
				return 'ERROR: Nie skonfigurowano poprawnie Świstak WebAPI [#2161].';

			$params = $params_['params'];
			$product_id = $params_['product_id'];
			$swistakLogin = $params_['swistakLogin'];
			$swistakPassword = $params_['swistakPassword'];

			$renew = (int)$params_['renew'];

			try {
				$client = new Core_SwistakApiSoapClient();

				$swistakHash = $client->get_hash($swistakLogin, md5($swistakPassword));

				$shop_id = $this->shop_id;

				$dataform = serialize($params);
// file_put_contents("/tmp/swistak.log", print_r($dataform, true), FILE_APPEND);
// 				return $params;
				$result = $client->add_auction($swistakHash, $params);

// file_put_contents("/tmp/swistak.log", ">".print_r($result, true), FILE_APPEND);
				$auction_id = $result['id'];

				$user_id = $client->get_id_by_login($swistakLogin);

				/// vvv logowanie prób wystawienia aukcji
// 				$log = array("method" => "sell", "timestamp" => date("Y-m-d H:i:s"), "sellform" => $form, "return" => $item);
// 				foreach($log['sellform'] as $i => $logPos)
// 				{
// 					if ($logPos->{'fid'} == 16 && $logPos->{'fvalue-image'} != '')
// 						$log['sellform'][$i]->{'fvalue-image'} = '... jest ...';
// 					if ($logPos->{'fid'} == 24 && $logPos->{'fvalue-string'} != '')
// 						$log['sellform'][$i]->{'fvalue-string'} = strtr($log['sellform'][$i]->{'fvalue-string'}, array("\n" => " "));
//
// 				}
// 				file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($log, true), FILE_APPEND);
				/// ^^^ logowanie prób wystawienia aukcji

				// zapisanie/aktualizacja w bazie Allegro Pośrednika konfiguracji sklepowych (tajne)
				$shopSettings = M('SwistakShopSettings')->findByShopId($shop_id);
				if ($shopSettings)
					$shopSetting = $shopSettings[0];
				else
					$shopSetting = M('SwistakShopSettings')->create();
				$shopSetting['shop_id'] = $shop_id;
				$shopSetting['user_id'] = $user_id;
				$shopSetting['login_swistak'] = $swistakLogin;
				$shopSetting['password_swistak'] = $swistakPassword;
				$shopSetting['error_counter'] = 0;
				$shopSetting->save();

				if(isset($auction_id) && $auction_id > 0)
				{
					// zapisanie w bazie Allegro Pośrednika informacji o wystawionych aukcjach
					$shopAuction = M('SwistakShopAuction')->create();
					$shopAuction['shop_id'] = $shop_id;
					$shopAuction['product_id'] = $product_id;
					$shopAuction['auction_id'] = $auction_id;
					$shopAuction['auction_name'] = $params['title'];
					$shopAuction['auction_price'] = $params['price'];
					$shopAuction['auction_items'] = (int)$params["item_count"];
					$shopAuction['date_start'] = date("Y-m-d H:i:s");

					$shopAuction->save();

					M('SwistakShopAuction')->db->execQuery("UPDATE swistak_shop_auction SET auction_hidden = 1 WHERE shop_id = {$shop_id} AND auction_active = 0 AND product_id = '{$product_id}' AND auction_id <> '{$shopAuction['auction_id']}';");

					/// Tutan zapisywane są dane w związku z RENEW
					if ($renew > 0)
					{
						$shopAuctionRenew = M('SwistakShopAuctionRenew')->create();

						$shopAuctionRenew['shop_id'] = $shop_id;
						$shopAuctionRenew['auction_id'] = $auction_id;
// 						$shopAuctionRenew['asar_renew'] = 1;
// 						$shopAuctionRenew['asar_renew_queue'] = 0;
// 						$shopAuctionRenew['asar_renew_repeats'] = 0;
						$shopAuctionRenew['asar_dataform'] = $dataform;

						$shopAuctionRenew->save();
					}

					return $auction_id;
				}
// 				else
// 				{
// 					file_put_contents("/tmp/SwistakWebApiServer-{$this->shop_name}.log", print_r($check, true), FILE_APPEND);
// 					file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($item, true), FILE_APPEND);
// 					return "ERROR: Bliżej nie określony błąd [#315]";
// 				}
			}
			catch(SoapFault $soapFault)
			{
				/// vvv logowanie prób wystawienia aukcji
// 				$log = array("method" => "sell", "timestamp" => date("Y-m-d H:i:s"), "sellform" => $form, "return" => $soapFault);
// 				foreach($log['sellform'] as $i => $logPos)
// 				{
// 					if ($logPos->{'fid'} == 16 && $logPos->{'fvalue-image'} != '')
// 						$log['sellform'][$i]->{'fvalue-image'} = '... jest ...';
// 					if ($logPos->{'fid'} == 24 && $logPos->{'fvalue-string'} != '')
// 						$log['sellform'][$i]->{'fvalue-string'} = strtr($log['sellform'][$i]->{'fvalue-string'}, array("\n" => " "));
//
// 				}
// 				file_put_contents("/tmp/WebApiServer-{$this->shop_name}.log", print_r($log, true), FILE_APPEND);
				/// ^^^ logowanie prób wystawienia aukcji

// 				$shopAuctionSellError = M('AllegroWebApiShopAuctionSellError')->create();
// 				$shopAuctionSellError['shop_id'] = $this->shop_id;
// 				$shopAuctionSellError['product_id'] = $product_id;
// 				$shopAuctionSellError['country_id'] = $country_id;
// 				$shopAuctionSellError['auction_name'] = $values["fid:1"];
// 				$shopAuctionSellError['asase_error_message'] = $soapFault->getMessage();
// 				$shopAuctionSellError->save();
//
// 				$errorMessage = $soapFault->getMessage();
//
// 				$return['error_text'] = "ERROR: ".$errorMessage;//." || ".print_r($out['fid'],true);
//
// 				preg_match('`.*fid:\s(?<fid>[0-9]+)$`iU', $errorMessage, $out);
// 				if (isset($out['fid']) && (int)$out['fid'] > 0)
// 				{
// 					$fid = (int)$out['fid'];
// 					$return['error_fid'] = $fid;
// 					$fids = M('AllegroWebApiSellForm')->find("country_id = '{$country_id}' AND sellform_id = '{$fid}'");
// 					if ($fids)
// 					{
// 						$return['error_text'] = strtr($return['error_text'], array(
// 								" - fid: {$fid}" => "{$fids[0]['sellform_title']}"
// 							));
//
// 						switch($fids[0]['sellform_type'])
// 						{
// 							case '1':	// input
// 								if (strlen($values["fid:{$fid}"]) > $fids[0]['sellform_length'])
// 									$return['error_text'] .= " - przekroczona maksymalna liczba znaków (wprowadzono ".strlen($values["fid:{$fid}"])." a maksimum to {$fids[0]['sellform_length']})";
// 								break;
// 						}
// 					}
// 				}
				file_put_contents("/tmp/swistak.log", $soapFault->faultcode."\n", FILE_APPEND);

				$errory = array(
						'ERR_USER_PASSWD' => 'Błędny login lub hasło.',
						'ERR_USER_BLOCKED' => 'Użytkownik zablokowany.',
						'ERR_USER_STATUS' => 'Konto użytkownika posiada ograniczenia na sprzedaż.',
						'ERR_AUTHORIZATION' => 'Niepoprawny hash lub sesja utraciła ważność.',
						'ERR_NOT_ACCEPTED_RULES' => 'Użytkownik nie zaakceptował aktualnej wersji regulaminu.',
						'ERR_INVALID_ID_OUT' => 'Niepoprawny numer id_out.',
						'ERR_INVALID_TITLE' => 'Brak tytułu lub tytuł przekracza 50 znaków.',
						'ERR_INVALID_PRICE' => 'Brak ceny lub cena większa niż 100000000.00.',
						'ERR_INVALID_ITEM_COUNT' => 'Brak określonej ilości sztuk towaru lub ilość sztuk większa niż 9999999.',
						'ERR_INVALID_DESCRIPTION' => 'Brak opisu przedmiotu lub opis krótszy niż 20 znaków.',
						'ERR_INVALID_TAGS' => 'Słowa pomocnicze dłuższe niż 64 znaki.',
						'ERR_INVALID_CATEGORY' => 'Kategoria nie istnieje lub posiada podkategorie.',
						'ERR_INVALID_CITY' => 'Nazwa lokalizacji posiada powyżej 50 znaków.',
						'ERR_DUPLICATE_ID_OUT' => 'Użytkowik posiada trwającą aukcję o taki samym id_out.',
						'ERR_INVALID_DELIVERY_COST_1' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST_2' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST_3' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST_4' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST_5' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST_6' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST_7' => 'Błędnie określone koszty przesyłki. Błędny zakres ilości sztuk lub brak kosztu dostawy.',
						'ERR_INVALID_DELIVERY_COST' => 'Nie określono żadnej formy przesyłki. Należy określić koszty przesyłki dla wszystkich sztuk towaru przynjmniej dla jednej formy przesyłki.',
						'ERR_INTERNAL_SERVER_ERROR' => 'Wewnętrzny błąd WebAPI Swistak.pl.'
					);

				return $errory[$soapFault->faultcode]. " (".$soapFault->faultcode.")";
			}
		}
		else
			return 'ERROR: Wystąpił problem z autoryzacją w Pośredniku [#319].';
	}

	public function getGoogleTranslateSignCost()
	{
// 		return (float)0.000070184;
		return (float)0.00012;
	}

	public function getGoogleTranslate($params)
	{
		$out = array();

		if($this->authenticated)
		{
			$customer_info = M('Ecommerce24hCustomerShop')->getInfo($this->shop_name);
			if($customer_info)
			{
				$account_value = M('Ecommerce24hCustomerAccount')->currentAccount($customer_info['customers_shops_id']);
				
				$customers_priority = M('Ecommerce24hCustomerAccount')->findBySql("SELECT customers_priority FROM customers WHERE customers_id = ". $customer_info['customers_id']);
				if($customers_priority)
					$customers_priority = (int)$customers_priority[0]['customers_priority'];
// 				///na potrzeby testów
// 				$account_value = 10;
				if($account_value <= 0 && $customers_priority < 7)
					$out['errors'][] = "Tłumaczenia dostępne tylko przy dodatnim stanie konta. <br />Twój stan konta to ". number_format($account_value, 2, ",", ",") ." zł";
				else
				{
					///cena netto za znak wyliczona przez nas
					$price_for_sign = $this->getGoogleTranslateSignCost();

					$date = date('Y-m-d H:i:s', time());
					$day = substr($date, 0, 10);
					$month = substr($date, 0, 7);

					$text_length = mb_strlen($params['text'], 'utf-8');

					$Core_GoogleTranslate = Core_GoogleTranslate::getInstance();
					$out = $Core_GoogleTranslate->translate($params, $customer_info['customers_shops_id']);

					if(isset($out['translatedText']))
					{
						$current_account = M('Ecommerce24hCustomerAccount')->findOrCreate("customers_shops_id = {$customer_info['customers_shops_id']} AND customers_account_day = '". date("Y-m-d", time()) ."' AND customers_account_operation_description = 'Tłumaczenia Google Translate'");
						$current_account = $current_account[0];
						$used_sign_by_day = M("Ecommerce24hGoogleTranslateHistory")->getCountSignsPerDay($day, $customer_info['customers_shops_id']);

						$current_account['customers_id'] = $customer_info['customers_id'];
						$current_account['customers_shops_id'] = $customer_info['customers_shops_id'];
						$current_account['customers_account_day'] = $day;
						$current_account['customers_account_month'] = $month;
						$current_account['customers_account_operation_amount'] = $used_sign_by_day * $price_for_sign * -1;
						$current_account['customers_account_operation_description'] = 'Tłumaczenia Google Translate';
						$current_account->save();
					}
				}
			}
			else
				$out['errors'][] = 'Wystąpił błąd autoryzacji';
		}
		else
			$out['errors'][] = 'Wystąpił błąd autoryzacji';
// 		l($out);
		return $out;
	}

	public function getGoogleTranslateTest($params)
	{
		$user = $params['user'];
		$pass = $params['pass'];
		if($user == 'ec24h' && $pass == '3c24hhh')
		{
			$Core_GoogleTranslate = Core_GoogleTranslate::getInstance();
			$out = $Core_GoogleTranslate->translate($params, 0);
		}
		else
			$out['errors'][] = 'Wystąpił błąd autoryzacji';

		return $out;
	}

	public function isFacebookTarrif()
	{
		$out = array();

		if($this->authenticated)
		{
			$customerShop = M('Ecommerce24hCustomerShop')->getInfo($this->shop_name);

			$tariffs = explode(";", $customerShop['customers_shops_tariff']);
			$tariff_id = $tariffs[0];
			$tariff_changes = array();
			if (count($tariffs) > 1)
			{
				for($i = 1; $i < count($tariffs); $i++)
				{
					$tariffs_ = explode(">", $tariffs[$i]);
					$tariff_changes[$tariffs_[0]] = $tariffs_[1];
				}
			}

			foreach($tariff_changes as $tariff_change_month => $tariff_change_id)
			{
				$tariff_id = $tariff_change_id;
			}

			$tariff = M('Ecommerce24hCustomerShopTariff')->first($tariff_id);

			if ($tariff && $tariff['customers_shops_tariff_facebookshop'] > 0)
				return true;
			else
				return false;
		}
		else
			$out['errors'][] = 'Wystąpił błąd autoryzacji';

		return $out;
	}

	public function setFacebookPageId($params)
	{
		$out = array();

		if($this->authenticated)
		{
			$customerShop = M('Ecommerce24hCustomerShop')->getInfo($this->shop_name);
			$customerShop['customers_shops_facebook_page_id'] = $params['page_id'];
			$customerShop->save();

			return true;
		}
		else
			$out['errors'][] = 'Wystąpił błąd autoryzacji';

		return $out;
	}

	public function testTimeoutu($params)
	{
		sleep($params['timeout']);
		return $params;
	}

	public function getRefoundReasons($params)
	{		
		if(isset($params['countryCode']))
		{
			$countryCode=$params['countryCode'];
		}
		else
		{
			$countryCode=1;
		}
		if($this->authenticated&&isset($params['postbuyform_id'])&&isset($params['postbuyformit_auction_id']))
		{

			$shop_id = $this->shop_id;
			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}'");
			if ($shopSettings)
			{
				$shopSetting = $shopSettings[0];
				try
				{			
					
					$client = new Core_AllegroWebApiSoapClient(false);	
					$version=$client->doQuerySysStatus(array('sysvar'=>1,'countryId'=>1,'webapiKey'=>$shopSetting['web_api_code']));
					$doLoginResponse=$client->doLogin(array(
						'userLogin'=>$shopSetting['login_allegro'],
						'userPassword'=>$shopSetting['password_allegro'],
						'countryCode'=>$countryCode,
						'webapiKey'=>$shopSetting['web_api_code'],
						'localVersion'=>$version->{verKey},
						)
					);

					$allegroWebApiPostbuyformdata=M('AllegroWebApiPostbuyformdata')->first($params['postbuyform_id']);
					if($allegroWebApiPostbuyformdata)
					{						
						$doGetRefundsDealsResponse=$client->doGetRefundsDeals(
							array(
								'sessionId'=>$doLoginResponse->{sessionHandlePart},
								'filterOptions'=>array(
									array(
										'filterId'=>'itemId',
										'filterValueId'=>array($params['postbuyformit_auction_id'])
									),								
								),
							)
						);

						foreach($doGetRefundsDealsResponse->{dealsList}->{item} as $item)
						{
							if($item->{buyerId}==$allegroWebApiPostbuyformdata['postbuyform_buyer_id'])
							{
								$dealId=$item->{dealId};

								$reasons=$client->doGetRefundsReasons(array('sessionId'=>$doLoginResponse->{sessionHandlePart},'dealId'=>$dealId));

								return array(
									'reasons'=>$reasons->{reasonsList}->{item},
									'dealId'=>$dealId,
									);
							}							
						}
						return 3;
						
					}
					else
					{
						return 2;
					}
				}
				catch(Exception $e)
				{
					return $e->getMessage();
				}
			}
			return 1;
		}
		return 0;
	}

	public function getRefoundForm($params)
	{
		$allegro_id = (int)$params['allegro_id'];
		$allegro_user_id = (int)$params['allegro_user_id'];
		$reason = (int)$params['reason'];
/*
		$bids_items_count = M('AllegroWebApiBid')->getAuctionAndUserItemsCount($allegro_id, $allegro_user_id);
		if(is_null($bids_items_count) || !($bids_items_count >= 0))
			$bids_items_count = 0;
		$params['bids_items_count'] = $bids_items_count;
*/
		$auction = M('AllegroWebApiShopAuction')->find("auction_id = '{$allegro_id}'");
		$auction = $auction[0];

		$acp = M('AllegroWebApiCancelledProducts')->create();
		$acp['products_id'] = $params['products_id'];
		$acp['user_id'] = $allegro_user_id;
		$acp['reason_id'] = $reason;
		$acp['deal_id'] = $params['dealId'];
		$acp['shop_id'] = $auction['shop_id'];
		$acp['seller_id'] = $auction['user_id'];
		$acp['allegro_id'] = $allegro_id;
		if ($acp->save())
			return true;

		return false;
	}

	public function getShipmentData()
	{
		if($this->authenticated)
		{$shop_id = $this->shop_id;
			$shopSettings = M('AllegroWebApiShopSettings')->find("shop_id = '{$shop_id}'");
				if ($shopSettings)
				{
					$shopSetting = $shopSettings[0];

					$client = new Core_AllegroWebApiSoapClient();

					$reasons = $client->doGetShipmentData(1, $shopSetting['web_api_code']);
					return $reasons;
				}
			return false;
		}return false;
	}
	public function getAdminPhone()
	{
		if($this->authenticated)
		{
			$shops = M('Shop')->find("shop_id = '{$this->shop_id}'");
			$shop = $shops[0];
			return $shop['shop_phone_number'];
		}
		return false;
	}

	public function setAdminPhone($params)
	{
		if($this->authenticated)
		{
			$shops = M('Shop')->find("shop_id = '{$this->shop_id}'");
			$shop = $shops[0];
			if(empty($params['phone']))
			{
				$shop['shop_phone_number'] = NULL;
			}
			else
			{
				$shop['shop_phone_number'] = trim($params['phone']);
			}
			return $shop->save();
		}
		return false;
	}

	public function adminSMS($params)
	{
		if($this->authenticated)
		{
			$smsApi = Core_SMSApi::getInstance();
			$ret = $smsApi->sendAdminSMS($params);
			return $ret;
		}
		return false;
	}

	public function addSms($params)
	{
		if($this->authenticated)
		{
			$params['shops_id'] = $this->shop_id;
			//$smsApi = Core_SMSApi::getInstance();
			//$return = $smsApi->addToQueue($params);
			//if (isset($params['to'], $params['text'], $params['shops_id']))
			{
				//$to = preg_replace('`[^0-9]`', '', $params['to']);
				//$text = (string)$params['text'];
/*
				$smsQueue = M('SMSApiQueue')->create();
				//$smsQueue = M('SMSApiQueue')->find();$smsQueue = $smsQueue[0];
				$smsQueue['sms_shop_id'] = (int)$params['shops_id'];
				$smsQueue['sms_phone_number'] = (string)$to;
				$smsQueue['sms_text'] = $text;
				$smsQueue->save();
				$t=$smsQueue = M('SMSApiQueue')->find()->asArray();*/
			}
			$t=$smsQueue = M('SMSApiQueue')->addToQueue($params);
		return $t;
		$customer_info = M('Ecommerce24hCustomerShop')->getInfo($this->shop_name);
			if($customer_info)
			{
				$account_value = M('Ecommerce24hCustomerAccount')->currentAccount($customer_info['customers_shops_id']);
				
				$customers_priority = M('Ecommerce24hCustomerAccount')->findBySql("SELECT customers_priority FROM customers WHERE customers_id = ". $customer_info['customers_id']);
				//return $customer_info->asArray();
				return $customers_priority->asArray();
			}
		}
		else
		{
			return 'no auth';
		}
	}
	public function getNewOrdersTest($params)
	{
		if ($this->authenticated)
		{
			$out = array(); $dbg = true;

			$postbuyform_id = $params['postbuyform_id'];$out['test']='test';

			/// standardowo ta funkcja wywoływana jest bez parametrów, a możliwość wywołania jej z parametrem jest dorobiona tylko dla celów odpluskwiania
			if ($postbuyform_id == 0)
			{
				$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find(sql(array("
							shop_id = %shop_id
							AND postbuyform_id = '267895167'
						",
						"shop_id" => '802'//$this->shop_id
						)));
			}
			else
			{	$dbg = true;
				$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find(sql(array("
							shop_id = %shop_id
							AND postbuyform_id = %postbuyform_id
						",
						"shop_id" => $this->shop_id,
						"postbuyform_id" => $postbuyform_id
						)));//var_dump($postbuyformdatas,'postbuyformdatas');$out['t']=$postbuyformdatas;
			}
			if(isset($params['dbg']) && $params['dbg'] == 1)
			{
				$dbg = true;
				
				$postbuyformdatas = M('AllegroWebApiPostbuyformdata')->find(sql(array("
							shop_id = %shop_id
							AND postbuyform_get_by_last_datetime > '2013-04-28 10:00:00'
							AND postbuyform_get_by_last_datetime < '2013-04-30 10:00:00'
							
						",
						"shop_id" => $this->shop_id//,
						//"postbuyform_id" => $postbuyform_id
						)));
				
			}

			$countries = array();

			if ($postbuyformdatas)
			{
// 				return $postbuyformdatas->asArray();
				foreach($postbuyformdatas as $i => $postbuyformdata)
				{
					$postbuyformadrs = $postbuyformdata->AllegroWebApiPostbuyformadr;
					if (!$postbuyformadrs)
						continue;

					$get_other_bids = 1;
					$shopSettings = $postbuyformdata->AllegroWebApiShopSettings;
					if ($shopSettings['get_other_bids'] == 0)
						$get_other_bids = 0;

					$out[$i] = $postbuyformdata->asArray();

					if (trim($out[$i]['postbuyform_shipment_title']) == '' || $postbuyformdata['shipment_id'] == '10022' || $postbuyformdata['shipment_id'] == '20022')
					{
						if ((int)$postbuyformdata['shipment_id'] > 0)
						{
							$shipments = M('AllegroWebApiShipment')->find("country_id = '1' AND shipment_id = '{$postbuyformdata['shipment_id']}'");
							if ($shipments)
							{
								$out[$i]['postbuyform_shipment_title'] = $shipments[0]['shipment_name'];
								$out[$i]['postbuyform_shipment_type'] = $shipments[0]['shipment_type'];
								if(($postbuyformdata['shipment_id'] == '10022' || $postbuyformdata['shipment_id'] == '20022') && trim($postbuyformdata['postbuyform_shipment_title']) != '')
									$out[$i]['postbuyform_shipment_title'] = $shipments[0]['shipment_name'].' ['.trim($postbuyformdata['postbuyform_shipment_title']).']';
							}
						}
						else
						{
							$out[$i]['postbuyform_shipment_title'] = "Brak danych";
						}
					}

					$out[$i]['shipment']  = array();
					$out[$i]['invoice']  = array();

					foreach($postbuyformadrs as $postbuyformadr)
					{
						if (!isset($countries[$postbuyformadr['postbuyformadr_country']]))
							$countries[$postbuyformadr['postbuyformadr_country']] = M('AllegroWebApiCountry')->getCountryName($postbuyformadr['postbuyformadr_country']);

						if ($postbuyformadr['postbuyformadr_type'] == 'shipment')
						{
							$out[$i]['shipment'] = $postbuyformadr->asArray();
							$out[$i]['shipment']['postbuyformadr_country'] = $countries[$postbuyformadr['postbuyformadr_country']];
						}

						if ($postbuyformadr['postbuyformadr_type'] == 'invoice')
						{
							$out[$i]['invoice'] = $postbuyformadr->asArray();
							$out[$i]['invoice']['postbuyformadr_country'] = $countries[$postbuyformadr['postbuyformadr_country']];
						}
					}

					$out[$i]['payment']  = array();
					$paymentform = $postbuyformdata->AllegroWebApiPostbuyformpayment;
					if ($paymentform)
						$out[$i]['payment'] = $paymentform->asArray();

					$postbuyformitems = $postbuyformdata->AllegroWebApiPostbuyformitem;

					$postbuyformitems = $postbuyformdata->AllegroWebApiPostbuyformitem;
					if (!$postbuyformitems)
					{
						$postbuyformdata['postbuyform_get_by_shop'] = 2; //if(!$dbg) $postbuyformdata->save();
						unset($out[$i]);
						continue;
					}

					$there_are_from_our_system = false;
					foreach($postbuyformitems as $j => $postbuyformitem)
					{
						if ($get_other_bids == 0)
						{
							$auctions = M('AllegroWebApiShopAuction')->findByAuctionId($postbuyformitem['postbuyformit_auction_id']);
							if ($auctions && $auctions[0]['other_system'] == 0)
								$there_are_from_our_system = true;
						}


						$out[$i]['items'][$j]['item'] = $postbuyformitem->asArray();

						$bids = M('AllegroWebApiBid')->findByAuctionIdAndUserId($postbuyformitem['postbuyformit_auction_id'], $postbuyformdata['postbuyform_buyer_id']);
						$bid = $bids[0];

						$shopAuction =  $bid->AllegroWebApiShopAuction;
						$user = $bid->AllegroWebApiUser;
						if ($shopAuction && $user)
						{
							$out[$i]['items'][$j]['auction'] = $shopAuction->asArray();
							$out[$i]['user'] = $user->asArray();
						}
						else
						{
							
							if(!$user)
								$postbuyformdata['postbuyform_get_by_shop'] = 6; 
							if(!$shopAuction)
								$postbuyformdata['postbuyform_get_by_shop'] = 3; 

							if(!$postbuyformitem['postbuyformit_auction_id'])
								$postbuyformdata['postbuyform_get_by_shop'] = 7; 

							//if(!$dbg) $postbuyformdata->save();
							unset($out[$i]);
							continue 2;
						}


						if (!isset($countries[$out[$i]['user']['country_id']]))
							$countries[$out[$i]['user']['country_id']] = M('AllegroWebApiCountry')->getCountryName($out[$i]['user']['country_id']);

						$out[$i]['user']['country'] = $countries[$out[$i]['user']['country_id']];
					}

					if ($get_other_bids == 0 && $there_are_from_our_system == false)
					{
						$postbuyformdata['postbuyform_get_by_shop'] = 4; if(!$dbg) $postbuyformdata->save();
						unset($out[$i]);
					}

					$postbuyformdata['postbuyform_get_by_last_datetime'] = date('Y-m-d H:i:s');
					//if(!$dbg) $postbuyformdata->save();
				}
			}

			return $out;
		}
		else
			return false;
	}

	public function getProductsOnAuctions($params = array())
	{
		// l('a');
		if ($this->authenticated) {
			$out = array();
			$since = '';
			if(isset($params['date_since'])) {
				$date_since = $params['date_since'];
			}

			$active_only = true;
			if(isset($params['active_only'])) {
				$active_only = $params['active_only'];
			}

			foreach(M('AllegroWebApiShopAuction')->findProductsOnAuctions($this->shop_id, $active_only, $date_since) as $product)
			{
				$out[] = $product['product_id'];
			}

			return $out;
		} else {
			return array(1);
		}
	}

	
}

function webApiLog($action, $method, $object)
{
	file_put_contents("/var/log/allegro/{$action}.log", date("Y-m-d H:i:s") . " | {$method} | " . json_encode($object) . "\n", FILE_APPEND);
}
