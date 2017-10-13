<html>
<header>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	{literal}
	<style>
		* { font-family: Arial, Helvetica, Tahoma; font-size: 12px; }
	</style>
	{/literal}
</header>
<body>
	<b>Witaj <a href="http://{$allegro_url}/show_user.php?uid={$user.user_id}" target="_blank">{$user.nick}</a> ({$user.rating}),</b>

	<br /><br />

	Dziękujemy za zainteresowanie ofertą sklepu <a href="{$shop_url}" target="_blank"><b>{$shop_name_full}</b></a>.

	<br /><br />

	Zarejestrowaliśmy Twoje następujące oferty złożone poprzez Allegro w naszych aukcjach:
	<ol>
		{foreach from=$bids item=bid}
			<li><a href="http://{$allegro_url}/show_item.php?item={$bid.auction_id}" target="_blank">{$bid.auction_name}</a> {$bid.quantity} x {$bid.price} {if $country_id == 1}zł{else}crd.{/if}</li>
		{/foreach}
	</ol>

	na łączną kwotę: <b>{$sum} {if $country_id == 1}zł{else}crd.{/if}</b>

	<br /><br />

	Jeżeli planujesz kupić coś jeszcze z naszej oferty to serdecznie zapraszamy. Kupując jednocześnie na kilku aukcjach oszczędzasz na kosztach przesyłki.

	<br /><br />

	Oferta naszego sklepu dostępna jest na Allegro pod nazwą <a href="http://{$allegro_url}/show_user_auctions.php?uid={$shop_user_id}"><b>{$allegro_login}</b></a> <b>({$shop_rating})</b>.

	<br /><br />

	<b>Skorzystaj z poniższego adresu celem uzupełnienia oraz weryfikacji swoich danych oraz wyboru formy transportu i płatności: <a href="http://{$form_hostnam}/allegro_{$user.user_hash}_{$shop_id}" target="_blank">http://core.{$form_hostnam}/allegro_{$user.user_hash}_{$shop_id}</a></b>

	<br /><br />

	Jeżeli planujesz kupić coś jeszcze z naszej oferty to serdecznie zapraszamy. Kupując jednocześnie na kilku aukcjach oszczędzasz na kosztach przesyłki.

	<br /><br />

	Z poważaniem,<br />
	{$shop_name_full}
</body>
</html>