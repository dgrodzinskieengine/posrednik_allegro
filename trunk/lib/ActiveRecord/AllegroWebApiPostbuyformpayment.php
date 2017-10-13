<?php

class ActiveRecord_AllegroWebApiPostbuyformpayment extends Core_ActiveRecord
{
	public $tableName = "allegro_postbuyformpayment";
	public $primaryKey = "postbuyformpayment_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function getShopsNotNotices()
	{
		return $this->db->getAssoc("
				SELECT DISTINCT
					allegro_postbuyformdata.shop_id
				FROM
					allegro_postbuyformpayment
						INNER JOIN allegro_postbuyformdata USING(postbuyform_id)
				WHERE
					allegro_postbuyformpayment.postbuyformpayment_get_by_shop = 0
					AND postbuyformpayment_recive_date > NOW() - INTERVAL 10 day
				ORDER BY
					allegro_postbuyformdata.shop_id;
			");
	}

	public function find($cond = "true", $options = array())
	{
		$options['order'] = "postbuyformpayment_id DESC";
		return parent::find($cond, $options);
	}

}

