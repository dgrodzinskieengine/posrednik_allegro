<?php

class ActiveRecord_OsCommerceProductDescription extends Core_ActiveRecord
{
	public $tableName = "products_description";
	public $primaryKey = "products_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceProduct" => "<(products_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}

	public function findByProductIdAndLanguage($products_id, $language_id)
	{
		return $this->find(sql(array("
				products_id = %products_id
				AND language_id = %language_id
			",
			"products_id" => $products_id,
			"language_id" => $language_id
			)));
	}
}

?>