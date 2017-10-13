<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

$validation = array(
	"--day" => array(
		"required" => false,
		"type" => "string",
		"description" => "Dzień (format YYYY-MM-DD) dla którego mają być przegenerowane kwoty"
	),

	"--month" => array(
		"required" => false,
		"type" => "string",
		"description" => "Miesiąc (format YYYY-MM) dla którego mają być przegenerowane kwoty"
	),

	"--lastDays" => array(
		"required" => false,
		"type" => "integer",
		"description" => "Ilość dni wstecz (z wyłączeniem dnia dzisiejszego) dla których mają być przegenerowane kwoty"
	),
);

include dirname(__FILE__) . "/base_cli.php";

if(!$month && !$day && !$lastDays) {
	consoleLog('ERROR: Pass at least one of parameters');
}

if(strlen($month) && strlen($day)) {
	consoleLog('ERROR: Day and month cannot be passed together');
}

if($day && !preg_match('/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/', $day)) {
	consoleLog("ERROR: Wrong date format (should be YYYY-MM-DD)");
	exit();
}

if($month && !preg_match('/[0-9]{4}\-[0-9]{2}\/', $month)) {
	consoleLog("ERROR: Wrong date format (should be YYYY-MM)");
	exit();
}

$dateCondition = '';

if($day) {
	$dateCondition = "DATE_FORMAT(ssq.insert_timestamp, '%Y-%m-%d') = '{$day}'";
}

if($month) {
	$dateCondition = "DATE_FORMAT(ssq.insert_timestamp, '%Y-%m') = '{$month}'";
}

if($lastDays) {
	$dateCondition = "
		DATE_FORMAT(ssq.insert_timestamp, '%Y-%m-%d') >= DATE_FORMAT(NOW() - INTERVAL {$lastDays} DAY, '%Y-%m-%d')
		AND DATE_FORMAT(ssq.insert_timestamp, '%Y-%m-%d') < DATE_FORMAT(NOW(), '%Y-%m-%d')
	";
}

$customers_account_operation_description = 'Wysyłka sms przez smsApi.pl';

$sql = "
	SELECT
  		cs.customers_id,
		cs.customers_shops_id,
		DATE_FORMAT(ssq.insert_timestamp, '%Y-%m-%d') sending_day,
		DATE_FORMAT(ssq.insert_timestamp, '%Y-%m') sending_month,
		cs.customers_shops_name,
		s.shop_eco_sms,
		ca.customers_account_operation_amount,
		count(1) * s.shop_eco_sms * -1 as newDefaultAmount,
		count(1) as sentQty
	FROM
		`ok6`.`smsapi_queue` ssq
	INNER JOIN
		`ok6`.`shop` s 
		ON ssq.sms_shop_id = s.shop_id
	INNER JOIN
		`ok6_ecommerce24h`.`customers_shops` cs 
		ON cs.customers_shops_name LIKE s.shop_name
	LEFT JOIN
		`ok6_ecommerce24h`.`customers_account` ca 
		ON cs.customers_shops_id = ca.customers_shops_id 
			AND ca.customers_account_operation_description = '{$customers_account_operation_description}'
			AND ca.customers_account_day = DATE_FORMAT(ssq.insert_timestamp, '%Y-%m-%d')
	WHERE
		{$dateCondition}
	GROUP BY sending_day, ssq.sms_shop_id
	ORDER BY sending_day, ssq.sms_shop_id
";

foreach(M('Ecommerce24hCustomerAccount')->db->getAssoc($sql) as $row) {
	mysql_select_db(EC24H_DB_PREF);

	$current_account = M('Ecommerce24hCustomerAccount')->findOrCreate("
		customers_shops_id = {$row['customers_shops_id']}
		AND customers_account_day = '{$row['sending_day']}'
		AND customers_account_operation_description = '{$customers_account_operation_description}'
	");

	$current_account = $current_account[0];
	$current_account['customers_id'] = $row['customers_id'];
	$current_account['customers_shops_id'] = $row['customers_shops_id'];
	$current_account['customers_account_day'] = $row['sending_day'];
	$current_account['customers_account_month'] = $row['sending_month'];
	$current_account['customers_account_operation_amount'] = $row['newDefaultAmount'];
	$current_account['customers_account_operation_description'] = $customers_account_operation_description;
	$current_account->save();
}

//
//mysql_select_db(DB_PREF);
//mysql_select_db(EC24H_DB_PREF);
