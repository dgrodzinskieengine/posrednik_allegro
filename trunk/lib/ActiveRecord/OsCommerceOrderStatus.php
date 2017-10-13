<?php

class ActiveRecord_OsCommerceOrderStatus extends Core_ActiveRecord
{
	public $tableName = "orders_status";
	public $primaryKey = "orders_status_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>