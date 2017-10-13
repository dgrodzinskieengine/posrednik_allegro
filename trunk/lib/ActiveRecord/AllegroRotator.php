<?php

class ActiveRecord_AllegroRotator extends Core_ActiveRecord
{
	public $tableName = "allegro_rotator";
	public $primaryKey = "ar_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		'AllegroWebApiShopAuction' => '<(ar_auction_ts_id=auction_id)'
	);

	public function checkAndRegenerate($user_id, $auction_id, $counter, $auction_type)
	{
		$user_id = (int)$user_id;
		$auction_id = (int)$auction_id;
		$counter = (int)$counter;

		if($auction_id == 0) {
			return false;
		}
		
// 		usleep(rand(100000, 5000000));

		# sprawdzenie czy istnieje aktualny zestaw
		$ilosc = $this->db->getValue("SELECT count(*) FROM allegro_rotator WHERE auction_id = {$auction_id} AND ar_auction_ts_type = '{$auction_type}' AND ar_expiration_date > now();");

		if ($ilosc <= 0) {
			/// chodzi o to, aby regeneracja odbywała się tylko dla pierwszej reklamki z zestawu
			if ($counter == 1) {

				/// skasowanie wyexpirowanego zestawu (jeżeli istnieje)
				$this->db->execQuery("DELETE FROM allegro_rotator WHERE auction_id = {$auction_id};");

				/// domyślny czas przechowywania w cache'u (30 minut)
				$expiration_date = date("Y-m-d H:i:s", time() + 30 * 60);
				if ($auction_type == 'active') {
					/// przygotowanie zestawu aukcji aktywnych (max 12)
					$shopAuctions = M('AllegroWebApiShopAuction')->find(sql(array(
							"
						user_id = %user_id
						AND auction_id <> %auction_id
						AND auction_image_url <> ''
						AND 
							(auction_image_url LIKE %image_pattern1
							OR auction_image_url LIKE %image_pattern2
							OR auction_image_url LIKE %image_pattern3
							)
						AND auction_active = 1
						AND auction_hidden = 0
					",
							"user_id" => $user_id,
							"auction_id" => $auction_id,
							"image_pattern1" => '%.jpg',
							"image_pattern2" => '%.png',
							"image_pattern3" => '%allegroimg%'
						))
						, array(
							"order" => "rand()",
							"limit" => "12"
						));
				} elseif ($auction_type == 'bestsell') {
					/// przygotowanie zestawu aukcji bestselerow (max 12)
					$query = "SELECT asa.*, sum(ab.ab_quantity_payed) as order_count_products
								FROM allegro_shop_auction as asa
								INNER JOIN allegro_bid as ab USING (auction_id)
								WHERE
								asa.user_id = {$user_id} AND
								asa.auction_id <> {$auction_id} AND
								asa.auction_image_url <> '' AND
								asa.auction_active = 1 AND
								asa.auction_hidden = 0 AND
								(auction_image_url LIKE '%.jpg'
								OR auction_image_url LIKE '%.png'
								OR auction_image_url LIKE '%allegroimg%'
								)
								GROUP BY asa.auction_id, asa.products_variant_base_id
								ORDER BY order_count_products DESC
								LIMIT 12";
					$shopAuctions = M('AllegroWebApiShopAuction')->findBySql($query);
				}

				if ($shopAuctions) {

					$i = 1;
					foreach ($shopAuctions as $shopAuction) {
						$allegroRotator = M('AllegroRotator')->create();
						$allegroRotator['auction_id'] = $auction_id;
						$allegroRotator['ar_auction_ts_id'] = $shopAuction['auction_id'];
						$allegroRotator['ar_auction_ts_type'] = ($auction_type == 'active' ? "active" : "bestsell");
						$allegroRotator['ar_order'] = $i;
						$allegroRotator['ar_expiration_date'] = $expiration_date;
						$allegroRotator->save();
						$i++;
					}
				}

				/// przygotowanie zestawu aukcji aktywnych (max 12)
//				$shopAuctions = M('AllegroWebApiShopAuction')->find(sql(array("
//						user_id = %user_id
//						AND auction_id <> %auction_id
//						AND auction_image_url <> ''
//						AND (auction_image_url LIKE '%.jpg' OR auction_image_url LIKE '%.png')
//						AND auction_active = 0
//						AND auction_hidden = 0
//						AND date_stop > %date_stop
//					",
//						"user_id" => $user_id,
//						"auction_id" => $auction_id,
//						"date_stop" => date("Y-m-d H:i:s", time() - 60*60*24*30))
//					), array("order" => "rand()", "limit" => "12"));
//
//				if ($shopAuctions)
//				{
//					
//					$i = 1;
//					foreach($shopAuctions as $shopAuction)
//					{
//						$allegroRotator = M('AllegroRotator')->create();
//						$allegroRotator['auction_id'] = $auction_id;
//						$allegroRotator['ar_auction_ts_id'] = $shopAuction['auction_id'];
//						$allegroRotator['ar_auction_ts_type'] = "archive";
//						$allegroRotator['ar_order'] = $i;
//						$allegroRotator['ar_expiration_date'] = $expiration_date;
//						$allegroRotator->save();
//						$i++;
//					}
//				}
			} else {
				sleep(3);
			}
		}

		$this->db->execQuery("DELETE FROM allegro_rotator WHERE ar_expiration_date < now();");
	}
}

