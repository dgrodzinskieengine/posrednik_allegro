$(document).ready(function() {
	$('.form').ajaxForm(function(msg) {
			if (msg.substring(0,5) == "ERROR")
			{
				alert(msg.substring(7,10000));
			}
			else
			{
				if (msg == "OK")
				{
					alert('Twoje zamówienie zostąło przesłane do sklepu.');
					document.location.reload();
				}
			}

		});
	$('.submit').bind("click", function() {
			$('.form').trigger('submit');
		});
});