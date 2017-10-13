<html>
<header>
	{box from=$boxes uniqueId='header'}
	{literal}
	<style>
		body {
			background: #eee;
		}
		input { border: 1px solid gray; }
		.pageWrapper {
			width: 930px;
			margin: auto;
			border: 1px solid gray;
			background: white;
			padding: 30px;
		}
	</style>
	{/literal}
</heade>
<body>
<div class="pageWrapper">
	<h3>Witaj <a href="http://{$allegro_url}/show_user.php?uid={$user.user_id}" target="_blank">{$user.nick}</a> ({$user.rating}),</h3>

	<form class="form" action="/allegro_{$user_hash}_{$shop_id}/ajax" method="post">

	<table width="100%">
	<tr valign="top">
		<td>

			Dziękujemy za zainteresowanie ofertą sklepu <a href="{$shop_url}" target="_blank" class="urlDoSklepu"><b>{$shop_name_full}</b></a>.

			<br /><br />

			{if $bids}

				Zarejestrowaliśmy Twoje następujące oferty złożone poprzez Allegro w naszych aukcjach:
				<ol>
					{foreach from=$bids item=bid}
						<li><a href="http://{$allegro_url}/show_item.php?item={$bid.auction_id}" target="_blank">{$bid.auction_name}</a> {$bid.quantity} x {$bid.price} {if $country_id == 1}zł{else}crd.{/if}<input type="hidden" name="abid[]" value="{$bid.abid}" /></li>
					{/foreach}
				</ol>

				na łączną kwotę: <b>{$sum} {if $country_id == 1}zł{else}crd.{/if}</b>

				<br /><br />

				Jeżeli planujesz kupić coś jeszcze z naszej oferty to zrób to przed zatwierdzeniem niniejszego formularza. Pomożesz nam tym samym szybciej skompletować całe Twoje zamówienie.
			{else}
				<b>Wszystkie Twoje dotychczasowe zamówienia złożone przez Allegro zostały przekazane do naszego sklepu internetowego.</b>
			{/if}

			<br /><br />

			Oferta naszego sklepu dostępna jest na Allegro pod nazwą <a href="http://{$allegro_url}/show_user_auctions.php?uid={$shop_user_id}"><b>{$allegro_login}</b></a> <b>({$shop_rating})</b>.

			{if $bids}

				<br /><br />

				Jeżeli chcesz przekazać swoje zamówienie do realizacji zweryfikuj dane pobrane z Allegro, wybierz formę transportu oraz płatności <i>(wg cennika zamieszczone w aukcji)</i> i zatwierdź formularz.

				<br /><br />

				Wybieram następującą formę transportu:
				<select style="width: 300px;" name="transport">
					<option value=''>-- wybierz --</option>
					{foreach from=$transport item=tr}
						<option value="{$tr.name} [{$tr.id}]">{$tr.name}</option>
					{/foreach}
				</select>

				<br /><br />

				Wybieram następującą formę płatności:
				<select style="width: 308px;" name="payment">
					<option value=''>-- wybierz --</option>
					{foreach from=$payment item=pay}
						<option value="{$pay.name} [{$pay.id}]">{$pay.name}</option>
					{/foreach}
				</select>

				<br /><br />

				<b>Twój komentarz do zamówienia</b><br />
				<textarea name="comment" style="width: 530px; height: 100px; border: 1px solid gray;"></textarea>
			{/if}

		</td>
		<td width="350px" style="padding-left: 20px;">

			<b>Dane Osobowe</b>
			<div style="border: 1px solid gray; background-color: #eee; width: 300px;">
			<table>
				<tr>
					<td width="100">Imię:</td>
					<td><input type="text" name="entry_firstname" value='{$user.first_name}' {if !$bids}disabled{/if} /> *</td>
				</tr>
				<tr>
					<td>Nazwisko:</td>
					<td><input type="text" name="entry_lastname" value='{$user.last_name}' {if !$bids}disabled{/if} /> *</td>
				</tr>
				<tr>
					<td>Adres e-mail:</td>
					<td><input type="text" name="customers_email_address" value='{$user.email}' {if !$bids}disabled{/if} /> *</td>
				</tr>
			</table>
			</div>

			<br />

			<b>Dane Firmy</b>
			<div style="border: 1px solid gray; background-color: #eee; width: 300px;">
			<table>
				<tr>
					<td width="100">Nazwa Firmy:</td>
					<td><input type="text" name="entry_company" value='{$user.company}' {if !$bids}disabled{/if} /></td>
				</tr>
				<tr>
					<td>NIP:</td>
					<td><input type="text" name="entry_nip" value='' {if !$bids}disabled{/if} /></td>
				</tr>
			</table>
			</div>

			<br />

			<b>Dane teleadresowe</b>
			<div style="border: 1px solid gray; background-color: #eee; width: 300px;">
			<table>
				<tr>
					<td width="100">Ulica:</td>
					<td><input type="text" name="entry_street_address" value='{$user.street}' {if !$bids}disabled{/if} /> *</td>
				</tr>
				<tr>
					<td>Kod Pocztowy:</td>
					<td><input type="text" name="entry_postcode" value='{$user.postcode}' {if !$bids}disabled{/if} /> *</td>
				</tr>
				<tr>
					<td>Miasto:</td>
					<td><input type="text" name="entry_city" value='{$user.city}' {if !$bids}disabled{/if} /> *</td>
				</tr>
				<tr>
					<td>Kraj:</td>
					<td><input type="text" name="entry_country" value='{if $user.country_id == 1}Polska{else}Neverland (webapi){/if}' {if !$bids}disabled{/if} /> *</td>
				</tr>
			</table>
			</div>

			<br />

			<b>Dane Kontaktowe</b>
			<div style="border: 1px solid gray; background-color: #eee; width: 300px;">
			<table>
				<tr>
					<td width="100">Nr Telefonu:</td>
					<td><input type="text" name="customers_telephone" value='{$user.phone}' {if !$bids}disabled{/if} /> *</td>
				</tr>
				<tr>
					<td>Nr Telefonu 2:</td>
					<td><input type="text" name="customers_fax" value='{$user.phone2}' {if !$bids}disabled{/if} /></td>
				</tr>
			</table>
			</div>

			<br />
			<table><tr><td width="100"></td><td><input class="submit" type="button" value="zatwierdź" {if !$bids}disabled{/if} /></td></tr></table>
	</td>
</tr>
</table>

	</form>

<iframe src="{$shop_url}/allegro.php?key=1" style="border:0px; width:1px; height:1px;"></iframe>
</div>
</body>
</html>