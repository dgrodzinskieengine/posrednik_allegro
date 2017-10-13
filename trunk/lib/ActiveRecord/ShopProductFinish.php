<?php

class ActiveRecord_ShopProductFinish extends Core_ActiveRecord
{
	public $tableName = "shop_products_finished";
	public $primaryKey = "spf_id";		// nie potrzebne

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
// 		'AllegroWebApiShopAuction' => '<(auction_id)',
		'AllegroWebApiShopSettings' => '<(shop_id)'
	);

	public function save($options = array())
	{
		if (!isset($this->__dataChanged['update_timestamp']))
			$this->__dataChanged['update_timestamp'] = date("Y-m-d H:i:s");
		return parent::save($options);
	}

	public function AllegroWebApiShopAuctions()
	{
		if ($this['product_id'] > 0)
		{
			if ($this['kit_id'] > 0)
				return M('AllegroWebApiShopAuction')->find("shop_id = '{$this['shop_id']}' AND product_id = '{$this['product_id']}' AND kit_id = '{$this['kit_id']}' AND (auction_active = 1 OR date_stop > NOW() - INTERVAL 15 DAY)");
			else
				return M('AllegroWebApiShopAuction')->find("shop_id = '{$this['shop_id']}' AND product_id = '{$this['product_id']}' AND (auction_active = 1 OR date_stop > NOW() - INTERVAL 15 DAY)");
		}
	}
}
