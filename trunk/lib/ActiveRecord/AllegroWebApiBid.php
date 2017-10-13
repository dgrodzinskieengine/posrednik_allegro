<?php

class ActiveRecord_AllegroWebApiBid extends Core_ActiveRecord
{
	public $tableName = "allegro_bid";
	public $primaryKey = "ab_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"AllegroWebApiShopAuction" => "<(auction_id)",
		"AllegroWebApiOrder" => "<(ao_id)",
		"AllegroWebApiUser" => "<(user_id)"
	);

	public function findByAuctionIdAndUserId($auction_id, $user_id)
	{
		return $this->find(sql(array("
				auction_id = %auction_id
				AND user_id = %user_id
			",
				"auction_id" => $auction_id,
				"user_id" => $user_id
			)));
	}

	public function getAuctionsByShopAndCountryNotSendedToShop()
	{
		return $this->db->getAssoc("
				SELECT
					tab.shop_id, tab.country_id, tab.auction_id, asa.user_id
				FROM
					(
						(
							SELECT
								ab.shop_id,
								ab.country_id,
								ab.auction_id
							FROM
								allegro_bid ab
							WHERE
								ab.ab_quantity_payed - ab.ab_quantity < 0
						)
						UNION
						(
							SELECT
								ab.shop_id,
								ab.country_id,
								ab.auction_id
							FROM
								(SELECT * FROM allegro_bid WHERE ab_quantity_payed - ab_quantity = 0) as ab
									INNER JOIN allegro_postbuyformitem apbfi ON apbfi.postbuyformit_auction_id = ab.auction_id
									LEFT JOIN allegro_postbuyformdata apbfd ON apbfd.postbuyform_id = apbfi.postbuyform_id
							WHERE
								apbfd.postbuyform_get_by_shop = 0
						)
					) as tab
					LEFT JOIN
						allegro_shop_auction asa ON asa.auction_id = tab.auction_id
				ORDER BY
					tab.shop_id,
					asa.user_id,
					tab.country_id,
					tab.auction_id;
			");
	}

	public function findByUserIdNotSendedToShop($shop_id, $user_id)
	{
		return $this->find(sql(array("
				user_id = %user_id
				AND ab_sended_to_shop = 0
				AND shop_id = %shop_id
			",
				"user_id" => $user_id,
				"shop_id" => $shop_id
			)), array('order' => 'ab_id'));
	}

	public function getShopIdCountryIdAuctionIdWithoutFeedbackGrouped()
	{
		return $this->findBySql("
			SELECT
				shop_id,
				country_id,
				auction_id
			FROM
				allegro_bid
			WHERE
				(
					fb_recvd = 0
					OR fb_gave = 0
				)
				AND ab_bid_date > NOW() - INTERVAL 1 month
			GROUP BY
				shop_id,
				country_id,
				auction_id
			ORDER BY
				shop_id,
				country_id,
				auction_id DESC;
			");
	}

	public function findWithoutFod()
	{
		die('Funkcja ActiveRecord_AllegroWebApiBid::findWithoutFod wyłączona - FOD już nie istnieje!');
// 		return $this->find("ab_id IN (SELECT ab.ab_id FROM allegro_bid ab LEFT JOIN allegro_order ao ON ab.auction_id = ao.auction_id AND ab.user_id = ao.user_id WHERE ao.ao_id IS NULL)", array('order' => 'ab_id'));
	}

	public function findNotPayed()
	{
		return $this->find("ab_quantity_payed - ab_quantity < 0 AND ab_bid_date > NOW() - INTERVAL 5 DAY", array('order' => 'ab_id'));
	}

	public function getAuctionAndUserItemsCount($allegro_id, $user_id)
	{
		$count = $this->findBySql("SELECT SUM(ab_quantity) count FROM allegro_bid WHERE auction_id = '{$allegro_id}' AND user_id = '{$user_id}'");
		return $count['count'];
	}
}

