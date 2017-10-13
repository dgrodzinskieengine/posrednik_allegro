<?php

class ActiveRecord_OsCommerceCustomer extends Core_ActiveRecord
{
	public $tableName = "customers";
	public $primaryKey = "customers_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceAddressBook" => "<(customers_default_address_id=address_book_id)",
		"OsCommerceCustomerInfo" => "<(customers_id=customers_info_id)",
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>