<?php

set_include_path(get_include_path() . PATH_SEPARATOR . DIR . "/phpExcel");

class Core_Excel
{
	private $PHPExcel = null;

	public function __construct($template = '', $filename = "")
	{
		require_once ('PHPExcel.php');
		require_once ('IOFactory.php');
		
		if ($template != "")
		{
			if ($filename != "")
				$ext = strrchr($filename, ".");
			else
				$ext = strrchr($template, ".");
				
			if ($ext == ".xlsx")
				$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			else
				$objReader = PHPExcel_IOFactory::createReader('Excel5');

			// czy sama nazwa pliku (ścieżka względna), czy też ścieżka bezwzględna
			if (substr($template, 0, 1) != "/")
				$this->PHPExcel = $objReader->load(DIR . "/phpExcel/templates/" . $template);
			else
				$this->PHPExcel = $objReader->load($template);
		}
		else
		{
			$this->PHPExcel = new PHPExcel();
		}
	}

	public function fillByArray($array)
	{
		foreach($array as $w => $wiersz)
			foreach($wiersz as $k => $value)
			{
				$this->fillCell($k.$w, $value);
			}
	}

	public function fillCell($cell, $value)
	{
		$this->PHPExcel->getActiveSheet()->setCellValue($cell, $value);
		if (substr($value, 0, 4) == "http")
			$this->PHPExcel->getActiveSheet()->getCell($cell)->getHyperlink()->setUrl($value);
	}

	public function getCell($cell)
	{
		$value = $this->PHPExcel->getActiveSheet()->getCell($cell)->getCalculatedValue();
		if (is_object($value))
			return $value->getPlainText();
		else
			return trim($value);
	}

	public function getContent()
	{
		$temp_file = "/tmp/".md5(uniqid()).".xls";
		$this->saveToFile($temp_file);

		$handle = fopen($temp_file, "r");
		$content = fread($handle, filesize($temp_file));
		fclose($handle);

		unlink($temp_file);

		return $content;
	}

	public function saveToFile($file_name)
	{
		$objWriter = PHPExcel_IOFactory::createWriter($this->PHPExcel, 'Excel5');
		$objWriter->save($file_name);
	}
}
