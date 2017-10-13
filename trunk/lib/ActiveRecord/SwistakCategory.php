<?php

class ActiveRecord_SwistakCategory extends Core_ActiveRecord
{
	public $tableName = "swistak_category";
	public $primaryKey = "sc_id";

	public $foreignTables = array(
		'SwistakParameterToCategory' => '<*(category_id)',
	);
}
