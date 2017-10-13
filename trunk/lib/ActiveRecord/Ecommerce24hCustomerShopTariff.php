<?php

class ActiveRecord_Ecommerce24hCustomerShopTariff extends Core_ActiveRecord
{
	public $tableName = "customers_shops_tariff";
	public $primaryKey = "customers_shops_tariff_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	function __construct($data = array()) {
		parent::__construct($data);

		$this->db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, EC24H_DB_PREF);
	}
}