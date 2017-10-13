<?php

class Core_Template
{
	protected $smarty;
	protected $boxes = array();		///< lista boxów dla danego Theme'a/Box'a
#	static $css;			///< lista plików css dla danego templejta
#	static $js;			///< lista plików javascrip dla danego templejta

	public function __construct()
	{
		if (!isset($this->css) || !is_array($this->css)) $this->css = array();
		if (!isset($this->js) || !is_array($this->js)) $this->js = array();
	}

	public static function run(&$smarty, $type, $name, $actionName, $args)
	{
		$className = $type."_".$name;		

		$template = new $className();
		$template->smarty = $smarty;
		$template->smarty->assign($template->$actionName($args));
		$template->smarty->assign('boxes', $template->boxes);
		$tplName = $type."/".$name."/".$name.".".$actionName.".tpl";

		if ($type == "Theme")
		{
			$template->smarty->display($tplName);
		}
		else
		{
			return $template->smarty->fetch($tplName);
		}
	}

	protected function addBox($uniqueId, $boxName, $actionName, $args)
	{
		$this->boxes[$uniqueId] = Core_Template::run($this->smarty, 'Box', $boxName, $actionName, $args);
	}

	protected function addCss(&$args, $cssFilename)
	{
		if (!isset($args['css']) || !is_array($args['css'])) $args['css'] = array();
		array_push($args['css'], "css/" . $cssFilename . '.css');
	}

	public function addJs(&$args, $jsFilename)
	{
		if (!isset($args['js']) || !is_array($args['js'])) $args['js'] = array();
		array_push($args['js'], "js/" . $jsFilename . '.js');
	}

}


?>
