<?php

// Kilka razy na dobę

require_once(dirname(__FILE__)."/../lib/lib.php");

$temp_dir = KATALOG."/core/temp/cron";

$db = db();

$shops = M('Shop')->find();
foreach ($shops as $shop)
{
	$shop_id = $shop['shop_id'];
	$shop_name = $shop['shop_name'];
	$shop_url = $shop['shop_url'];
	$shop_xml = $shop_url."/wch.php";
	$xml_file = $temp_dir.'/wch-xml-'.$shop_id.'.xml';
	$http_login = "template";
	$http_password = "template";

	print "Pobieranie XMLa: {$shop_xml}\n";
	$cmd = 'echo -n > '.$xml_file.'; wget --no-check-certificate -t1 --http-user='.$http_login.' --http-password='.$http_password.' --output-document="'.$xml_file.'" '.$shop_xml.' --user-agent="User-Agent: WCH crowler" --timeout=180 2> /dev/null';
	exec($cmd);

	$db->execQuery("UPDATE shop_offer SET status = 0 WHERE shop_id = $shop_id;");

	print "Parsowanie pliku: {$xml_file}\n";
	$parser = new parser($xml_file);
	while($value = $parser->getValue())
	{
		$shopOffers = M("ShopOffer")->findByUrl($value['url']);
		if ($shopOffers)
		{
			$shopOffer = $shopOffers[0];
			$shopOffer['name'] = $value['name'];
			$shopOffer['price'] = $value['price'];
			$shopOffer['photo'] = $value['photo'];
			$shopOffer['status'] = 1;
			$shopOffer->save();
		}
		else
		{
			$shopOffer = M("ShopOffer")->create();
			$shopOffer['shop_id'] = $shop_id;
			$shopOffer['url'] = $value['url'];
			$shopOffer['name'] = $value['name'];
			$shopOffer['price'] = $value['price'];
			$shopOffer['photo'] = $value['photo'];
			$shopOffer['status'] = 1;
			$shopOffer->save();
		}
	}

	$db->execQuery("DELETE FROM shop_offer WHERE shop_id = $shop_id AND status = 0;");
}



class Parser
{
	private $encoding;
	private $tablica;
	private $xmlFileContent;
	private $maxKes;
	private $zakladkaKes;
	private $kesNo = 0;
	private $noMore = false;
	private $fileName = '';

	public function Parser($fileName)
	{
		$this->fileName = $fileName;
		$this->encoding == "";
		$this->maxKes = 8*1024*1024;	// 16MB
		$this->zakladkaKes = 512*1025;	// 0,5MB (?)
		$this->tablica = array();
		$this->offers = array();
	}

	private function parse()
	{
		$this->getXmlFile();
		$this->clearXmlFile();

		$parse_function = "parse_wch";
		$this->$parse_function();
	}

	public function getValue()
	{
		while($this->noMore == false && count($this->tablica) == 0) $this->parse();
		return array_shift($this->tablica);
	}

	private function getXmlFile()
	{
		$nazwa_pliku = $this->fileName;
		//$uchwyt = fopen($nazwa_pliku, "r");
		//$xml = fread($uchwyt, filesize($nazwa_pliku));
		$start = $this->kesNo*$this->maxKes-($this->kesNo==0?0:$this->zakladkaKes);
		$size = $this->maxKes+2*$this->zakladkaKes;
		unset($this->xmlFileContent);
		$this->xmlFileContent = file_get_contents($nazwa_pliku, FILE_TEXT, null, $start, $size);
		$this->kesNo++;
		if (strlen($this->xmlFileContent) < $size) $this->noMore = true;

		//return $xml;
	}

	private function clearXmlFile()
	{
		if ($this->encoding == "")
		{
			$encodings = array();
			preg_match_all("`encoding=\"(.*)\"\\s*\\?>`U", $this->xmlFileContent, $encodings, PREG_SET_ORDER);
			$encoding = strtoupper(trim(trim($encodings[0][1]), '"'));
		}
		else
			$encoding = $this->encoding;

		if ($encoding != "UTF-8")
			die("Skrypt obsługuje tylko kodowanie UTF-8, a wykryto {$encoding}!");

		$this->xmlFileContent = mb_ereg_replace("[^0-9a-zA-ZęóąśłżźćńĘÓĄŚŁŻŹĆŃ<>\/\x20:&;\.\,\-\_\?\=\+\(\)\[\]\{\}\*\"\!\%\#\']", " ", $this->xmlFileContent);

		///  \xC8\xE8\xCC\xEC\xDD\xFD\xC1\xE1\xAE\xBE\xD9\xF9\xCD\xED\xA9\xB9

		$this->xmlFileContent = mb_ereg_replace("([\x20]+)", " ", $this->xmlFileContent);
		$this->xmlFileContent = mb_ereg_replace('(&amp;)', "&", $this->xmlFileContent, "i");
		$this->xmlFileContent = mb_ereg_replace('(&lt;)', "<", $this->xmlFileContent, "i");
		$this->xmlFileContent = mb_ereg_replace('(&gt;)', ">", $this->xmlFileContent, "i");
		$this->xmlFileContent = mb_ereg_replace('(&nbsp;)', " ", $this->xmlFileContent, "i");
		$this->xmlFileContent = mb_ereg_replace('(&quot;)', '"', $this->xmlFileContent, "i");
		$this->xmlFileContent = mb_ereg_replace("[']+", "''", $this->xmlFileContent, "i");
		$this->xmlFileContent = mb_ereg_replace('(&oacute;)', 'ó', $this->xmlFileContent, "i");
	}

	private function valide_offer($k)
	{
		if (!(strlen($this->tablica[$k]['id']) > 0))	{ unset($this->tablica[$k]); return null; }
		if (!(strlen($this->tablica[$k]['name']) > 0))	{ unset($this->tablica[$k]); return null; }
		if (!(strlen($this->tablica[$k]['url']) > 0))	{ unset($this->tablica[$k]); return null; }
		if (!($this->tablica[$k]['price'] > 0))			{ unset($this->tablica[$k]); return null; }
		if ($this->tablica[$k]['price'] >= 100000000)	{ unset($this->tablica[$k]); return null; }
	}

	private function parse_wch()
	{
		$xml = $this->xmlFileContent;

		$offers = array();
		preg_match_all("|<product>(.*)<\/product>|iU", $xml, $offers, PREG_SET_ORDER);
		$this->tablica = array();
		$k = 0;

		foreach($offers as $offerXml)
		{
			$this->tablica[$k] = array();

			$out = array();
			preg_match_all("|<id>(.*)</id>|iU", $offerXml[1], $out, PREG_SET_ORDER);
			$this->tablica[$k]['id'] = trim(strip_tags(preg_replace('/<!\[CDATA\[(.*)\]\]>/i', '$1', $out[0][1])));

			$out = array();
			preg_match_all("|<name>(.*)</name>|iU", $offerXml[1], $out, PREG_SET_ORDER);
			$this->tablica[$k]['name'] = trim(strip_tags(preg_replace('/<!\[CDATA\[(.*)\]\]>/i', '$1', $out[0][1])));

			$out = array();
			preg_match_all("|<price>(.*)</price>|iU", $offerXml[1], $out, PREG_SET_ORDER);
			$this->tablica[$k]['price'] = strtr(trim(strip_tags(preg_replace('/<!\[CDATA\[(.*)\]\]>/i', '$1', $out[0][1]))),array(","=>".", " "=>""));

			$out = array();
			preg_match_all("|<url>(.*)</url>|iU", $offerXml[1], $out, PREG_SET_ORDER);
			$this->tablica[$k]['url'] = trim(strip_tags(preg_replace('/<!\[CDATA\[(.*)\]\]>/i', '$1', $out[0][1])));

			$out = array();
			preg_match_all("|<photo>(.*)</photo>|iU", $offerXml[1], $out, PREG_SET_ORDER);
			$this->tablica[$k]['photo'] = trim(strip_tags(preg_replace('/<!\[CDATA\[(.*)\]\]>/i', '$1', $out[0][1])));

			$this->valide_offer($k);

			$k++;
		}
	}
}
