<?php

class ActiveRecord_OsCommerceAllegroConfiguration extends Core_ActiveRecord
{
	public $tableName = "allegro_configuration";
	//public $primaryKey = "shop_offer_id";		// nie potrzebne

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, 3306, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}

	public function findByKey($key)
	{
		return $this->findByAllegroConfigurationKey($key);
	}

	public function findByGroup($group)
	{
		return $this->findByAllegroConfigurationGroup($group);
	}

	public function configurationSave($group, $key, $value)
	{
		$configurations = $this->findByKey($key);

		if ($configurations)
			$configuration = $configurations[0];
		else
			$configuration = $this->create();

		$configuration['allegro_configuration_group'] = $group;
		$configuration['allegro_configuration_key'] = $key;
		$configuration['allegro_configuration_value'] = $value;
		$configuration['update_timestamp'] = date('Y-m-d H:i:s');

		$configuration->save();
	}

	public function configurationGet($group, $key)
	{
		$configurations = $this->find(sql(array("
				allegro_configuration_group = %allegro_configuration_group
				AND allegro_configuration_key = %allegro_configuration_key
				",
				"allegro_configuration_group" => $group,
				"allegro_configuration_key" => $key
			)));
		if ($configurations)
			return $configurations[0];
		else
			return array();
	}

	public function configurationExists($group, $key)
	{
		if ($this->configurationGet($group, $key))
			return true;
		else
			return false;
	}
}

?>