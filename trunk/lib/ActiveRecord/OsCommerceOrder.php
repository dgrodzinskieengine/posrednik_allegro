<?php

class ActiveRecord_OsCommerceOrder extends Core_ActiveRecord
{
	public $tableName = "orders";
	public $primaryKey = "orders_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceCustomer" => "<(customers_id)",
		"OsCommerceOrderProduct" => "<*(orders_id)",
		"OsCommerceOrderTotal" => "<*(orders_id)",
		"OsCommerceOrderStatusHistory" => "<*(orders_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>