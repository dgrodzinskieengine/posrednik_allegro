<?php

class ActiveRecord_AllegroWebApiPostbuyformitem extends Core_ActiveRecord
{
	public $tableName = "allegro_postbuyformitem";
	public $primaryKey = "postbuyformit_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

}

