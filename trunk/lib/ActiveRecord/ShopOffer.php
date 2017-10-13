<?php

class ActiveRecord_ShopOffer extends Core_ActiveRecord
{
	public $tableName = "shop_offer";
	public $primaryKey = "shop_offer_id";		// nie potrzebne

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"shop" => "<(shop_id)"
	);


}

?>