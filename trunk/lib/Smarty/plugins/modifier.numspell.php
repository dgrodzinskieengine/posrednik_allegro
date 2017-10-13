<?php
function smarty_modifier_numspell ($text, $num0 = '', $num1 = '', $num2 = '')
{
	$rtext = (int)$text;
	$text = abs($text);
	$tmp0 = $text % 10;
	$tmp1 = $text % 100;
	if ($text == 1)
		return $rtext.' '.$num1;
	elseif ($text == 0 || ($tmp0 !== $text && $tmp0 >=0 && $tmp0 <= 1) || ($tmp0 >= 5 && $tmp0 <= 9) || ($tmp1 > 10 && $tmp1 < 20))
		return $rtext.' '.$num0;
	elseif (($tmp1 < 10 || $tmp1 > 20) && $tmp0 >= 2 && $tmp0 <= 4)
		return $rtext.' '.$num2;
}
?>