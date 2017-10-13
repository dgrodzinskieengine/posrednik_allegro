<?php

class ActiveRecord_Shop extends Core_ActiveRecord
{
	public $tableName = "shop";
	public $primaryKey = "shop_id";        // nie potrzebne

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"AllegroWebApiShopSettings" => "<(shop_id)"
	);

	public function getShopName($shop_id)
	{
		$shops = M('Shop')->find($shop_id);
		if ($shops)
			return $shops[0]['shop_name'];
	}

	public function isActive()
	{
		if(!empty($this['shop_id'])) {
			return (int)$this['shop_active'] == 1;
		}

		return false;
	}

	public function isSmsServiceActive()
	{
		if(!empty($this['shop_id'])) {
			return (int)$this['shop_active'] == 1 && (int)$this['shop_sms_enabled'] == 1;
		}

		return false;
	}
}
