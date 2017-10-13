<?php

class ActiveRecord_AllegroWebApiSellFormOption extends Core_ActiveRecord
{
	public $tableName = "allegro_webapi_sellform_option";
	public $primaryKey = "awa_sellform_option_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function findByCountryAndSellFormId($country_id, $sellform_id)
	{
		return $this->find(sql(array("
					country_id = %country_id
					AND sellform_id = %sellform_id
				",
				"country_id" => $country_id,
				"sellform_id" => $sellform_id
			)), array('order' => 'option_position'));
	}
}

?>