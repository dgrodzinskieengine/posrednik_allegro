<?php

class ActiveRecord_AllegroWebApiShipment extends Core_ActiveRecord
{
	public $tableName = "allegro_shipment";
	public $primaryKey = "as_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function isPaczkomatyShipping($shipping_id)
	{
		$paczkomaty_array = array(
			'10022', '20022', '10023', '20023'
		);

		return in_array($shipping_id, $paczkomaty_array);
	}
}
