<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

$client = new Core_SwistakApiSoapClient();

try
{
	$deliveries = $client->get_delivery_info();
// 		print_r($deliveries);die;
	foreach($deliveries as $delivery)
	{
		if ($delivery)
		{
			$unit_ = M('SwistakDelivery')->findOrCreate("delivery_id = '".$delivery->{'id'}."'");
			$unit_[0]['delivery_id'] = $delivery->{'id'};
			$unit_[0]['delivery_name'] = $delivery->{'delivery_info'};
			$unit_[0]['status'] += 2;
			$unit_[0]->save();
		}
	}
}
catch(SoapFault $soapFault)
{
// 			print_r($soapFault);
}

M('SwistakDelivery')->db->execQuery("UPDATE swistak_delivery SET status = 0 WHERE status = 1;");
M('SwistakDelivery')->db->execQuery("UPDATE swistak_delivery SET status = 1 WHERE status > 1;");