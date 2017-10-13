<?php

class ActiveRecord_SwistakParameter extends Core_ActiveRecord
{
	public $tableName = "swistak_parameter";
	public $primaryKey = "sp_id";

	public $foreignTables = array(
		"SwistakParameterValue" => "<*(parameter_id)"
	);

	function find($cond = "true", $options = array())
	{
		if (!isset($options['order']))
			$options['order'] = "parameter_id ASC";
		return parent::find($cond, $options);
	}
}
