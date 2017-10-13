<?php

class ActiveRecord_AllegroWebApiSellForm extends Core_ActiveRecord
{
	public $tableName = "allegro_webapi_sellform";
	public $primaryKey = "awa_sellform_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function findByCountryIdAndCategoryId($country_id, $sellform_id)
	{
		return $this->find(sql(array(
				"country_id = %country_id
				AND sellform_id = %sellform_id",
				"country_id" => $country_id,
				"sellform_id" => $sellform_id
			)));
	}
}

?>