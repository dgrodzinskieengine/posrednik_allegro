<?php
/**
 * @file /shared/class/Core/Frontend.php
 * @brief zawiera definicję klasy Snip_Core_Frontend
 * @author Marek Maksimczyk (mandos22@poczta.fm)
 * @author $LastChangedBy: scotty $
 * @date creation date 2007-08-24 10:51:06
 * @date $LastChangedDate: 2008-12-02 13:44:27 +0100 (wto, 02.12.2008) $
 * @version $Rev: 5585 $
 */

require_once(DIR_SHARED.'libs/mysmarty.php');

 /**
  * @class Snip_Core_Frontend
  * @brief klasa uruchamiajaca program
  */
class Snip_Core_Frontend 
{

 	private static $thisInstance;		///< przechowuje referencję do siebie samego
	public $smarty; 								///< zmienna klasy template
	public $theme;                 ///< referencja do obiektu Theme
	public $request;               ///< referencja do obiektu FrontRequest
 	
  /*
   * @brief konstruktor prywatny klasy Snip_Core_Frontend
   */
  private function __construct () {
	  	if(!(is_numeric(DBG_TYPE) && is_numeric(DBG_TYPE_INLINE) && (DBG_TYPE & DBG_TYPE_INLINE))) {
  			set_error_handler(array($this, 'error_handler'));
	  	}
		setlocale(LC_ALL, 'pl', 'pl_PL', 'pl_PL.UTF-8', 'plk', 'polish', 'Polish');
		ob_start();
		
				
		//początek debugera
		Snip_Core_Debugger::startBlock(DBG_BLK_MAIN);
		
		$db = Snip_Core_Database::getInstance();
		$db->setDebugError(DB_ERROR);
		
		// inicjacja i ustawienia smarty
 		$this->smarty = new mysmarty(DIR_BASE.'themes/');
		$this->smarty->template_dir = 'themes';
		$this->smarty->cache_dir = DIR_CACHE . 'smarty_cache';
		$this->smarty->compile_dir = DIR_CACHE . 'smarty_compile';
		$this->smarty->use_sub_dirs = SMARTY_SUBDIRS;
		
		$this->smarty->caching = SMARTY_CACHE;

		$request = Snip_Core_FrontRequest::getInstance();
		
		$this->smarty->assign('request', $request);
		Snip_Core_Debugger::debug(DBG_TYPE_IN, $request, "informacje z FrontRequesta");
		
  }

  /**
   * @brief destruktor klasy Snip_Core_Frontend 
   */
  public function __destruct()
  {
  	//wywołanie funkcji wyświetlającej na ekranie wyniki debugowania
		//Snip_Core_Debugger::endBlock(DBG_BLK_MAIN);
		Snip_Core_Debugger::printDebug();
  }
  
  /*
   * @brief zwraca referencję do objektu Snip_Core_Frontend, jeżeli go nie ma to go tworzy
   * @return object self::$thisInstance
   */
  public static function getInstance() 
  {
  	if (self::$thisInstance == null) {
  		self::$thisInstance = new Snip_Core_Frontend();
  	}
  	return self::$thisInstance;
  }
	
	/**
	 * @brief funkcja do obługi błędów 
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param $errsrc
	 * @todo sprawdzić czy jest używana, bo chyba podobna jest w index.php
	 */
	public static function error_handler ($errno, $errstr, $errfile, $errline, $errsrc = null)
	{
		$error = $errfile . ' linia:' . $errline;
		switch ($errno) {
		case E_NOTICE:
			Snip_Core_Debugger::debug(DBG_TYPE_NOTICE, $error, 'notice ' . $errstr);
			break;
    case E_WARNING:
			Snip_Core_Debugger::debug(DBG_TYPE_WARNING, $error, 'warning ' . $errstr);
 			break;
 		case E_USER_NOTICE:
			Snip_Core_Debugger::debug(DBG_TYPE_NOTICE, $error, 'notice ' . $errstr);
			break;
    case E_USER_WARNING:
			Snip_Core_Debugger::debug(DBG_TYPE_WARNING, $error, 'warning ' . $errstr);
 			break;
 		case E_USER_ERROR:
			Snip_Core_Debugger::debug(DBG_TYPE_ERROR, $error, 'error ' . $errstr);
 			break;
 		case E_RECOVERABLE_ERROR:
			Snip_Core_Debugger::debug(DBG_TYPE_ERROR, $error, 'error ' . $errstr);
 			break;
 		case E_ALL:
			Snip_Core_Debugger::debug(DBG_TYPE_ERROR, $error, 'error ' . $errstr);
 			break;
    }
   /* Don't execute PHP internal error handler */
    return false;
	}
	
	/**
	 * @brief wywołanie odpowiedniego theme na postawie danych z Snip_Core_FrontRequest
	 */
	public function run ($requestData)
	{
		$this->request = Snip_Core_FrontRequest::getInstance();
		$url = new Snip_Core_Url();
		$url->setBasicUrl($this->request->baseUrl);
		// $requestData z Link::parseUrl()
		if($requestData) {
			
			list($theme, $view, $params) = $requestData;

			if (!isset($params['product_id']) || (int)$params["product_id"] <= 0) $params["product_id"] = 0;

			$className = 'Theme_' . $theme;

			$this->theme = new $className($params, $this->smarty, $url, $requestData);
			$this->theme->doCommand();
			$this->theme->proceed($view, $params);
		} else {
			if ( !empty($this->request->theme) && $this->request->theme != false){
				
				if (file_exists(DIR_BASE.'themes/' . $this->request->theme . '.theme.php')){
					$className = 'Theme_' . $this->request->theme;
					$this->theme = new $className($this->request->getParams(), $this->smarty, $url);
					$this->theme->doCommand();
					$this->theme->proceed();
				} else {
					Snip_Core_Debugger::debug(DBG_TYPE_CRITICAL, $className . ' nie został załadowany', 'Brak Theme');
				}
			} else {
				// w przypadku kiedy nie ma zdefiniowanego requestu wyświetla stronę główną
				$this->request->theme = 'Main';
				$this->request->view = 'defaultView';
				if (file_exists(DIR_BASE.'themes/'.$this->request->theme.'.theme.php')){
					$className = 'Theme_' . $this->request->theme;
					$this->theme = new $className($this->request->getParams(), $this->smarty, $url);
					$this->theme->proceed();
				} else
					trigger_error('ERROR! Brak theme: "'.$this->request->theme.'" lub nie został załadowany.', E_USER_ERROR);
			}
		}
	}
}
  ?>
