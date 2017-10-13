<?php

class ActiveRecord_AllegroWebApiCategory extends Core_ActiveRecord
{
	public $tableName = "allegro_webapi_category";
	public $primaryKey = "awa_category_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function findByCountryIdAndCategoryId($country_id, $category_id)
	{
		return $this->find(sql(array(
				"country_id = %country_id
				AND category_id = %category_id",
				"country_id" => $country_id,
				"category_id" => $category_id
			)));
	}

// 	function find($cond = "true", $options = array()) {
// 		if (!isset($options['order']))
// 				$options['order'] = "parent_id ASC";
// 		return parent::find($cond, $options);
// 	}

}

?>