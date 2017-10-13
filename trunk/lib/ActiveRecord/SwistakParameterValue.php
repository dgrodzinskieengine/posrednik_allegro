<?php

class ActiveRecord_SwistakParameterValue extends Core_ActiveRecord
{
	public $tableName = "swistak_parameter_value";
	public $primaryKey = "spv_id";

	public $foreignTables = array(
	);

	function find($cond = "true", $options = array())
	{
		if (!isset($options['order']))
			$options['order'] = "`order` ASC";
		return parent::find($cond, $options);
	}
}
