<?php

// co kilka minut

require_once(dirname(__FILE__)."/../lib/lib.php");

$db = db();

print "Aktualizacja ofert na stronie głównej.\n";
$shopOffers = M("ShopOffer")->find("true", array("order" => "shop_offer_use_counter ASC, RAND()", "limit" => 10));
if ($shopOffers)
{
	$db->execQuery("TRUNCATE TABLE shop_offer_mainpage;");

	foreach($shopOffers as $shopOffer)
	{
		$shop = $shopOffer->shop;

		$shopOfferMainpage = M('ShopOfferMainpage')->create();
		$shopOfferMainpage['name'] = $shopOffer['name'];
		$shopOfferMainpage['url'] = $shopOffer['url'];
		$shopOfferMainpage['photo'] = $shopOffer['photo'];
		$shopOfferMainpage['price'] = $shopOffer['price'];
		$shopOfferMainpage->save();

		$shopOffer['shop_offer_use_counter'] = $shopOffer['shop_offer_use_counter']+1;
		$shopOffer->save();
	}
}
