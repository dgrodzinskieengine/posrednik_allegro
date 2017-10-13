<?php

class ActiveRecord_AllegroWebApiShopSettings extends Core_ActiveRecord
{
	public $tableName = "allegro_shop_settings";
	public $primaryKey = "ass_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);


	public function getAllegroUserIdByLoginAllegroOrShopName($login_allegro = '', $shop_name = '')
	{
		$user_id = 0;
		
		if($login_allegro == '' && $shop_name != '') {
			$shop_id = (int)$this->db->getValue(sql(array("SELECT shop_id FROM shop WHERE shop_name = %shop_name", "shop_name" => $shop_name)));

			$user_id = (int)$this->db->getValue(sql(array("SELECT user_id FROM allegro_shop_settings WHERE shop_id = %shop_id ORDER BY ass_id LIMIT 1", "shop_id" => $shop_id)));
		} else {
			$user_id = (int)$this->db->getValue(sql(array("SELECT user_id FROM allegro_shop_settings WHERE login_allegro = %login_allegro", "login_allegro" => $login_allegro)));	
		}

		return $user_id;
	}
}
