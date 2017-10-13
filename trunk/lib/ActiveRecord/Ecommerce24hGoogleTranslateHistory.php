<?php
	class ActiveRecord_Ecommerce24hGoogleTranslateHistory extends Core_ActiveRecord
	{
		public $tableName = "google_translate_history";
		public $primaryKey = "google_translate_history_id";

		// jeżeli puste to nie potrzebne
		public $foreignTables = array(
		);
		
		function __construct($data = array()) {
			parent::__construct($data);
			
			$this->db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, EC24H_DB_PREF);
		}
		
		function getCountSignsPerDay($day, $customers_shops_id)
		{
			$day = preg_replace("`[^0-9\-]*`", "", $day);
			$customers_shops_id = (int)$customers_shops_id;
			
			return (int)$this->db->getValue("SELECT SUM(google_translate_history_text_length) FROM google_translate_history WHERE google_translate_history_day = '{$day}' AND customers_shops_id = {$customers_shops_id}");
		}
	}
