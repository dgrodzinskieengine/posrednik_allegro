<div class="offers">
	<div class="title"><div class="text">OSTATNIO DODANE</div></div>
	<div class="content">

	{foreach from=$shopOffers item=shopOffer}
		<div class="position">
			<a href="{$shopOffer.url}" class="picture" style="background-image:url({$shopOffer.photo})">
				<img src="img/picture_border.gif">
			</a>
			<div class="description">
				<a href="{$shopOffer.url}" class="title">{$shopOffer.name|truncate:60:""|upper}</a>
				cena: {$shopOffer.price|string_format:"%.2f"|replace:'.':','} zł
			</div>
		</div>
	{/foreach}

	</div>
</div>
<div class="contentBottom"></div>