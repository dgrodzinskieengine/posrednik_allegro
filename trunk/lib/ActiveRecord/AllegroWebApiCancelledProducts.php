<?php

class ActiveRecord_AllegroWebApiCancelledProducts extends Core_ActiveRecord
{
	public $tableName = "allegro_cancelled_products";
	public $primaryKey = "acp_id";

public function getForCron()
{
	$return = $this->findBySql("SELECT *, acp.user_id buyer_id FROM allegro_cancelled_products acp INNER JOIN allegro_shop_auction asa ON(acp.allegro_id = asa.auction_id) WHERE acp.sent = 0 AND deal_id != 0 AND asa.date_stop > NOW() - INTERVAL 45 DAY");
	return $return;
}

}
?>