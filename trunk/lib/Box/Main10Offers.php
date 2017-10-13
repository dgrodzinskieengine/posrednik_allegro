<?php

class Box_Main10Offers extends Core_Template
{
	public function defaultView($args)
	{
		$args['shopOffers'] = M('ShopOfferMainpage')->find('true', array('limit' => 4));
// 		foreach($args['shopOffers'] as $k => $v)
// 			$args['shopOffers'][$k]['name'] = $v['name']." ".$v['name']." ".$v['name']." ".$v['name']." ".$v['name']." ".$v['name']." ".$v['name'];
		return $args;
	}
}

?>