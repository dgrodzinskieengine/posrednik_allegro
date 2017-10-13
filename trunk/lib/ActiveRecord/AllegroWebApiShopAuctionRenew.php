<?php

class ActiveRecord_AllegroWebApiShopAuctionRenew extends Core_ActiveRecord
{
	public $tableName = "allegro_shop_auction_renew";
	public $primaryKey = "asar_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		'AllegroWebApiShopAuction' => '<(auction_id)',
		'AllegroWebApiShopSettings' => '<(shop_id)'
	);

}
