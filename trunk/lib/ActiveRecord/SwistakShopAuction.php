<?php

class ActiveRecord_SwistakShopAuction extends Core_ActiveRecord
{
	public $tableName = "swistak_shop_auction";
	public $primaryKey = "ssa_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		'SwistakShopSettings' => '<(shop_id)',
		'SwistakShopAuctionRenew' => '<(auction_id)'
	);

	public function findActive()
	{
		return $this->find("auction_active = 1 OR date_stop > NOW() - INTERVAL 3 DAY", array('order' => 'shop_id ASC, country_id ASC'));
// 		return $this->find(
// 				'date_stop > "'.date("Y-m-d H:i:s", time()-60*60*8).'"',
// 				array(
// 					'order' => 'shop_id, country_id'
// 				)
// 			);
	}

}
