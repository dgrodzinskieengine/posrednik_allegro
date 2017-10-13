<?php

class ActiveRecord_AllegroWebApiShopAuctionParam extends Core_ActiveRecord
{
	public $tableName = "allegro_shop_auction_params";
	public $primaryKey = "asap_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

}

