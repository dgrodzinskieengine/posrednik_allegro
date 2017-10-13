<?php

class ActiveRecord_OsCommerceCategory extends Core_ActiveRecord
{
	public $tableName = "categories";
	public $primaryKey = "categories_id";		// nie potrzebne

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	function __construct($data = array()) {
		parent::__construct($data);
		$this->db = Core_Database::getInstance('mysql', DB_SERVER, 3306, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
	}

	function getCategoryPath($imploder = " / ", $languages_id = 1, $categories_id = null)
	{
		if ($categories_id == null)
			$categories_id = $this['categories_id'];

		$category_path_array = array();

		$categories_id_temp = $categories_id;
		while(true)
		{
			$category_info = $this->db->getRow("
				SELECT
					cd.categories_name,
					c.parent_id
				FROM
					categories_description cd,
					categories c
				WHERE
					c.categories_id = cd.categories_id
					AND c.categories_id = $categories_id_temp
					AND cd.language_id = $languages_id;
				");

			if ($category_info)
			{
				if ($categories_id_temp == $category_info['parent_id']) break;
				$category_path_array[] = $category_info['categories_name'];
				$categories_id_temp = $category_info['parent_id'];
			}
			else break;
		}

		$this['category_path'] = implode($imploder, array_reverse($category_path_array));

		return $this['category_path'];
	}

}

?>