<?php

require_once(dirname(__FILE__)."/../lib/lib.php");


$category_url = 'http://www.swistak.pl/download/kategorie.xml';

$temp_folder = dirname(__FILE__) ."/temp/";

consoleLog("Category GET: $category_url");
system('wget --quiet -t10 -T0 --output-document="'.$temp_folder."swistak_category.xml" . '" "' . $category_url . '" -e robots=off -U "Mozilla/5.0 (compatible; Konqueror/3.2; Linux)"');
$size = @filesize($temp_folder."swistak_category.xml");

$categories = array();

if ($size > 1000000)
{
	$xml = file_get_contents($temp_folder."swistak_category.xml");

	preg_match_all('`<kategoria><id><!\[CDATA\[(?P<id>[0-9]+)\]\]></id><name><!\[CDATA\[(?P<name>.+)\]\]></name><parent><!\[CDATA\[(?P<parent>[0-9]+)\]\]></parent><poziom><!\[CDATA\[(?P<poziom>[0-9]+)\]\]></poziom><ids><!\[CDATA\[(?P<ids>[0-9,]+)\]\]></ids></kategoria>`iU', $xml, $dopasowania, PREG_SET_ORDER);

	foreach($dopasowania as $dopasowanie)
	{
		$categories[$dopasowanie['id']] = $dopasowanie['name'];

		$category = M('SwistakCategory')->findOrCreate("category_id = '{$dopasowanie['id']}'");
		$category[0]['category_id'] = $dopasowanie['id'];
		$category[0]['category_name'] = $dopasowanie['name'];
		$category[0]['parent_id'] = $dopasowanie['parent'];
		$category[0]['status'] += 2;

		$category_path = array();
		foreach(explode(",", $dopasowanie['ids']) as $id)
			$category_path[] = $categories[$id];

		$category[0]['category_path'] = implode(" > ", $category_path);
		$category[0]['parent_id'] = $dopasowanie['parent'];
		$category[0]->save();
// 		print_r($dopasowanie);die;
	}

	M('SwistakCategory')->db->execQuery("UPDATE swistak_category SET status = 0 WHERE status = 1;");
	M('SwistakCategory')->db->execQuery("UPDATE swistak_category SET status = 1 WHERE status > 1;");
}