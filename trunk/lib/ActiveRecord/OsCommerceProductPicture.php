<?php

class ActiveRecord_OsCommerceProductPicture extends Core_ActiveRecord
{
	public $tableName = "products_pictures";
	public $primaryKey = "products_pictures_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceProduct" => "<(products_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}

	function find($cond = "true", $options = array())
	{
		if (!isset($options['order']))
			$options['order'] = "products_pictures_order ASC";
		return parent::find($cond, $options);
	}

	function getNextOrder($products_id)
	{
		return 1 + (int)$this->db->getValue(sql(array(
				"SELECT MAX(products_pictures_order) FROM products_pictures WHERE products_id = %products_id;",
				"products_id" => $products_id
			)));
	}
}

?>