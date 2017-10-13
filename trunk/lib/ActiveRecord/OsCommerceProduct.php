<?php

class ActiveRecord_OsCommerceProduct extends Core_ActiveRecord
{
	public $tableName = "products";
	public $primaryKey = "products_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceProductToCategory" => "<*(products_id)",
		"OsCommerceProductPicture" => "<*(products_id)",
		"OsCommerceProductFile" => "<*(products_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>