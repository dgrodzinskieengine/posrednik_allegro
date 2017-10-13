<?php

class ActiveRecord_SwistakParameterToCategory extends Core_ActiveRecord
{
	public $tableName = "swistak_parameter_to_category";
	public $primaryKey = "sptc_id";

	public $foreignTables = array(
		'SwistakParameter' => '<(parameter_id)',
		'SwistakCategory' => '<(category_id)',
	);
}
