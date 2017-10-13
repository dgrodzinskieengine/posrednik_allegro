<?php
/**
 * Smarty plugin
 * @package Smarty
 * @author Marek Maksimczyk
 */

/**
 * @brief na podstawie parametrów zwraca ścieżkę do zdjęcia
 * @param array [in] $params tablica asocjacyjna z parametrami
 * id - id produktu
 * topcategory_id - id topowej kategorii produktu
 * thumb - w jakim podkatalogu jest zdjęcie (np th100)
 * @param [in/out] $smarty referencja do obiektu smarty(?)
 * @return string $content zwraca scieżkę do zdjecia
 */
function smarty_function_get_picture_path($params, &$smarty)
{
	if (empty($params['id'])) 
		return "r/i/photo/brak_zdjecia.gif";

	/*
	if (empty($params['topcategory_id'])) {
	$smarty->_trigger_fatal_error("[plugin] parameter 'topcategory_id' cannot be empty");
	return;
	}
	*/
	
	$subdirectory = floor($params['id']/100);

	if ($params['topcategory_id'] == 933)
		$content = "http://img1.okazje.info.pl/{$params['topcategory_id']}/$subdirectory/";
	else
		$content = IMG_URL."/{$params['topcategory_id']}/$subdirectory/";

	if ($params['thumb'])
		$content .= "{$params['thumb']}/";
		
	$content .= "{$params['id']}.jpg";
	
	if (!empty($params['assign']))
		$smarty->assign($params['assign'],$content);
	else
		return $content;
}

/* vim: set expandtab: */

?>