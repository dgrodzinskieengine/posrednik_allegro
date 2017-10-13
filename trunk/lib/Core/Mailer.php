<?php

require(DIR."/phpMailer/class.phpmailer.php");

class Core_Mailer extends PHPMailer
{
	public function notifyAboutProblem($content)
	{
		$this->ClearAddresses();
		$this->Subject = "Pośrednik Allegro zawiadamia o błędzie";
		$this->Body = $content;
		$this->AddAddress('pawel@walaszek.pl', 'Paweł Walaszek');
		$this->Timeout = 30;

		if ($this->Send())
		{
			//echo "OK, mail sent: {$this->Subject}\n";
			return true;
		}
		else
		{
			echo "ERROR, mail not sent: {$this->Subject}, error: {$this->ErrorInfo}\n";
			return false;
		}
	}
// 	public function notifyAllegroBiders($shop_id, $user_id)
// 	{
// 		$args = array();
// 
// 		$args['shop_id'] = $shop_id;
// 
// 		$shops = M('Shop')->find($shop_id);
// 		if ($shops)
// 		{
// 			$args['shop_name_full'] = $shops[0]['shop_name_full'];
// 			$args['shop_url'] = $shops[0]['shop_url'];
// 			$shopSettings = $shops[0]->AllegroWebApiShopSettings;
// 			$args['shop_user_id'] = $shopSettings['user_id'];
// 			$args['shop_rating'] = $shopSettings['user_rating'];
// 
// 			$users = M('AllegroWebApiUser')->findByUserId($user_id);
// 			if ($users)
// 			{
// 				$user_id = $users[0]['user_id'];
// 				$args['user'] = $users[0]->asArray();
// 
// 				$bids = M('AllegroWebApiBid')->findByUserIdNotSendedToShop($shop_id, $user_id);
// 				if ($bids)
// 				{
// 					$country_id = $args['country_id'] = $bids[0]['country_id'];
// 
// 					if ($country_id == 228)
// 					{
// 						$args['allegro_url'] = 'www.testwebapi.pl';
// 						$args['allegro_login'] = $shopSettings['login_testwebapi'];
// 					}
// 					if ($country_id == 1)
// 					{
// 						$args['allegro_url'] = 'www.allegro.pl';
// 						$args['allegro_login'] = $shopSettings['login_allegro'];
// 					}
// 
// 					$args['bids'] = array();
// 					$i = 0;
// 					$args['sum'] = 0;
// 
// 					foreach($bids as $bid)
// 					{
// 						$auction = $bid->AllegroWebApiShopAuction->asArray();
// 						$args['bids'][$i]['auction_name'] = $auction['auction_name'];
// 						$args['bids'][$i]['auction_id'] = $auction['auction_id'];
// 						$args['bids'][$i]['price'] = strtr($bid['ab_price'],'.',',');
// 						$args['bids'][$i]['quantity'] = $bid['ab_quantity'];
// 						$args['sum'] += $bid['ab_price']*$bid['ab_quantity'];
// 
// 						$i++;
// 					}
// 					$args['sum'] = round($args['sum'], 2);
// 
// 					$args['form_hostnam'] = DOMENA;
// 
// 					$this->smarty->assign($args);
// 
// 					$this->ClearAddresses();
// 					$this->Subject = "Forumlarz wyboru formy płatności i transportu";
// 					$this->Body = $this->smarty->fetch("Theme/Mail/Mail.notifyAllegroBiders.tpl");
// 					$this->AddAddress($args['user']['email'], $args['user']['first_name']." ".$args['user']['last_name']);
// 					$this->Timeout = 30;
// 
// 					if ($this->Send())
// 					{
// 						//echo "OK, mail sent: {$this->Subject}\n";
// 						return true;
// 					}
// 					else
// 					{
// 						echo "ERROR, mail not sent: {$this->Subject}, error: {$this->ErrorInfo}\n";
// 						return false;
// 					}
// 				}
// 			}
// 		}
// 	}

	// poniżej pozostaw bez zmian (ewentualnie skonfiguruj)
	static private $thisInstance = null;

	public function __construct()
	{
		$this->From = MAIL_FROM;
		$this->FromName = MAIL_FROMNAME;
		$this->Host = MAIL_SMTP_HOST;
		$this->Mailer = MAIL_MAILER;
		$this->Username = MAIL_USERNAME;
		$this->Password = MAIL_PASSWORD;
		$this->SMTPAuth = MAIL_SMTP_AUTH;
		$this->SMTPSecure = MAIL_SMTP_SECURE;
		$this->Port = MAIL_SMTP_PORT;
		$this->CharSet = 'utf-8';
		$this->Encoding = 'base64';
		$this->isHTML(true);

		global $smarty;
		$this->smarty = $smarty;
	}

	static public function getInstance()
	{
		if(!isset(self::$thisInstance) || self::$thisInstance == null)
		{
			self::$thisInstance = new Core_Mailer();
		}

		return self::$thisInstance;
	}

}

?>