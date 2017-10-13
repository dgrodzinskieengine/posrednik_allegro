<?php

/**
 * Skrypt do zainkludowaniu na początku każdego skryptu odpalanego z konsoli
 *
 * Przed zainkludowaniem tego skryptu wskazane jest skonfigurowanie tablicy $validation, np.:
 *
 * $validation = array(
 *				"--shop_id" =>	array(
 *								"required" => true,
 *								"type" => "integer",
 *								"returnAs" => "shopId",
 *								"description" => "shop_id sklepu, którego chcemy klasyfikować"
 *							),
 *				"--sip" =>	array(
 *								"required" => false,
 *								"type" => "integer",
 *								"returnAs" => "sip",
 *								"description" => "ID rekordu z shop_import_process..."
 *							),
 *				"--nameClassifyOnly" =>	array(
 *								"required" => false,
 *								"type" => "boolean",
 *								"returnAs" => "nameClassifyOnly",
 *								"description" => "Jeżeli ustawiono jakąś wartość ..."
 *							)
 *			);
 *
 * gdzie:
 *	- required - określa czy pole jest obligatoryjne do wywołania skryptu
 *	- type - typ danych na jaki rzutowana ma być wartość
 *	- returnAs - nazwa zmiennej pod jaką dodatkowo ma być widoczna w skrypcie wartość (można nie podawać)
 *	- description - opis, który może się przydać później
 *
 * Na wyjściu skrypt daje tablicę $params oraz zmienne o nazwach podanych w polu returnAs
 */

// nowy sposób wyciągania parametrów
$params = array();
foreach($argv as $arg)
{
	$param = "";
	$value = "";

	$elem = explode("=", $arg);
	$param = trim($elem[0]);
	$value = trim($elem[1]);

	if ($param == "--help" || $param == "/?" || $param == "/h")
		showDocumentationAndDie();

	if ($param != "")
	{
		if (isset($validation) && isset($validation[$param]) && $validation[$param]["type"])
		{
			switch ($validation[$param]["type"]) {
				case "integer":
						if ($value != (int)$value)
							showDocumentationAndDie();
						else
							$value = (int)$value;
					break;
				case "boolean":
				case "bool":
						$value = true;
					break;
				case "string":
							$value = (string)$value;
					break;
			}
		}
		$params[$param] = $value;
	}
}
if (isset($validation) && is_array($validation))
{
	foreach($validation as $param => $valids)
	{
		if (isset($valids["required"]) && $valids["required"] && !isset($params[$param]))
			showDocumentationAndDie();

		if (isset($params[$param]))
		{
			if (isset($valids["returnAs"]))
				$nazwaZmiennej = $valids["returnAs"];
			else
				$nazwaZmiennej = trim($param, "-");

			$$nazwaZmiennej = $params[$param];
		}
	}
}

function showDocumentationAndDie()
{
	global $validation;
	$maxLength = 0;

	$validation['--help'] = array(
					"required" => false,
					"type" => "boolean",
					"description" => "Wyświetla ten ekran pomocy."
				);

	foreach($validation as $key => $value)
		if (strlen($key) > $maxLength)
			$maxLength = strlen($key);

	print "\nSkrypt przyjmuje nasztepujące parametry wywołania:\n\n";
	foreach($validation as $key => $value)
	{
		print "  ".str_pad($key, $maxLength+2);
		print "Typ parametru: {$value['type']}. ";
		if ($value['required']) print "Parametr wymagany. ";
		print $value['description'];
		print "\n";
	}
	//print_r($validation);
	die("\n");
}
