<?php

class ActiveRecord_AllegroWebApiPostbuyformdata extends Core_ActiveRecord
{
	public $tableName = "allegro_postbuyformdata";
	public $primaryKey = "postbuyform_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"AllegroWebApiPostbuyformadr" => "<*(postbuyform_id)",
		"AllegroWebApiPostbuyformitem" => "<*(postbuyform_id)",
		"AllegroWebApiShopSettings" => "<(shop_id)",
		"AllegroWebApiPostbuyformpayment" => "<(postbuyform_id)",
		"AllegroWebApiShipment" => "<(shipment_id)",
	);

	public function getShopsNotNotices()
	{
		return $this->db->getAssoc("
			SELECT DISTINCT
				shop_id
			FROM
				allegro_postbuyformdata
				INNER JOIN shop USING (shop_id)
			WHERE
				postbuyform_get_by_shop = 0
				AND shop_active = 1
			ORDER BY
				shop_id;");
	}

	public function setPostbuyformStatus($postbuyform_id, $shop_id, $status){
	    return $this->db->execQuery("
	        UPDATE allegro_postbuyformdata SET postbuyform_get_by_shop = $status WHERE postbuyform_id = $postbuyform_id AND shop_id = $shop_id;
	    ");
    }

	function getNext_postbuyformadr_id()
	{
		$this->db->execQuery("INSERT INTO allegro_postbuyformdata_seq (postbuyformadr_id) VALUES (NULL);");
		return $this->db->lastInsertId();
	}

}

