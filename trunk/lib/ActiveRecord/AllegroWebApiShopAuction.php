<?php

class ActiveRecord_AllegroWebApiShopAuction extends Core_ActiveRecord
{
	public $tableName = "allegro_shop_auction";
	public $primaryKey = "asa_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		'AllegroWebApiShopSettings' => '<(shop_id)',
		'AllegroWebApiShopAuctionParam' => '<*(auction_id)',
		'AllegroWebApiShopAuctionRenew' => '<(auction_id)'
	);

	public function findActive($shopId = 0)
	{
		$shopId = (int)$shopId;
		if($shopId)
		{
			// print_r($this->find("shop_id = {$shopId} AND (auction_active = 1 OR date_stop > NOW() - INTERVAL 10 DAY)", array('order' => 'shop_id ASC, country_id ASC'))->asArray());die;
			return $this->find("shop_id = {$shopId} AND (auction_active = 1 OR date_stop > NOW() - INTERVAL 10 DAY)", array('order' => 'shop_id ASC, country_id ASC'));
		}
		else
		{
			return $this->find("auction_active = 1 OR date_stop > NOW() - INTERVAL 10 DAY", array('order' => 'shop_id ASC, country_id ASC'));	
		}
	}

	public function findProductsOnAuctions($shop_id, $active_only = true, $date_since = '')
	{
		$shop_id = (int)$shop_id;
		if($shop_id > 0) {
			$active_condition = '';
			if($active_only) {
				$active_condition = 'AND auction_active = 1';	
			}

			$date_since_condition = '';
			if($date_since) {
				$date_since = date('Y-m-d', strtotime(preg_replace('/[^0-9\-]/', '', $date_since)));
				if($date_since) {
					$date_since_condition = "AND insert_timestamp >= '{$date_since}'";
				}
			}
			
			return $this->db->getAssoc("SELECT product_id FROM allegro_shop_auction WHERE shop_id = {$shop_id} {$active_condition} {$date_since_condition} GROUP BY product_id");
		} else {
			return false;
		}
	}
}
