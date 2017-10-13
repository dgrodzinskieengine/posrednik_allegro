<?php

class ActiveRecord_OsCommerceCustomerInfo extends Core_ActiveRecord
{
	public $tableName = "customers_info";
	public $primaryKey = "customers_info_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>