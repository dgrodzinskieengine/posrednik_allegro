<?php

class ActiveRecord_OsCommerceOrderProduct extends Core_ActiveRecord
{
	public $tableName = "orders_products";
	public $primaryKey = "orders_products_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceOrder" => "<(orders_id)",
		"OsCommerceProduct" => "<(products_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>