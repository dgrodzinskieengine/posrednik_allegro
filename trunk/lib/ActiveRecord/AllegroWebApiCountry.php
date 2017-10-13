<?php

class ActiveRecord_AllegroWebApiCountry extends Core_ActiveRecord
{
	public $tableName = "allegro_webapi_country";
	public $primaryKey = "awa_country_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function getCountryName($country_id)
	{
		$countriesAR = $this->findByCountryId($country_id);
		if ($countriesAR)
			return $countriesAR[0]['country_name'];
		else
			return "";
	}

}
