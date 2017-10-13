<?php

class ActiveRecord_OsCommerceOrderStatusHistory extends Core_ActiveRecord
{
	public $tableName = "orders_status_history";
	public $primaryKey = "orders_status_history_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
		"OsCommerceOrder" => "<(orders_id)"
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, DB_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}
}

?>