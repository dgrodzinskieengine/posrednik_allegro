<?php

class ActiveRecord_Ecommerce24hCustomerAccount extends Core_ActiveRecord
{
	public $tableName = "customers_account";
	public $primaryKey = "customers_account_id";
	private $default_db = NULL;

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);
	
	function __construct($data = array()) {
		parent::__construct($data);
		//$this->default_db = $this->db;
		$this->db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, EC24H_DB_PREF);
	}
	
	public function currentAccount($customers_shops_id)
	{
		$customers_shops_id = (int)$customers_shops_id;
		if($customers_shops_id)
		{
			$return = $this->db->getValue("SELECT SUM(customers_account_operation_amount) FROM customers_account WHERE customers_shops_id = {$customers_shops_id}");
			//$this->db = $this->default_db;
			return $return;
		}
		else
		{
			//$this->db = $this->default_db;
			return false;
		}
	}
}