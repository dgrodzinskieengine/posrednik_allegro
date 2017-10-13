<?php

require_once(dirname(__FILE__) . "/../lib/lib.php");

// Odpalaj raz na miesiąc

// tutaj podać którego serwisu ma dotyczyć aktualizacja
$service_type = "allegro";	// allegro lub testwebapi
#$service_type = "testwebapi";

// podaj jakiś prawdziwy apiKey (niezbędny do pobrania automatycznie wersji API)
$config = array("apiKey" => "93485b00b9");

// które elementy mają być aktualizowane (jeżeli chcesz coś pominąć to zakomentuj linie)
$update_part = array(
# 		"kraje",
		"kategorie",
		"parametry",
		"opcjeParametrów",
		"formyDostawy"
	);


switch($service_type)
{
	case "testwebapi":
		$country_id = 228;
		break;
	case "allegro":
		$country_id = 1;
		break;
	case "aukro.cz":
		$country_id = 56;
		break;
}
consoleLog("START");
try
{
    consoleLog("START - kraje");
	$client = new Core_AllegroWebApiSoapClient();
	// pobieranie wersji WebAPI
	$version = $client->doQuerySysStatus(1, $country_id, $config['apiKey']);

	/// kraje
	if (in_array("formyDostawy", $update_part))
	{
		if ($country_id == 1)
		{
			$shipments = $client->doGetShipmentData($country_id, $config['apiKey']);
			foreach($shipments['shipment-data-list'] as $shipment)
			{
				$awaShipment = M('AllegroWebApiShipment')->findOrCreate("country_id = '{$country_id}' AND shipment_id = '".$shipment->{'shipment-id'}."'");
				$awaShipment[0]['country_id'] = $country_id;
				$awaShipment[0]['shipment_id'] = $shipment->{'shipment-id'};
				$awaShipment[0]['shipment_name'] = $shipment->{'shipment-name'};
				$awaShipment[0]['shipment_type'] = $shipment->{'shipment-type'};
				$awaShipment[0]->save();
			}
		}
	}

	/// kraje
	if (in_array("kraje", $update_part))
	{
		if ($country_id == 1)
		{
			$db->execQuery("UPDATE allegro_webapi_country SET status = 0;");
			$countries = $client->doGetCountries($country_id, $config['apiKey']);
			foreach($countries as $country)
			{
				$awaCountry = M('AllegroWebApiCountry')->findByCountryId($country->{'country-id'});
				if (!$awaCountry)
					$awaCountry = M('AllegroWebApiCountry')->create();
				else
					$awaCountry = $awaCountry[0];

				$awaCountry['country_id'] = $country->{'country-id'};
				$awaCountry['country_name'] = $country->{'country-name'};
				$awaCountry['status'] = 1;

				$awaCountry->save();
			}
			$db->execQuery("DELETE FROM allegro_webapi_country WHERE status = 0;");
		}
	}
    consoleLog("STOP - kraje");
	/// kategorie
    consoleLog("START - kategorie");
	if (in_array("kategorie", $update_part))
	{
		$db->execQuery("UPDATE allegro_webapi_category SET status = 0 WHERE country_id = {$country_id};");
		$categoryCounts = $client->doGetCatsDataCount($country_id, $version, $config['apiKey']);
		//print_r($categoryCounts);die;
		for($offset = 0; $offset < ceil($categoryCounts['cats-count'] / 50); $offset++)
		{
			$categories = $client->doGetCatsDataLimit($country_id, $version, $config['apiKey'], $offset, 50);
			foreach($categories['cats-list'] as $category)
			{
				#print_r($category);
				$awaCategory = M('AllegroWebApiCategory')->findByCountryIdAndCategoryId($country_id, $category->{'cat-id'});
				if (!$awaCategory)
					$awaCategory = M('AllegroWebApiCategory')->create();
				else
					$awaCategory = $awaCategory[0];

				$awaCategory['country_id'] = $country_id;
				$awaCategory['category_id'] = $category->{'cat-id'};
				$awaCategory['category_name'] = $category->{'cat-name'};
				$awaCategory['parent_id'] = $category->{'cat-parent'};
				$awaCategory['category_position'] = $category->{'cat-position'};
				$awaCategory['status'] = 1;

				$awaCategory->save();
			}
		}
		$db->execQuery("DELETE FROM allegro_webapi_category WHERE country_id = {$country_id} AND status = 0;");

		/// aktualizuje category_path
		$category_paths = array();
		$categories = M("AllegroWebApiCategory")->find(true, array('order' => 'parent_id, category_position'));
		foreach($categories as $category)
		{
			$category_id = $category['category_id'];
			$category_name = $category['category_name'];
			$parent_id = $category['parent_id'];
			if (isset($category_paths[$parent_id]))
				$cateogry_path = $category_paths[$parent_id]." > ". $category_name;
			else
				$cateogry_path = $category_name;
			$category['category_path'] = $category_paths[$category_id] = $cateogry_path;
			$category->save();
		}
	}
    consoleLog("STOP - kategorie");

	/// parametry formularza wystawiania aukcji
    consoleLog("START - parametry");
	if (in_array("parametry", $update_part))
	{
		$db->execQuery("UPDATE allegro_webapi_sellform SET status = 0 WHERE country_id = {$country_id};");
		$formFields = $client->doGetSellFormFieldsExt($country_id, $version, $config['apiKey']);
		foreach($formFields['sell-form-fields'] as $sellform)
		{
// 			if ($sellform->{'sell-form-cat'} == 629)
				#print_r($sellform);
// 			else
// 				continue;
// 			continue;

// 			if ($sellform->{'sell-form-id'} == 1842)
// 				print_r($sellform);

			$awaSellForm = M('AllegroWebApiSellForm')->findByCountryIdAndCategoryId($country_id, $sellform->{'sell-form-id'});
			if (!$awaSellForm)
			{
				$awaSellForm = M('AllegroWebApiSellForm')->create();
				$awaSellForm['update_timestamp'] = date('Y-m-d H:i:s');
			}
			else
			{
				$awaSellForm = $awaSellForm[0];
			}

// 			print_r($awaSellForm);

			$awaSellForm['country_id'] = $country_id;
			$awaSellForm['sellform_id'] = $sellform->{'sell-form-id'};
			$awaSellForm['sellform_title'] = $sellform->{'sell-form-title'};
			$awaSellForm['category_id'] = $sellform->{'sell-form-cat'};
			$awaSellForm['sellform_type'] = $sellform->{'sell-form-type'};
			$awaSellForm['sellform_res_type'] = $sellform->{'sell-form-res-type'};

			$awaSellForm['sellform_param_id'] = $sellform->{'sell-form-param-id'};
			$awaSellForm['sellform_parent_id'] = $sellform->{'sell-form-parent-id'};
			$awaSellForm['sellform_parent_value'] = $sellform->{'sell-form-parent-value'};

			switch($sellform->{'sell-form-type'})
			{
				case 1:
					$awaSellForm['form_field_type'] = 'input';
					break;
				case 2:
					if ($sellform->{'sell-form-id'} == 2)
						$awaSellForm['form_field_type'] = 'select/category';
					elseif ($sellform->{'sell-form-id'} == 9)
						$awaSellForm['form_field_type'] = 'select/option';
					else
						$awaSellForm['form_field_type'] = 'input';
					break;
				case 3:
					$awaSellForm['form_field_type'] = 'price';
					break;
				case 4:
					$awaSellForm['form_field_type'] = 'select/option';
					break;
				case 5:
					$awaSellForm['form_field_type'] = 'select/option'; // (na dobrą sprawę mogło by być radio)
					break;
				case 6:
					$awaSellForm['form_field_type'] = 'checkbox/option';
					break;
				case 7:
					$awaSellForm['form_field_type'] = 'image';
					break;
				case 8:
					$awaSellForm['form_field_type'] = 'text';
					break;
				case 9:
					$awaSellForm['form_field_type'] = 'datetime';
					break;
				case 13:
					$awaSellForm['form_field_type'] = 'date';
					break;
			}

			if ($sellform->{'sell-form-id'} == 24 || $sellform->{'sell-form-id'} == 27)
				$awaSellForm['form_field_type'] = 'text';

			$awaSellForm['sellform_position'] = $sellform->{'sell-form-pos'};


			if ($sellform->{'sell-form-length'} == 0 && $sellform->{'sell-form-res-type'} == 2)	/// integer
				$awaSellForm['sellform_length'] = 11;

			if ($sellform->{'sell-form-length'} > 0 || $sellform->{'sell-form-res-type'} != 2)
				$awaSellForm['sellform_length'] = $sellform->{'sell-form-length'};



			$awaSellForm['sellform_min_value'] = $sellform->{'sell-min-value'};
			$awaSellForm['sellform_max_value'] = $sellform->{'sell-max-value'};

			if ($awaSellForm['sellform_description'] != $sellform->{'sell-form-desc'})
				$awaSellForm['update_timestamp'] = date('Y-m-d H:i:s');
			if ($awaSellForm['sellform_opts_values'] != $sellform->{'sell-form-opts-values'})
				$awaSellForm['update_timestamp'] = date('Y-m-d H:i:s');

			$awaSellForm['sellform_description'] = $sellform->{'sell-form-desc'};
			$awaSellForm['sellform_opts_values'] = $sellform->{'sell-form-opts-values'};

			$options = array();
			if ($sellform->{'sell-form-opt'} & 1 == 1)
				$options[] = 'required';
			if ($sellform->{'sell-form-opt'} & 2 == 2)
				$options[] = 'hidden';
			if ($sellform->{'sell-form-opt'} & 4 == 4)
				$options[] = 'fixed';
			if ($sellform->{'sell-form-opt'} & 8 == 8)
				$options[] = 'optional';
			if ($sellform->{'sell-form-opt'} & 16 == 16)
				$options[] = 'opt_nomask_val';

			$awaSellForm['sellform_option'] = implode(", ", $options);

			$awaSellForm['sellform_field_desc'] = $sellform->{'sell-form-field-desc'};
			$awaSellForm['sellform_field_unit'] = $sellform->{'sell-form-unit'};
			$awaSellForm['sellform_field_options'] = $sellform->{'sell-form-options'};
			$awaSellForm['status'] = 1;

			$awaSellForm->save();

			unset($awaSellForm);
		}
		$db->execQuery("DELETE FROM allegro_webapi_sellform WHERE country_id = {$country_id} AND status = 0;");
	}
    consoleLog("STOP - parametry");
	/// opcje parametrówy formularza wystawiania aukcji
    consoleLog("START - opcje parametrów");
	if (in_array("opcjeParametrów", $update_part))
	{
		$awaSellForms = M('AllegroWebApiSellForm')->find("form_field_type LIKE '%/option'");
		foreach($awaSellForms as $awaSellForm)
		{
// 			print_r($awaSellForm->asArray());
			$trzebaDodac = true;
			$awaSellFormOptions = M('AllegroWebApiSellFormOption')->find("country_id = '{$awaSellForm['country_id']}' AND sellform_id = '{$awaSellForm['sellform_id']}'");
			if ($awaSellFormOptions)
			{
				$trzebaDodac = false;
				if (strtotime($awaSellFormOptions[0]['insert_timestamp']) < strtotime($awaSellForm['update_timestamp']))
				{
					/// usunąć bo stare
					M('AllegroWebApiSellFormOption')->db->execQuery("DELETE FROM allegro_webapi_sellform_option WHERE country_id = '{$awaSellForm['country_id']}' AND sellform_id = '{$awaSellForm['sellform_id']}';");
					$trzebaDodac = true;
				}
			}

			if ($trzebaDodac)
			{
				$values = explode("|", $awaSellForm['sellform_opts_values']);
				$descriptions = explode("|", $awaSellForm['sellform_description']);

				foreach($values as $k => $value)
				{
					$awaSellFormOption = M('AllegroWebApiSellFormOption')->create();
					$awaSellFormOption['country_id'] = $awaSellForm['country_id'];
					$awaSellFormOption['sellform_id'] = $awaSellForm['sellform_id'];
					$awaSellFormOption['option_position'] = $k+1;
					$awaSellFormOption['option_id'] = $values[$k];
					$awaSellFormOption['option_name'] = $descriptions[$k];
					$awaSellFormOption->save();
				}
			}
		}
	}
    consoleLog("STOP - opcje parametrów");
}
catch(SoapFault $soapFault)
{
    print_r($soapFault);
}
consoleLog("STOP");
