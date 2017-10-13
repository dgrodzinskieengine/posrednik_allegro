<?php
	class ActiveRecord_Ecommerce24hCustomerShop extends Core_ActiveRecord
	{
		public $tableName = "customers_shops";
		public $primaryKey = "customers_shops_id";

		// jeżeli puste to nie potrzebne
		public $foreignTables = array(
		);

		function __construct($data = array()) {
			parent::__construct($data);

			$this->db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, EC24H_DB_PREF);
		}

		public function getInfo($shop_name)
		{
			$shop_name = trim($shop_name);
// 			$shop_name = "'kwiate";
			if($shop_name)
			{
				$shop = $this->find(sql("customers_shops_name = %(shop_name)", array('shop_name' => $shop_name)));
				if($shop)
					return $shop[0];
			}
			else
				return false;
		}
	}