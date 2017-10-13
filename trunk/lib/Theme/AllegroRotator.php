<?php

class Theme_AllegroRotator extends Core_Template
{
	public function picture($args)
	{
		// l(urlencode('mulan_pl'));
		// l(DBG);
		// l($args);

		$login_allegro = '';
		if (isset($args['login_allegro'])) {
			$login_allegro = preg_replace('`^us_all_`', '', $args['login_allegro']);
		}

		$shop_name = '';
		if (isset($args['shop_name'])) {
			$shop_name = $args['shop_name'];
		}

		$auction_type = $args['auction_type'];
		$counter = $args['counter'];

		// to w zasadzie można by wyciągnąć do parametrów
		$width = 250;
		$width2 = $width * 2;
		$height = 300;
		$height2 = $height * 2;

		$type = "crop";
		if (isset($args['type'])) {
			$type = $args['type'];
		}

//		$user_id = M('AllegroWebApiShopSettings')->getAllegroUserIdByLoginAllegroOrShopName($login_allegro, $shop_name);		

//		if ($user_id == 0)
//			die('Błędna nazwa użytkownika lub sklepu!');
		if (DBG) {
//$_SERVER['HTTP_REFERER'] = '-i5661014136';
		}

		$auction_id = self::getAuctionIDFromReferer();

		/// jeżeli referer nieznany
//		if ($auction_id == 0)
//			 $auction_id = (int)M('AllegroRotator')->db->getValue("SELECT auction_id FROM (SELECT auction_id FROM `allegro_shop_auction` WHERE `user_id` = {$user_id} ORDER BY auction_active DESC, date_start DESC LIMIT 50) a ORDER BY RAND() LIMIT 1;");
		$user_id = M('AllegroWebApiShopAuction')->first(sql(array(
			'auction_id = %auction_id',
			'auction_id' => $auction_id
		)));
//		l($user_id);
		if ($user_id) {
			$user_id = $user_id['user_id'];
		}
//l($user_id);
		if (!$user_id) {
			$user_id = M('AllegroWebApiShopSettings')->getAllegroUserIdByLoginAllegroOrShopName($login_allegro, $shop_name);
		}

		if ($user_id == 0) {
			die('Błędna nazwa użytkownika lub sklepu!');
		}
		// l($auction_id);

		M('AllegroRotator')->checkAndRegenerate($user_id, $auction_id, $counter, $args['auction_type']);

		$auctionRotators = M('AllegroRotator')->find($s = sql(array(
			'auction_id = %auction_id AND ar_order = %ar_order AND ar_auction_ts_type = %ar_auction_ts_type',
			'auction_id' => $auction_id,
			'ar_order' => $counter,
			'ar_auction_ts_type' => $args['auction_type']
		)));
		if ($auctionRotators) {
			$auctionRotator = $auctionRotators[0];
		} else {
			header('Content-Type: image/gif');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			echo file_get_contents(DIR . "/../temp/auction_rotator/pixel.gif");
			die();
		}
		$allegroShopAuction = $auctionRotator->AllegroWebApiShopAuction;
		if (method_exists($this, 'generateSpecialTemplate_' . $user_id)) {
			$this->generateSpecialTemplate_41200461($allegroShopAuction);
		} else {
			$temp_image_file = DIR . "/../temp/auction_rotator/{$allegroShopAuction['auction_id']}-{$type}.jpg";
			if (!file_exists($temp_image_file) || filemtime($temp_image_file) < time() - 60 * 60 * 24 * 7) {
				if ($allegroShopAuction['auction_price'] > 10) {
					$allegroShopAuction['auction_price'] = round($allegroShopAuction['auction_price'], 0);
				}
				$caption = $allegroShopAuction['auction_name'];//. " (".strtr($allegroShopAuction['auction_price'], ".", ",")."zł)";
				//$caption .= " (" . $allegroShopAuction['auction_id'] . ")";

				// 		$source = "http://any.ok6.pl/images/KONTRI/brubeck-feel-bra-biustonosz.jpg";
				// 		$source = "http://www.kwiateo.pl/product_picture.php?products_id=306&order=2&width=500&height=400";
				$source = $allegroShopAuction['auction_image_url'];

				$cmd = array();
				$cmd[] = "convert -strip '{$source}' png:-";

				switch ($type) {
					case "crop":
						$cmd[] = "convert - -resize 'x{$height2}' -resize '{$width}x<' -resize 50% -gravity center -crop {$width}x{$height}+0+0 +repage png:-";
						$cmd[] = "convert - -resize '{$width2}x' -resize 'x{$height2}<' -resize 50% -gravity center -crop {$width}x{$height}+0+0 +repage png:-";
						break;
					default:
						$cmd[] = "convert - -resize '{$width}x{$height}>' -size {$width}x{$height} xc:white +swap -gravity center -composite png:-";
						break;

				}

				$cmd[] = "convert - -fill '#0008' -draw 'rectangle 0,240,250,300' png:-";
				$cmd[] = "convert -background '#0000' -fill white -size 250x60 -pointsize 17 -gravity center caption:'{$caption}' - +swap -gravity south -composite png:-";
				$cmd[] = "convert - -quality 85 jpg:'{$temp_image_file}'";

				$cmd = implode(" | ", $cmd);
				$lastLine = system($cmd, $return);
			}

			header('Content-Type: image/jpeg');
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			print file_get_contents($temp_image_file);
		}
		die;
	}

	public function redirect($args)
	{
		$login_allegro = '';
		if (isset($args['login_allegro'])) {
			$login_allegro = preg_replace('`^us_all_`', '', $args['login_allegro']);
		}

		$shop_name = '';
		if (isset($args['shop_name'])) {
			$shop_name = $args['shop_name'];
		}

		$action_type = $args['action_type'];
		$counter = $args['counter'];

//		$user_id = M('AllegroWebApiShopSettings')->getAllegroUserIdByLoginAllegroOrShopName($login_allegro, $shop_name);

//		if ($user_id == 0)
//			die('Błędna nazwa użytkownika lub sklepu!');
//l($_SERVER);

		$auction_id = self::getAuctionIDFromReferer();

		/// jeżeli referer nieznany
//		if ($auction_id == 0)
//			 $auction_id = (int)M('AllegroRotator')->db->getValue("SELECT auction_id FROM (SELECT auction_id FROM `allegro_shop_auction` WHERE `user_id` = {$user_id} ORDER BY auction_active DESC, date_start DESC LIMIT 50) a ORDER BY RAND() LIMIT 1;");

// 		if ($auction_id == 0)
// 			die("Brak informacji o auction_id pochodzacych z HTTP_REFERER!");

		$user_id = M('AllegroWebApiShopAuction')->first(sql(array(
			'auction_id = %auction_id',
			'auction_id' => $auction_id
		)));
//              l($user_id);
		if ($user_id) {
			$user_id = $user_id['user_id'];
		}
//l($user_id);
		if (!$user_id) {
			$user_id = M('AllegroWebApiShopSettings')->getAllegroUserIdByLoginAllegroOrShopName($login_allegro, $shop_name);
		}

		if ($user_id == 0) {
			die('Błędna nazwa użytkownika lub sklepu!');
		}


// l();die;
// l($auction_id);
// l($counter);
// l($action_type);

		$auctionRotators = M('AllegroRotator')->find(sql(array(
			'auction_id = %auction_id AND ar_auction_ts_type = %ar_auction_ts_type AND ar_order = %ar_order',
			'auction_id' => $auction_id,
			'ar_order' => $counter,
			'ar_auction_ts_type' => $action_type
		)));
// 		l($auctionRotators);
		if ($auctionRotators) {
			$auctionRotator = $auctionRotators[0];
		} else {
// 			l($args);
// 			l($auction_id);
			die('Nie znaleziono aukcji do zareklamowania!');
		}

		$allegroShopAuction = $auctionRotator->AllegroWebApiShopAuction;
//  		l($allegroShopAuction->asArray()); die;

		$url = "http://www.allegro.pl/";
		if ($allegroShopAuction['country_id'] == 228) {
			$url = "http://www.testwebapi.pl/";
		}

		$url .= "show_item.php?item=" . $allegroShopAuction['auction_id'];

		header("Location: {$url}");
		die;
	}

	public function generateSpecialTemplate_41200461($allegroShopAuction)
	{
		$auctionImage = ImageCreateFromJPEG($allegroShopAuction['auction_image_url']);
		$linkImage = ImageCreateFromJPEG(dirname(__FILE__) . '/raquo.jpg');

		// Allocate A Color For The Text
		$fontColorBlack = imagecolorallocate($auctionImage, 0, 0, 0); //black
		$fontColorGold = imagecolorallocate($auctionImage, 185, 148, 93); //gold
		$white = imagecolorallocate($auctionImage, 255, 255, 255);

		$orig_width = imagesx($auctionImage);
		$orig_height = imagesy($auctionImage);

		$width = 200;
		$height = 252;

		// Create new image to display
		$auctionImageResize = imagecreatetruecolor($width, $height + 80);
		imagefill($auctionImageResize, 0, 0, $white);

		// Create new image with changed dimensions
		imagecopyresized($auctionImageResize, $auctionImage,
			0, 0, 0, 0,
			$width, $height,
			$orig_width, $orig_height);

		// Set Path to Font File
		$font_path_georgia = dirname(__FILE__) . '/Georgia.ttf';
		$font_path_ubuntu = dirname(__FILE__) . '/ubuntu_regular.ttf';
		$font_path_arial = dirname(__FILE__) . '/arial.TTF';

		// maksymalna dlugosc tytulu w jednej lini
		$auctionName = explode(" ",$allegroShopAuction['auction_name']);
		$firstLineName = "";
		$secondLineName = "";
		$i = 0;
		foreach ($auctionName as $id => $word) {
			if (strlen($firstLineName.$word) <= 17) {
				if ($i == 0) {
					$firstLineName .= $word;
				} else {
					$firstLineName .= " ".$word;
				}
				unset($auctionName[$id]);
			}
			$i++;
		}
		$i = 0;
		foreach ($auctionName as $id => $word) {
			if (strlen($secondLineName.$word) <= 17) {
				if ($i == 0) {
					$secondLineName .= $word;
				} else {
					$secondLineName .= " ".$word;
				}
				unset($auctionName[$id]);
			}
			$i++;
		}


		// Set Text to Be Printed On Image
		$auctionPrice = str_replace(".", ",", $allegroShopAuction['auction_price']);
		$leftMarginFirstLine = floor((21 - strlen($firstLineName)) / 2) * 10;
		$leftMarginSecondLine = floor((21 - strlen($secondLineName)) / 2) * 10;
		if ($leftMarginFirstLine < 15) {
			$leftMarginFirstLine = 3;
		}

		// Print Text On Image
		imagettftext($auctionImageResize, 13, 0, $leftMarginFirstLine, 270, $fontColorGold, $font_path_georgia, $firstLineName);
		imagettftext($auctionImageResize, 13, 0, $leftMarginSecondLine, 292, $fontColorGold, $font_path_georgia, $secondLineName);

		$priceLength = strlen($auctionPrice);
		switch ($priceLength) {
			case '3':
				imagettftext($auctionImageResize, 23, 0, 80, 325, $fontColorBlack, $font_path_arial, $auctionPrice);
				break;
			case '4':
				imagettftext($auctionImageResize, 23, 0, 70, 325, $fontColorBlack, $font_path_arial, $auctionPrice);
				break;
			case '5':
				imagettftext($auctionImageResize, 23, 0, 60, 325, $fontColorBlack, $font_path_arial, $auctionPrice);
				break;
			case '6':
				imagettftext($auctionImageResize, 23, 0, 50, 325, $fontColorBlack, $font_path_arial, $auctionPrice);
				break;
			case '7':
				imagettftext($auctionImageResize, 23, 0, 40, 325, $fontColorBlack, $font_path_arial, $auctionPrice);
				break;
			default:
				imagettftext($auctionImageResize, 23, 0, 30, 325, $fontColorBlack, $font_path_arial, $auctionPrice);
				break;
		}
		$marge_right = 0;
		$marge_bottom = 6;
		$sx = imagesx($linkImage);
		$sy = imagesy($linkImage);
		imagecopy($auctionImageResize, $linkImage, imagesx($auctionImageResize) - $sx - $marge_right, imagesy($auctionImageResize) - $sy - $marge_bottom, 0, 0, imagesx($linkImage), imagesy($linkImage));

		header('Content-Type: image/jpeg');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		imagejpeg($auctionImageResize, NULL, 100);
	}

	private static function getAuctionIDFromReferer()
	{
		$auction_id = 0;
		preg_match('`item([0-9]+)_`i', $_SERVER['HTTP_REFERER'], $dopasowania);
		if ($dopasowania[1]) {
			$auction_id = $dopasowania[1];
		}

		/// jeżeli referer standardowym URLem
		if ($auction_id == 0) {
			preg_match('`item=([0-9]+)`i', $_SERVER['HTTP_REFERER'], $dopasowania);
			if ($dopasowania[1]) {
				$auction_id = $dopasowania[1];
			}
		}
		/// jeżeli referer urlem z iframe'a
		if($auction_id == 0) {
			preg_match('`([0-9]+)\?iframe`i', $_SERVER['HTTP_REFERER'], $dopasowania);
			if($dopasowania[1]) {
				$auction_id = $dopasowania[1];
			}
		}

		if ($auction_id == 0) {
			preg_match('`\-i([0-9]+)`i', $_SERVER['HTTP_REFERER'], $dopasowania);
			if ($dopasowania[1]) {
				$auction_id = $dopasowania[1];
			}
		}

		return $auction_id;
	}
}
