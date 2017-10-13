<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

$client = new Core_SwistakApiSoapClient();

try
{
	$provinces = $client->get_province();
// 		print_r($provinces);die;
	foreach($provinces as $province)
	{
		if ($province)
		{
			$unit_ = M('SwistakProvince')->findOrCreate("province_id = '".$province->{'id'}."'");
			$unit_[0]['province_id'] = $province->{'id'};
			$unit_[0]['province_name'] = $province->{'province'};
			$unit_[0]['status'] += 2;
			$unit_[0]->save();
		}
	}
}
catch(SoapFault $soapFault)
{
// 			print_r($soapFault);
}

M('SwistakProvince')->db->execQuery("UPDATE swistak_province SET status = 0 WHERE status = 1;");
M('SwistakProvince')->db->execQuery("UPDATE swistak_province SET status = 1 WHERE status > 1;");