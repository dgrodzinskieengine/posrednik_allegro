<?php
	class Core_GoogleTranslate
	{
		/// poniżej pozostaw bez zmian (ewentualnie skonfiguruj)
		static private $thisInstance = null;
		private $api_key, $client_id, $api_url;
		
		public function __construct()
		{
			# https://cloud.google.com/console#/project/717733688554/apiui/app/key/k0
			# pawel.walaszek@gmail.com

			$this->api_key = 'AIzaSyDgAIlhtTzsfQCEbSI--bp5GWsSp5VdUYk';
			$this->client_id = '717733688554.apps.googleusercontent.com';
			
			$this->api_url = 'https://www.googleapis.com/language/translate/v2';
	// 		key=INSERT-YOUR-KEY&source=en&target=de&q=Hello%20world
		}

		static public function getInstance()
		{
			if(!isset(self::$thisInstance) || self::$thisInstance == null)
			{
				self::$thisInstance = new Core_GoogleTranslate();
			}

			return self::$thisInstance;
		}
		
		public function translate($request, $shop_id = false, $test = false)
		{
			if(!(isset($request['from']) && $request['from'] && isset($request['to']) && $request['to'] && isset($request['text']) && trim($request['text']) != ''))
				return array("errors" => array("Wystąpił nieoczekiwany błąd, prosimy spróbować ponownie"));
			
			$text = $base_text = trim($request['text']);
			$from = $request['from'];
			$to = $request['to'];
			
			$formats = array("html" => 1, "text" => 1);
			if(isset($formats[$request['format']]))
				$format = $request['format'];
			else
				$format = 'html';
			
			preg_match_all('`(\{\$.*\})`iU', $text, $match, PREG_SET_ORDER);
			
			$strtr_array = array();
			$i = 1;
			foreach($match as $_match)
			{
				$strtr_array[$_match[1]] = "([[{$i}]])";
				$i++;
			}
	
			$text = strtr($text, $strtr_array);

			
			while($text != '')
			{
				$limit = 5000;
				$length = 5000;
				if (strlen($text) > $limit)
				{
					while(substr($text, $limit, 1) != "<" && $limit >= 0)
						$limit--;
					if($limit == 0)
					{
						$limit = 5000;
						while(substr($text, $limit, 1) != " " && $limit >= 0)
						$limit--;
					}
					$doTlumaczenia = substr($text, 0, $limit);

					$text = substr($text, $limit);
				}
				else
				{
					$doTlumaczenia = $text;
					$text = '';
				}
				
// 				$text_encode = urlencode($doTlumaczenia);
				
				$request_params = array();
				$request_params['key'] = $this->api_key;
				$request_params['source'] = $from;
				$request_params['target'] = $to;
				$request_params['q'] = $doTlumaczenia;
				$request_params['format'] = $format;
				
				
// 				l($request_params);die;
				
				$ch = curl_init($this->api_url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
				ob_start();
				curl_exec($ch);
				$json = ob_get_contents();
				ob_end_clean();
				curl_close ($ch);

				$translated = json_decode($json, true);
				
				if($translated['data'])
				{
					
					$przetlumaczone .= $translated['data']['translations'][0]['translatedText'];
				}
	// 			elseif($translated->responseStatus == 400 && $from == "pl" && $try_other == false)
	// 			{
	// 				$text_en = google_translate($text, 'pl', 'en', true);
	// 				return google_translate($text_en, 'en', $to);
	// 			}
			}
			
			$przetlumaczone = strtr($przetlumaczone, array_flip($strtr_array));
			
			if(trim($przetlumaczone) != '')
				$out['translatedText'] = $przetlumaczone;
			// else
			// 	$out['translatedText'] = $base_text;
			
			if($shop_id && isset($out['translatedText']))
			{
				$google_translate_history = M("Ecommerce24hGoogleTranslateHistory")->create();
				$google_translate_history['customers_shops_id'] = $shop_id;
				$google_translate_history['google_translate_history_day'] = date("Y-m-d", time());
				$google_translate_history['google_translate_history_from_language'] = $from;
				$google_translate_history['google_translate_history_to_language'] = $to;
				$google_translate_history['google_translate_history_from_text'] = $base_text;
				$google_translate_history['google_translate_history_to_text'] = $przetlumaczone;
				$google_translate_history['google_translate_history_text_length'] = strlen($base_text);
				$google_translate_history->save();
			}
			
			return $out;
		}
	}
