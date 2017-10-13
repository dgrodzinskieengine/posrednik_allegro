<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

$client = new Core_SwistakApiSoapClient();

$swistakCategories = M('SwistakCategory')->find('status = 1', array('order' => 'category_id'));
foreach($swistakCategories as $swistakCategory)
{
	try
	{
		$params = $client->get_parameters($swistakCategory['category_id']);
// 		print_r($params);
		foreach($params as $param)
		{
			if ($param)
			{
				$parameter = M('SwistakParameter')->findOrCreate("parameter_id = '".$param->{'parameter_id'}."'");
				$parameter[0]['parameter_id'] = $param->{'parameter_id'};
// 				$parameter[0]['category_id'] = $swistakCategory['category_id'];
				$parameter[0]['parameter_name'] = $param->{'name'};
				$parameter[0]['parameter_type'] = $param->{'type'};
				$parameter[0]['parameter_unit'] = $param->{'unit'};
				$parameter[0]['status'] += 2;
				$parameter[0]->save();

				$parameterToCategory = M('SwistakParameterToCategory')->findOrCreate("parameter_id = '".$param->{'parameter_id'}."' AND category_id = '".$swistakCategory['category_id']."'");
				$parameterToCategory[0]['parameter_id'] = $param->{'parameter_id'};
				$parameterToCategory[0]['category_id'] = $swistakCategory['category_id'];
				$parameterToCategory[0]['status'] += 2;
				$parameterToCategory[0]->save();

				if ($param->{'values'} && $param->{'parameter_id'} > 0)
				{
					M('SwistakParameterValue')->db->execQuery("DELETE FROM swistak_parameter_value WHERE parameter_id = '".$param->{'parameter_id'}."';");

					foreach($param->{'values'} as $order => $value)
					{
						$parameterValue = M('SwistakParameterValue')->create();
						$parameterValue['parameter_id'] = $param->{'parameter_id'};
						$parameterValue['order'] = $order;
						$parameterValue['label'] = $value->{'label'};
						$parameterValue['value'] = $param->{'type'} == "enum" ? $value->{'value'} / 2 : $value->{'value'};	/// nie pytaj czemu podają o 2x za duże wartości przy checkboxach....
						$parameterValue->save();
					}
				}
			}
		}
	}
	catch(SoapFault $soapFault)
	{
// 			print_r($soapFault);
	}
}

M('SwistakParameter')->db->execQuery("UPDATE swistak_parameter SET status = 0 WHERE status = 1;");
M('SwistakParameter')->db->execQuery("UPDATE swistak_parameter SET status = 1 WHERE status > 1;");

M('SwistakParameterToCategory')->db->execQuery("UPDATE swistak_parameter_to_category SET status = 0 WHERE status = 1;");
M('SwistakParameterToCategory')->db->execQuery("UPDATE swistak_parameter_to_category SET status = 1 WHERE status > 1;");
