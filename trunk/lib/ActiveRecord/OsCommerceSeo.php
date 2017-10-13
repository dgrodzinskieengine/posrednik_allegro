<?php

class ActiveRecord_OsCommerceSeo extends Core_ActiveRecord
{
	public $tableName = "seo";
	public $primaryKey = "seo_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceProduct" => "<(products_id)",
		"OsCommerceCategory" => "<(categories_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>