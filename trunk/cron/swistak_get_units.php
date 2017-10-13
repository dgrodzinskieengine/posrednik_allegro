<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

$client = new Core_SwistakApiSoapClient();

try
{
	$units = $client->get_unit();
// 		print_r($units);die;
	foreach($units as $unit)
	{
		if ($unit)
		{
			$unit_ = M('SwistakUnit')->findOrCreate("unit_id = '".$unit->{'id'}."'");
			$unit_[0]['unit_id'] = $unit->{'id'};
			$unit_[0]['unit_name'] = $unit->{'unit'};
			$unit_[0]['status'] += 2;
			$unit_[0]->save();
		}
	}
}
catch(SoapFault $soapFault)
{
// 			print_r($soapFault);
}

M('SwistakUnit')->db->execQuery("UPDATE swistak_unit SET status = 0 WHERE status = 1;");
M('SwistakUnit')->db->execQuery("UPDATE swistak_unit SET status = 1 WHERE status > 1;");