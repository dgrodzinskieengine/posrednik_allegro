<?php

class ActiveRecord_AllegroWebApiOrder extends Core_ActiveRecord
{
	public $tableName = "allegro_order";
	public $primaryKey = "ao_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"AllegroWebApiBid" => "<*(ao_id)"
	);

	public function getShopsNotNotices()
	{
		return $this->db->getAssoc("SELECT DISTINCT shop_id FROM allegro_order WHERE ao_get_by_shop = 0 ORDER BY shop_id;");
	}
}

?>