<?php

class ActiveRecord_OsCommerceProductToCategory extends Core_ActiveRecord
{
	public $tableName = "products_to_categories";
	public $primaryKey = "products_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceCategory" => "<(categories_id)",
		"OsCommerceProduct" => "<(products_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, 3306, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>