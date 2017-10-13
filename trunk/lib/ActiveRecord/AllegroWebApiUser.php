<?php

class ActiveRecord_AllegroWebApiUser extends Core_ActiveRecord
{
	public $tableName = "allegro_user";
	public $primaryKey = "au_id";

	// jeżeli puste to nie potrzebne
	public $foreignTables = array(
	);

	public function save($options=array())
	{
		if($this->__newRecord==true)
		{
			// consoleLog('Nowy uzytkownik: '.(int)$this['user_id']);
			$postbuyFormData=M('AllegroWebApiPostbuyformdata')->find('postbuyform_get_by_shop = 6 and postbuyform_buyer_id = '.(int)$this['user_id']);
			if($postbuyFormData)
			{
				foreach($postbuyFormData as $item)
				{
					// consoleLog('Znaleziono dla niego auckje: '.$item['postbuyform_id']);
					$item['postbuyform_get_by_shop']=0;
					$item->update();
				}
			}
		}
		return parent::save($options);
	}
}

?>