<?php
/**
 * Klasa służąca do generowania ładnych linków i do parsowania ładnych linków do postaci użytecznej
 */
class Link {
	/**
	 * Klucz pierwszego elementu każdej tablicy to cel linka. Wartość to odddzielone przecinkiem nazwy metod przetwarzających paramtry. Metody przyjmują dwa parametry. Pierwszy to $params (tablica w której należy podać paramtry do przetworzenia). Drugi to $dir (można podać Link::toUrl, Link::fromUrl) określa kierunek przetwarzania parametrów)
	 * Wzorce urli są definiowane przez wyrażenia regularne więc obowiązuje składnia opisana tutaj http://pl.php.net/manual/pl/regexp.reference.php
	 * Znaki . \ + * ? [ ^ ] $ ( ) { } = ! < > | : mają w regexp-ach specjalne znaczenie więc jeżeli mają siępojawiać w url-ach to trzeba je w wzorcach wpisać ujęte w nawiasy kwadratowe, czyli [.] zamiast . (i wyjątkowo [\\]] zamiast ] zresztą i tak nie można używać w urlu ] bo ten znak nie jest obsługiwany w generatorze linków)
	 * W przypaku konieczności użycia we wzorcu jakichś egzotycznych możliwości regex-pów należy sprawdzić czy poprawnie generują się linki na podstawie tego wzorca (jeżlei nie to należy twórczo rozwinąć metodę Link::get())
	 * Nazwa celu linka mieć postać NazwaTheme_NazwaView_wariant (część _wariant jest opcjonalna).
	 * Jeżeli wzorce linków mają zawierać jakieś parametry to powinny być one zapisane jako (?P<nazwa_parametu>wzorzec)
	 */
	static public $urlPatterns = array(

// --- POCZĄTEK DEFIENICJI WZORCÓW URL-i ---



// MAIN
			array('Main_defaultView' => '',
				'/'
 			),
 			array('Webapi_defaultView' => '',
				'/webapi'
 			),
 			array('AllegroForm_ajaxView' => '',
				'/allegro_(?P<user_hash>[a-z0-9]{32})_(?P<shop_id>[0-9]+)/ajax'
 			),
			array('AllegroRotator_picture' => '',
				'/allegro/picture/(?P<shop_name>[a-z0-9\-]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)',
				'/allegro/picture/(?P<shop_name>[a-z0-9\-]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)/(?P<random>[a-z0-9]+)',
				'/allegro/picture/(?P<shop_name>[a-z0-9\-]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)\-(?P<type>[a-z]{4})',
				'/allegro/picture/(?P<shop_name>[a-z0-9\-]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)\-(?P<type>[a-z]{4})/(?P<random>[a-z0-9]+)',

				'/allegro/picture/(?P<login_allegro>us_all_[a-z0-9\-\_]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)',
				'/allegro/picture/(?P<login_allegro>us_all_[a-z0-9\-\_]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)/(?P<random>[a-z0-9]+)',
				'/allegro/picture/(?P<login_allegro>us_all_[a-z0-9\-\_]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)\-(?P<type>[a-z]{4})',
				'/allegro/picture/(?P<login_allegro>us_all_[a-z0-9\-\_]+)\-(?P<auction_type>[a-z]+)\-(?P<counter>[0-9]+)\-(?P<type>[a-z]{4})/(?P<random>[a-z0-9]+)',
			),
			array('AllegroRotator_redirect' => '',
				'/allegro/redirect/(?P<shop_name>[a-z0-9\-]+)\-(?P<action_type>[a-z]+)\-(?P<counter>[0-9]+)',

				'/allegro/redirect/(?P<login_allegro>us_all_[a-z0-9\-\_]+)\-(?P<action_type>[a-z]+)\-(?P<counter>[0-9]+)',
			),
	);

// --- POCZĄTEK METOD PRZETWARZAJĄCYCH PARAMETRY URL-i ---
/*
// funkcje przetwarzające powinny być pisane wg. poniższego schematu

	static function translateFieldName(&$params, $dir, $name = 'field_name') {
		if(isset($params[$name])) {
			if($dir == self::fromUrl) {
				// code for translating $params[$name] from nice form to useful form
			}
			if($dir == self::toUrl) {
				// code for translating $params[$name] from useful form to nice form
			}
		}
	}
*/

	static function translateShopId(&$params, $dir, $name = 'shop_id') {
		if(isset($params[$name])) {
			if($dir == self::fromUrl) {
				$shops = M('Shop')->findByShopName($params[$name]);
				if ($shops)
					$params[$name] = $shops[0]['shop_id'];
				else
					self::redirect("/", 404);
			}
			if($dir == self::toUrl) {
				// code for translating $params[$name] from useful form to nice form
			}
		}
	}




	public function getRidOfSpacesAndNonAlphanumeric($str) {
		return trim(preg_replace('`[^a-z0-9]+`s', '-', strtolower(strtr($str, array(
			'Ę' => 'e',
			'Ą' => 'a',
			'Ó' => 'o',
			'Ś' => 's',
			'Ł' => 'l',
			'Ż' => 'z',
			'Ź' => 'z',
			'Ć' => 'c',
			'Ń' => 'n',
			'ę' => 'e',
			'ą' => 'a',
			'ó' => 'o',
			'ś' => 's',
			'ł' => 'l',
			'ż' => 'z',
			'ź' => 'z',
			'ć' => 'c',
			'ń' => 'n'
		)))), '-');


	}

// --- KONIEC METOD PRZETWARZAJĄCYCH PARAMETRY URL-i ---

	/**
	 * Zwraca adres url do przekazania do szablonu, prowadzący do zadanego celu
	 *
	 * @param string $target	cel linka, zazwyczaj w postaci NazwaTheme_nazwaView, odpowiada nazwie metody klasy Link w której definiowane są wzorce linków dla danego theme i view
	 * @param array $params 	tablica asocjacyjna wartości parametrów do umieszczenia w linku,
	 * 							klucze tablicy muszą odpowiadać nazwom użytym w zdefiniowanych wzorcach linków w metodzie Link::$target()
	 * 							tablica musi zawierać wszystkie klucze odpowiadjące nazwom parametrów w interesującym nas wzorcu (służą do jego wybrania spośród pozostałych)
	 * @param int $patternNo	jeżeli kilka wzorców w metodzie target ma takie same parametry jak klucze tablicy $params, to $patternNo wskazuje, który z pasujących wzorców zostanie użyty
	 * @param int $extendedParams	dodatkowe parametry, które nie są wymagane przez schemat linka ale mogą być potrzebne metodom translate do prawidłowego działania
	 * @return string gotowy ładny adres url
	 */
	function get($target, $params = array(), $patternNo = 0, $extendedParams = array()) {
/*	if($extendedParams['qqqq']) $qqqq=1;
	if($qqqq) l($params);*/
//	l($params);
		if(is_array($target) && isset($target[0])) {
			$params = $target;
			unset($params[0]);
			$target = $target[0];
		}
		$extendedParams = $params + $extendedParams;
		// --- przypadki szczególne
		if($target == 'Shop_categoryView') {
			if(in_array($params['category_id'], $GLOBALS['topCategoryIds'])) {
				$params['topcategory_id'] = $params['category_id'];
				unset($params['category_id']);
			}

		}
// 	if($qqqq) l($params);

		// jeżeli podano parametr filters, to przed jego użyciem usuwane są z niego pola podane bezpośrednio
		// jeżeli na skutek tego usunięcia parametr filters staje się pusty to nie bieże udziału w dalszym przetwarzaniu
//		$mark = is_numeric($params['city_id']);
//		if($mark) l($params);
		$paramToFilter = array(
			'producer_id' => 1,
			'shop_id' => 237,
			'city_id' => 238
		);
		if(isset($params['filters']) && is_array($params['filters'])) {
			$params['filters'] = array_diff_key($params['filters'], $params);
			foreach($paramToFilter as $paramKey => $filterKey) {
				if($params[$paramKey]) {
					unset($params['filters'][$filterKey]);
					unset($extendedParams['filters'][$filterKey]);
				}
			}
			$params['filters'] = array_filter($params['filters']);
			if(!$params['filters']) {
				unset($params['filters']);
			}
		}

		// jeżeli parametr page jest nienumeryczny lub równy zero lub 1 to usuń go
		if(!$params['page'] || !is_numeric($params['page']) || $params['page'] == 1) {
				unset($params['page']);
		}
		// jeżeli parametr sort nie ma wartości to usuń go
		if(!$params['sort']) {
				unset($params['sort']);
		}

		if(isset($params['filters'])) {
			if(is_array($params['filters'])) {
				$params['filters'] = array_filter($params['filters']);
			}
			if(!$params['filters']) unset($params['filters']);
		}
		// ---

		$paramSig = array_keys($params);
		sort($paramSig);

		if(!isset(self::$regexps[$target][serialize($paramSig)][$patternNo])) {
			throw new Exception("Pattern for $target with params (".implode(", ", array_keys($params)).") ".($patternNo?"number $patternNo ":"")."could not be found");
		}
		$linkSchema = self::$regexps[$target][serialize($paramSig)][$patternNo];
		$regexpMd5 = md5($linkSchema);

		$params = array_plus_recursive($params, $extendedParams);

		$templateParams = array();
		foreach($params as $name => $value) {
			if($value === '#{}') {
				$templateParams[$name] = '#{'.$name.'}';
			}
		}

		$params = array_diff_key($params, $templateParams);
		// wywołanie kodu przekształcającego parametry z uzytecznych w php do ładnych w url-u
		foreach(self::$methods[$target] as $m) {
			if(strpos($m, ':')!==false) {
				list($methodName, $methodParam) = explode(':', $m);
				Link::$methodName($params, self::toUrl, $methodParam);
			} else {
				Link::$m($params, self::toUrl);
			}
		}
		$params = $templateParams + $params;

		// wstawia wartości parametrów w miejsca ciągów (?P<nazwa>wzorzec)
		foreach(self::$parameters[$regexpMd5] as $name => $pos) {
			$linkSchema = substr_replace($linkSchema, $params[$name], $pos[0], $pos[1]);
		}

		// usuwa ciągi typu [znaki]* (opcja|inna)* z* [znaki]*? (opcja|inna)*? z*?
		do {
			$oldLinkSchema = $linkSchema;
			$linkSchema = preg_replace('`
				(?:
						\\((?!\\?P)[^()]*\\)
					|
						\\[[^\\]+]*\\]
					|
						[^[]
				)(?:\\*\\??|\\?)
			`isx', '', $linkSchema);
		} while($oldLinkSchema != $linkSchema);

		// usuwa ciągi typu (wzorzec)?
		$linkSchema = preg_replace('`\\([^()]*\\)\\?`is', '', $linkSchema);
		// usuwa ciągi typu z?
		$linkSchema = preg_replace('`[^)*+([\\]]\\?`is', '', $linkSchema);
		// zamienia ciągi typu [znaki] na z
		$linkSchema = preg_replace('`\\[([^\\]]?)[^\\]]*\\]`is', '\1', $linkSchema);
		// zamienia ciągi typu (abba|baba) na abba
		$linkSchema = preg_replace('`\\(([^|()]+)?[^)]*\\)`is', '\1', $linkSchema);
		// usuwa ciągi ()
		$link = str_replace('()', '', $linkSchema);

		return $link;
	}


	/**
	 * Metoda wydobywa z ładnego $url-a informacje o tym do jakiego Theme oraz View prowadzi link, i jakie parametry należy przekazać.
	 * Działa dopasowując $url do wzorców linków zdefiniowanych w metodach Link::$target(&$params,$dir)
	 *
	 * @param string $url ładny (przyjazny dla seo) link do zanalizowania
	 * @return array tablica trzyelementowa, pierwszy element zawiera nazwę Theme, drugi nazwę View, trzeci parametry do przekazania
	 */
	static function parseUrl($url) {
		// pobierz nazwy wszystkich możliwych targetów
		$targets = array_keys(self::$methods);

		// zgrupuj schematy linków po 40 na raz (żeby ograniczyć ilośc wywołań preg_match)
		self::$routeRegexp = array_chunk(self::$routeRegexp, 1, true);

		// dopasuj wyrażenie regularne
		foreach(self::$routeRegexp as $RR) {
			if(preg_match('`(?J)'.implode('|',$RR).'`is', $url, $params)) {
				// ustal która sekcja wyrażenia została dopasowana
				$regexpMd5 = array_intersect_key(array_filter($params), self::$parameters);
				$regexpMd5 = key($regexpMd5);
				$target = self::$targets[$regexpMd5];
				self::$currentTarget = $target;

				// usuń dopasowania o nazwach takich samych jak możliwe targety
				$params = array_intersect_key($params, self::$parameters[$regexpMd5]);

				list($theme, $view) = explode('_', $target);
				if(self::$methods[$target]) {
					foreach(self::$methods[$target] as $m) {
						if(strpos($m, ':')!==false) {
							list($methodName, $methodParam) = explode(':', $m);
							Link::$methodName($params, self::fromUrl, $methodParam);
						} else {
							$params = $params+array('target'=>$target);
							Link::$m($params, self::fromUrl);
						}
					}
				}
				foreach($_GET as $k => $v) {
					if(is_array($v)) {
						$params[$k] = array_merge($v, $params[$k]);
					} else {
						$params[$k] = $v;
					}
				}
				self::$currentParams = $params;
				self::$parsedWithLink = true;
				return array($theme, $view, $params);
			} else {
				self::$currentTarget = '';
				// no match (404)
			}
		}
	}

	/**
	 * Zwraca target bieżącej strony
	 */
	static function getCurrentTarget() {
		if(!isset(self::$currentTarget)) {
			return FALSE;
		}
		if(self::$currentTarget == '') {
			$info = Snip_Core_FrontRequest::getInstance()->info;
			return $info['theme'].'_'.$info['view'];
		}
		return self::$currentTarget;
	}

	/**
	 * stałe określające zachowanie metody url, oraz wybierające kod do wykonania w metodach Link::$target
	 * toUrl - jeżeli $dir jest równy tej stałej to powinien w metodzie Link::$target wykonać się kod konwertujący parametry z użytecznych (np. id kategorii) na "ładne" (np. nazwy kategorii)
	 * fromUrl - jeżeli $dir jest równy tej stałej to powinien w metodzie Link::$target wykonać się kod konwertujący parametry z "ładnych" (np. nazwy kategorii) na użyteczne (np. id kategorii)
	 * config - jeżeli $dir jest równy tej stałej to w metodzie Link::$target nie powinien się wykonywać żaden kod
	 */
	const toUrl = 1;
	const fromUrl = 2;
	const config = 3;

	public static $routeRegexp = array();
	public static $regexps = array(); // target, parameters => regexp
	public static $targets = array(); // md5(regexp) => target
	public static $parameters = array(); // md5(regexp) => array(parameterName => array('startPosition', 'length'))
	public static $methods = array(); // target => lista metod przetwarzających parametry oddzielona przecinkami
	public static $currentTarget;
	public static $currentParams;
	public static $parsedWithLink;

	/**
	 * @param string $target Cel linka zazwyczaj w postaci ThemeName_viewName
	 * @param string $dir	jeżeli ma wartość Link::config to metoda zapamiętuje wzorce linków przekazane jako pozostałe parametry jako skojarzone z $target
	 * 						jeżeli ma wartość Link::findUrlParams to metoda zapamiętuje wzorce podanych jako kolejne parametry, jako skojarzone z $target i z nazwami parametrów wystepujących we wzorcu (wykorzystywane przy znajdowaniu odpowiedniego wzorca do wygenerowania linka)
	 */
	static function urls($target, $regexps) {
			// znajdź wszystkie wystąpienia (?P<nazwa>regexp)
//			$regexps = array_slice(func_get_args(), 2);
			foreach($regexps as $i => &$regexp) {
				$regexpMd5 = md5($regexp);

				self::$targets[$regexpMd5] = $target;
				self::$parameters[$regexpMd5] = array();

				$expectedParameters = array();
				preg_match_all('`
					\\(\\?P<([a-zA-Z0-9_]+?)>	# (?P<nazwa>
						(?:						# /--
							(?>[^()[\\]]*)		# 	zero lub więcej znaków bez [] i ()
						|						# 	lub
							\\((?!\\?P)			# 	( bez ?P po nim
							(?:					# 	/--
								(?>[^()[\\]]+)	# 		pewna ilośc znaków bez [] i ()
							|					# 		lub
								(?R)			# 		rekursywnie cały wzorzec
							)*					# 	\-- powtórzone zero lub więcej razy
							\\)					# 	)
						|						# 	lub
							(?R)				# 	rekursywnie cały wzorzec
						)*						# \-- powtórzone zero lub więcej razy
					\\)							# )
				|								# lub
					\\[							# [
						(?:						# /--
							(?>[^[\\]]+)		#	zero lub więcej znaków bez []
						|						# lub
							(?R)				# 	rekursywnie cały wzorzec
						)*						# \-- powtórzone zero lub więcej razy
					\\]							# ]
				`ixs', $regexp, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
				foreach($matches as $m) {
					if(isset($m[1])) {
						$expectedParameters[] = $m[1][0];
						self::$parameters[$regexpMd5][$m[1][0]] = array($m[0][1], strlen($m[0][0]));
					}
				}
				self::$parameters[$regexpMd5] = array_reverse(self::$parameters[$regexpMd5]);

				sort($expectedParameters);
				self::$targetParameterSets[$target][] = array_flip($expectedParameters);

				self::$regexps[$target][serialize(array_unique($expectedParameters))][] = $regexp;

				self::$routeRegexp[] = "(?P<$regexpMd5>^$regexp\$)";

			}
	}
	static $targetParameterSets;
	static $actualParams;
	function redirect($url, $code = 302) {
		$url = trim($url);
		if($url{0} == '/') {
			$url = 'http://'.$_SERVER['SERVER_NAME'].$url;
		}
		$head = array(
			301 => "HTTP/1.0 301 Moved Permanently",
			302 => "HTTP/1.0 302 Found",
			404 => "HTTP/1.0 404 Not Found"
		);
		header($head[$code]);
		header("Location: $url");
		die;
	}
	static function init() {
		foreach(self::$urlPatterns as $ptrarr) {
			list($target, $methods) = each($ptrarr);
			unset($ptrarr[$target]);
			if(is_numeric($target)) {
				$target = $methods;
				self::$methods[$target] = array();
			} else {
				self::$methods[$target] = array_filter(array_map('trim', explode(',', $methods)));
			}
			self::urls($target, $ptrarr);
		}
//		self::$routeRegexp = implode('|', self::$routeRegexp);
	}
}

Link::init();


?>
