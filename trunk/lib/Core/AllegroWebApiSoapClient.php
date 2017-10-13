<?php

class Core_AllegroWebApiSoapClient extends SoapClient
{
	public function __construct($debug=false, $old=true)
	{
		if ($debug)
		{
			parent::__construct('https://webapi.allegro.pl.webapisandbox.pl/uploader.php?wsdl');
		}
		else
		{
			if($old)
			{
				parent::__construct('https://webapi.allegro.pl/uploader.php?wsdl');
			}
			else
			{
				parent::__construct('https://webapi.allegro.pl/service.php?wsdl',array('features'=>SOAP_SINGLE_ELEMENT_ARRAYS));
			}
		}
		
// 		$this->soap_defencoding = 'UTF-8';
// 		$this->decode_utf8 = false;
	}

	public function call($funct, $args)
	{
		return $this->__soapCall($funct, $args);
	}

	/**
	 * Redukuje obraz do wielkości nadającej się do przesyłu.
	 *
	 * @param string $url URL obrazka (lokalne, albo sieciowe).
	 * @return string Binarna zawartość obrazka w formacie JPEG.
	 */
	public static function resize($url)
	{
		if (trim($url) == "")
			return "";

		$hash = md5(rand());

		$temp_file = '/tmp/AllegroWebApiSoapClient_'.$hash.'.jpg';
		$temp_file2 = '/tmp/AllegroWebApiSoapClient_'.$hash.'_2.jpg';

		$powtorzen = 3;

		$image = curl_get_file_contents($url);

		$imagesize = false;
		while(($image === false || strlen($image) < 100) && $powtorzen > 0)
		{
			$image = curl_get_file_contents($url);

			$powtorzen--;
			sleep(1);
		}

		file_put_contents($temp_file2, $image);
		system("convert {$temp_file2} -colorspace sRGB JPG:{$temp_file}");
		$imagesize = @getimagesize($temp_file2);

		if ($image === false || $imagesize === false)
			return false;

		if (strlen( base64_encode($image) ) > 50000)
		{
			while( ($size = strlen(base64_encode($image))) > 50000)
			{
				$mnoznik = 0.9;

				if ($size > 1000000)
					$mnoznik = 0.5;

				$imagesize = getimagesize($temp_file2);

				$width = $imagesize[0];
				$height = $imagesize[1];

				$x = ceil($mnoznik * $width);
				$y = ceil($mnoznik * $height);

				$cmd = "convert -strip {$temp_file} -resize {$x}x{$y} -colorspace sRGB -quality 85 JPG:{$temp_file2}";
				system($cmd);

				$image = file_get_contents($temp_file2);
			}
		}

 		unlink($temp_file);
 		unlink($temp_file2);

		return $image;
	}
}

function curl_get_file_contents($URL)
{
	$URL = strtr($URL, array(" " => "%20"));

	$c = curl_init();
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_URL, $URL);
	$contents = curl_exec($c);
	curl_close($c);

	if ($contents)
		return $contents;
	else
		return FALSE;
}
