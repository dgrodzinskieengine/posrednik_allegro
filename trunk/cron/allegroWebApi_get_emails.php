<?php
/// Około raz na godzinę

die('Już nie potrzebne');

require_once(dirname(__FILE__)."/../lib/lib.php");

// new Core_Pop3();

// $mbox = imap_open("{pop.gmail.com:995/pop3/ssl/novalidate-cert}INBOX","allegro@walaszek.pl","8reksio");
$mbox = imap_open("{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX","allegro@walaszek.pl","8reksio");

$message_count = imap_num_msg($mbox);

// print $message_count."\n";die;

for ($i = 1; $i <= $message_count; ++$i)
{
	$header = imap_header($mbox, $i);
// 	print_r($header);

	imap_setflag_full($mbox, $i, "\\Seen");

	$title = "";
	$title_array = imap_mime_header_decode($header->subject);
// 	print_r($title_array);die;

	foreach ($title_array AS $title_obj)
	if (strtoupper($title_obj->charset) != 'UTF-8')
		$title .= rtrim(iconv($title_obj->charset, 'UTF-8', $title_obj->text));
	else
		$title .= rtrim($title_obj->text);

// 	print_r($title);die;

	$body = imap_qprint(imap_body($mbox, $i));

	$body = substr($body, strpos($body, 'Content-Type: text/html'));
// 	print_r($body);die;

	preg_match('`Content-Type: text/html; charset=(?<charset>[a-z0-9\-]+)`i', $body, $matches);
	if (strtoupper($matches['charset']) != 'UTF-8' && trim($matches['charset']) != '')
		$body = iconv($matches['charset'], 'UTF-8', $body);

// 	print_r($body);die;
	
	$message = array(
			'message_id' => $header->message_id,
			'date' => date("Y-m-d H:i:s", $header->udate),
			'from' => "{$header->from[0]->mailbox}@{$header->from[0]->host}",
			'to' => "{$header->to[0]->mailbox}@{$header->to[0]->host}",
			'title' => $title,
			'body' => $body
		);

	preg_match('`Kupujący (?<user>.*) wybrał`iU', $message['title'], $matches);
	$message['params']['User'] = $matches['user'];

// 	print_r($message);die;
	if (trim($matches['user']) != "")
	{
// 		print_r($body);die;
		$body = strtr($message['body'], array("\n" => "", "\r" => ""));
		preg_match_all('`<tr>\s*<td.*>(?<param>.*)</td>\s*<td.*>(?<value>.*)</td>\s*</tr>`iU', $body, $matches, PREG_SET_ORDER);
// 		print_r($matches);die;
		foreach($matches as $match)
		{
			$param = trim($match['param']);
			$value = false;
			switch($param)
			{
				case "Aukcja":
					preg_match_all('`\((?<value>[0-9]+)\)`iU', $match['value'], $matches);
					$value = implode(",", $matches['value']);
					break;
				case "Płatność":
					$value = trim($match['value']);
					break;
				case "Metoda Płatności":
					$value = trim(preg_replace('`\s+`i', ' ', $match['value']));
					break;
				case "Sposób i koszt dostawy":
					$value = preg_replace('`(\s[0-9].)`iU', ':$1', trim(preg_replace('`\s+`i', ' ', strip_tags($match['value']))));
					break;
				case "Kwota za zakupy":
					$value = preg_replace('`[^0-9\.]+`i', '', strtr(trim(preg_replace('`\s+`i', ' ', $match['value'])), array(',' => '.')));
					break;
				case "Do zapłaty":
					$value = preg_replace('`[^0-9\.]+`i', '', strtr(trim(preg_replace('`\s+`i', ' ', $match['value'])), array(',' => '.')));
					break;
				case "Data wypełnienia":
					$value = date('Y-m-d H:i:s', strtotime(trim(preg_replace('`\s+`i', ' ', $match['value']))));
					break;
				case "Adres do wysyłki":
					$value = trim(preg_replace('`\s+`i', ' ', $match['value']));
					break;
				case "Dane do fatkury VAT":
				case "Faktura VAT":
					$value = trim(preg_replace('`\s+`i', ' ', $match['value']));
					$param = "Dane do fatkury VAT";
					break;
				case "Numer telefonu":
					$value = trim(preg_replace('`\s+`i', ' ', $match['value']));
					break;
				case "Wiadomość dla sprzedającego":
					$value = trim(preg_replace('`\s+`i', ' ', $match['value']));
					break;
			}

			if ($value !== false)
				$message['params'][$param] = $value;

			if (isset($message['body']))
				unset($message['body']);
		}
	// 	if (!$matches)
	// 	{
	// 		$messages[$i]['body'] = $body;
	// 		print_r($body);
	// 		print_r($matches);
	// 		die;
	// 	}

// 		print_r($message); die;

		$user = M('AllegroWebApiUser')->findByNick($message['params']['User']);
		if ($user)
		{
// 			print_r($message);die;

			$auction_ids = explode(",", $message['params']['Aukcja']);
			$doZapisania = count($auction_ids);
			foreach($auction_ids as $auction_id)
			{
				$aukcja = M('AllegroWebApiShopAuction')->findByAuctionId($auction_id);
				if ($aukcja)
				{
					$user_id = $user[0]['user_id'];
					$shop_id = $aukcja[0]['shop_id'];
					$country_id = $aukcja[0]['country_id'];

					if ((int)M('AllegroWebApiBid')->db->getValue("SELECT ab_id FROM allegro_bid WHERE user_id = {$user_id} AND auction_id = {$auction_id};") > 0)
					{
						$ao_id = (int)M('AllegroWebApiBid')->db->getValue("SELECT ao_id FROM allegro_bid WHERE user_id = {$user_id} AND auction_id = {$auction_id};");
	// 					var_dump($ao_id);die;
						if ($ao_id == 0)
						{
							$order = M('AllegroWebApiOrder')->create();
							$order['shop_id'] = $shop_id;
							$order['country_id'] = $country_id;
							$order['auction_id'] = $auction_id;
							$order['user_id'] = $user_id;
							$order['ao_transport'] = $message['params']['Sposób i koszt dostawy'];
							$order['ao_payment'] = $message['params']['Metoda Płatności'];
							$order['ao_message'] = $message['params']['Wiadomość dla sprzedającego'];

	// 						if ($message['params']['Adres do wysyłki'] != '')
	// 							$order['ao_message'] .= "\n\nADRES WYSYŁKI:\n" . strtr($message['params']['Adres do wysyłki'], array('<br>' => "\n", '<br />' => "\n", '<br/>' => "\n"));

							if ($message['params']['Dane do fatkury VAT'] != '')
								$order['ao_message'] .= "\n\nADANE DO FAKTURY VAT:\n" . strtr($message['params']['Dane do fatkury VAT'], array('<br>' => "\n", '<br />' => "\n", '<br/>' => "\n"));

							$order['ao_firstname'] = $user[0]['first_name'];
							$order['ao_lastname'] = $user[0]['last_name'];
							$order['ao_company'] = $user[0]['company'];
							$order['ao_address'] = $user[0]['street'];
							$order['ao_postcode'] = $user[0]['postcode'];
							$order['ao_city'] = $user[0]['city'];
							$order['ao_country_id'] = $user[0]['country_id'];

	// 						print_r($message['params']);
							preg_match_all('`br>(?<value>.*)(<|$)`iU', $message['params']['Adres do wysyłki'], $matches);
							if (!$matches['value'])
								preg_match_all('`br/>(?<value>.*)(<|$)`iU', $message['params']['Adres do wysyłki'], $matches);
							if (!$matches['value'])
								preg_match_all('`br />(?<value>.*)(<|$)`iU', $message['params']['Adres do wysyłki'], $matches);

// 							print_r($matches); die;
							
							if ($matches['value'])
							{
								$order['ao_delivery'] = 1;
								$deliveries = array_reverse($matches['value']);

								foreach($deliveries as $delivery)
								{
									$delivery = trim($delivery);
									
									if (strpos($delivery, 'NIP') !== false)
										continue;

									preg_match('`^(?<postcode>[0-9\-]+) (?<city>.*)$`iU', $delivery, $matches);
									if ($matches)
									{
										$order['ao_delivery_postcode'] = $matches['postcode'];
										$order['ao_delivery_city'] = $matches['city'];
										continue;
									}

									if (trim($order['ao_delivery_postcode']) != '' && trim($order['ao_delivery_address']) == '')
									{
										$order['ao_delivery_address'] = $delivery;
										continue;
									}

									if (trim($order['ao_delivery_address']) != '' && trim($order['ao_delivery_firstname']) == '')
									{
										preg_match('`^(?<imie>[^\s]+) (?<nazwisko>.*)$`iU', $delivery, $matches);
										if ($matches)
										{
	// 										print_r($matches);die;
											$order['ao_delivery_firstname'] = $matches['imie'];
											$order['ao_delivery_lastname'] = $matches['nazwisko'];

										}
										continue;
									}

									if (trim($order['ao_delivery_firstname']) != '' && trim($order['ao_delivery_company']) == '')
									{
										$order['ao_delivery_company'] = $delivery;
										continue;
									}

								}

								$order['ao_delivery_country_id'] = 1;
							}
							else
								$order['ao_delivery'] = 0;


							preg_match_all('`br>(?<value>.*)(<|$)`iU', $message['params']['Dane do fatkury VAT'], $matches);
							if (!$matches['value'])
								preg_match_all('`br/>(?<value>.*)(<|$)`iU', $message['params']['Dane do fatkury VAT'], $matches);
							if (!$matches['value'])
								preg_match_all('`br />(?<value>.*)(<|$)`iU', $message['params']['Dane do fatkury VAT'], $matches);
							
							if ($matches['value'])
							{
								$order['ao_invoice'] = 1;
								$deliveries = array_reverse($matches['value']);

								foreach($deliveries as $delivery)
								{
									$delivery = trim($delivery);
									
									if (strpos($delivery, 'NIP') !== false)
									{
										$order['ao_invoice_taxnumber'] = $delivery;
										continue;
									}

									preg_match('`^(?<postcode>[0-9\-]+) (?<city>.*)$`iU', $delivery, $matches);
									if ($matches)
									{
										$order['ao_invoice_postcode'] = $matches['postcode'];
										$order['ao_invoice_city'] = $matches['city'];
										continue;
									}

									if (trim($order['ao_invoice_postcode']) != '' && trim($order['ao_invoice_address']) == '')
									{
										$order['ao_invoice_address'] = $delivery;
										continue;
									}

									if (trim($order['ao_invoice_address']) != '' && trim($order['ao_invoice_firstname']) == '')
									{
										preg_match('`^(?<imie>[^\s]+) (?<nazwisko>.*)$`iU', $delivery, $matches);
										if ($matches)
										{
	// 										print_r($matches);die;
											$order['ao_invoice_firstname'] = $matches['imie'];
											$order['ao_invoice_lastname'] = $matches['nazwisko'];

										}
										continue;
									}

									if (trim($order['ao_invoice_firstname']) != '' && trim($order['ao_invoice_company']) == '')
									{
										$order['ao_invoice_company'] = $delivery;
										continue;
									}

								}

								$order['ao_invoice_country_id'] = 1;
							}
							else
								$order['ao_invoice'] = 0;

// 							print_r($order->asArray());die;
							$order->save();


							$ao_id = (int)$order['ao_id'];

							if ($ao_id > 0 && $auction_id > 0)
							{
								M('AllegroWebApiBid')->db->execQuery("UPDATE allegro_bid SET ao_id = {$ao_id} WHERE user_id = {$user_id} AND auction_id = {$auction_id};");
								consoleLog("Zapisanie FOD (shop_id: {$shop_id}, auction_id: {$auction_id}, user_id: {$user_id})");

								$doZapisania--;
							}
							else
								consoleLog("Wystąpił BŁĄD zapisu FOD (shop_id: {$shop_id}, auction_id: {$auction_id}, user_id: {$user_id})");
						}
						else
						{
							consoleLog("FOD już istniał (shop_id: {$shop_id}, auction_id: {$auction_id}, user_id: {$user_id})");
							$doZapisania--;
						}
					}

	// 				print_r($user->asArray());
	// 				print_r($aukcja->asArray());

					if ($doZapisania == 0)
						imap_delete($mbox, $i);
				}
			}
		}
	}
}

imap_expunge($mbox);
imap_close($mbox);



/// powiadomienie sklepów, że mają co ściągać
$shops = M('AllegroWebApiOrder')->getShopsNotNotices();
$shopCounter = count($shops);
$counter = 1;
if ($shops)
foreach($shops as $shop)
{
	$shop_id = (int)$shop['shop_id'];

	$shops = M('Shop')->find($shop_id);
	if ($shops)
	{
		consoleLog("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter).");
		$shop_url = $shops[0]['shop_url'] . "/allegro.php?key=1";
		$ret = file_get_contents($shop_url);

		$redPards = explode("|", $ret);

		if ($redPards[0] == "OK")
		{
			$ao_ids = json_decode($redPards[1]);
			consoleLog("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => OK (count: ".count($ao_ids).").");
			if (is_array($ao_ids))
			foreach($ao_ids as $ao_id)
			{
				$orders = M('AllegroWebApiOrder')->find((int)$ao_id);
				if ($orders)
				{
					$orders[0]['ao_get_by_shop'] = 1;
					$orders[0]->save();
				}
			}
		}
		else
		{
			consoleLog("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => ERROR ({$shop_url})!");
			#$mailer->notifyAboutProblem("Wymuszenie ze sklepu pobrania danych (shop_id: {$shop_id}) ($counter z $shopCounter) => ERROR ({$shop_url})! <br /><br /><pre>".print_r($ret, true)."</pre>");
			var_dump($ret);
		}
	}
	$counter++;
}
