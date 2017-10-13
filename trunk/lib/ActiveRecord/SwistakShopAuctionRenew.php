<?php

class ActiveRecord_SwistakShopAuctionRenew extends Core_ActiveRecord
{
	public $tableName = "swistak_shop_auction_renew";
	public $primaryKey = "ssar_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		'SwistakShopAuction' => '<(auction_id)',
		'SwistakShopSettings' => '<(shop_id)'
	);

}
