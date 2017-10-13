<?php

class Core_ActiveRecord implements ArrayAccess {
// methods operating on one record
	public $id;
	protected $__data = array();
	protected $__dataChanged = array();
	protected $__newRecord = false;
	const __evalPrefix = "eval";	///< jeżeli istnieje metoda której nazwa zaczyna się od tego i kotś odwoła się do indeksu nazywającego się tak jak pozostała część nazwy metody to ta metoda zostanie wywołana a jej wartość zwrócona zamiast wartości pola

	function offsetGet($field) {
		if(method_exists($this, self::__evalPrefix.ucfirst($field))) {
			return call_user_func(array($this, self::__evalPrefix.ucfirst($field)));
		}
		if(isset($this->__dataChanged[$field])) {
				return $this->__dataChanged[$field];
		}
		return $this->__data[$field];
	}

	function offsetExists($field) {
		return isset($this->__data[$field]);
	}

	function offsetSet($field, $value) {
		$this->__dataChanged[$field] = $value;
	}

	function offsetUnset($field) {
		unset($this->__data[$field]);
	}

	function __get($fieldName) {
		// jeżeli pytano o primaryKey to skonstruuj go z nazwy tabel, zapamiętaj na przyszśość i zwróć
		if($fieldName == "primaryKey") {
			return $this->primaryKey = $this->tableName."_id";
		}

		// przyjmij że pytano o nazwę obcej tabeli
		$foreignTableName = $fieldName;
		$toOne = false;

		// ToDo: jeżeli poniższy if nie znajdzie to można by dorobić odkamelizowywanie tego co jest w $foreignTableName
		if(isset($this->foreignTables[$foreignTableName]) || ($toOne = in_array($foreignTableName, $this->foreignTables))) {
			if($toOne) {
				$type = ">";
			} else {
				$type = $this->foreignTables[$foreignTableName];
			}
			$foreignObjectName = implode("", array_map("ucwords", explode("_", $foreignTableName))); // przekształć nazwę typu foo_bar na FooBar
// 			if($type == ">(company_id)") {
// 				$obs = M($foreignObjectName)->find("company_id = ".$this['company_id']);
// 				return $obs; // przypadek specjaly bo zwraca kolekcję, przez analogie do > taka składnia powinna zwracać pojedynczy obiekt
// 			}
//
			if($type{0} == '>') {
				$otherKeyName = M($foreignObjectName)->primaryKey;
				$myKeyName = $foreignTableName."_id";
			} else if($type{0} == '<') {
				$otherKeyName = $this->tableName."_id";
				$myKeyName = $this->primaryKey;
			} else {
				throw new Exception("First symbol of value from array ".get_class($this)."->foreignTables should be > or <");
			}
			if(strpos($type, '(') !== FALSE) {
				$parts = explode('(', $type);
				$cond = substr($parts[1], 0, -1);
				$type = $parts[0];
				if(strpos($cond, '=') !== FALSE) {
					list($myKeyName, $otherKeyName) = explode('=', $cond);
				} else {
					$myKeyName = $otherKeyName = $cond;
				}
			}
			//$obs = M($foreignObjectName)->find($otherKeyName." = ".$this[$myKeyName]);
			$dbId = M($foreignObjectName)->db->getDbId();
			//print $foreignObjectName." ".sql(array("%1|name = %2", $otherKeyName, $this[$myKeyName]), $dbId)." ".$dbId."\n";
			$obs = M($foreignObjectName)->find(sql(array("%1|name = %2", $otherKeyName, $this[$myKeyName]), $dbId));

			if(!isset($type{1}) || $type{1} != '*') {
				if (isset($obs[0]))
					$obs = $obs[0];
				else
					$obs = null;
			}
//			l($foreignObjectName.' '.$otherKeyName." = ".$this[$myKeyName]);
			return $obs;
		}
		throw new Exception("Object ".get_class($this).":$this->id has no field ".$fieldName);
	}

	function __call($methodName, $arguments) {
		$prefix = "findBy";
		$prefixLen = strlen($prefix);
		if(strncmp($methodName, $prefix, $prefixLen) == 0) {
			$fieldNames = explode("_", substr($methodName, $prefixLen));
			$cond = array();
			$options = isset($arguments[count($fieldNames)]) ?  $arguments[count($fieldNames)] : array();
			$i = 0;
			foreach($fieldNames as $name) {
				$name = $this->db->n(implode("_", array_map("strtolower", preg_split('`(?=[A-Z])`s', $name, -1, PREG_SPLIT_NO_EMPTY)))); // changes MultWordFieldName to multi_word_field_name
				if(is_array($arguments[$i])) {
					$cond[]	= "($name IN (".implode(",", array_map(array($this,'v'), $arguments[$i]))."))";
					if($options['keepOrder']) {
						$order = "position('^' || replace(($name)::text, '^', '^^') || '^' IN '^' || ".$this->v(implode('^', array_map(create_function('$a', 'return str_replace("^","^^",$a);'), $arguments[$i])))." || '^')";
						if(isset($options['order'])) {
							 if(is_array($options['order'])) {
							 	$options['order'][] = $order;
							 } else {
							 	$options['order'] = array($options['order'], $order);
							 }
						} else {
							$options['order'] = $order;
						}
					}
				} else {
					if($arguments[$i]===NULL) {
						$cond[] = "($name IS NULL)";
					} else {
						$cond[] = "($name = ".$this->db->v($arguments[$i]).")";  /// @todo escape value to secure it against sql injection
					}
				}
				$i++;
			}
			$cond = implode(" AND ", $cond);
			return $this->find($cond, $options);
		}
		throw new Exception("Object of class ".get_class($this)." has no method named ".$methodName);
	}



	protected function findByEqualityConditions($leftSides, $rightSides, $options) {
			$i = 0;
			foreach($leftSides as $name) {
				if(is_array($rightSides[$i])) {
					if(!$rightSides[$i]) {
						$cond[]	= "false";
						break;
					}
					$cond[]	= "($name IN (".implode(",", array_map(array($this,'v'), $rightSides[$i]))."))";
					if($options['keepOrder']) {
						$order = "position('^' || replace(($name)::text, '^', '^^') || '^' IN '^' || ".$this->v(implode('^', array_map(create_function('$a', 'return str_replace("^","^^",$a);'), $rightSides[$i])))." || '^')";
						if(isset($options['order'])) {
							 if(is_array($options['order'])) {
							 	$options['order'][] = $order;
							 } else {
							 	$options['order'] = array($options['order'], $order);
							 }
						} else {
							$options['order'] = $order;
						}
					}
				} else {
					$cond[] = "($name = ".$this->db->v($rightSides[$i]).")";  /// @todo escape value to secure it against sql injection
				}
				$i++;
			}
			$cond = implode(" AND ", $cond);
			return $this->find($cond, $options);
	}


	function asArray() {
		return $this->__dataChanged + $this->__data;
	}

	protected $__cache;
	function findBySql($sql, $options = array())
	{
		//print $sql."\n";
		$data = $this->db->getAssoc($sql);
		//print_r($data);
		return $this->wrap($data);
	}
	public function insert($options = array()) {

		$names = array();
		$values = array();
		$this->__dataChanged = $this->__dataChanged + $this->__data;
		if($this->__dataChanged) {
			$engine = $this->db->getEngine();
			if (!isset($this->__dataChanged[$this->primaryKey]) && $engine == "postgresql")
				$this->__dataChanged[$this->primaryKey] = $this->db->getNext($this->tableName, $this->primaryKey);

			foreach($this->__dataChanged as $k => $v) {
				$names[] = $this->n($k);
				if (isset($options['bytea']) && is_array($options['bytea']) &&  in_array($k, $options['bytea']))
					$values[] = $this->escapeBytea($v);
				else
					$values[] = $this->v($v);
			}
			$names = implode(", ", $names);
			$values = implode(", ", $values);

			$this->db->execQuery($a = "INSERT INTO $this->tableName($names) VALUES ($values)", false, '', isset($options['ignoreErrors']) && $options['ignoreErrors']);

			if (!isset($this->__dataChanged[$this->primaryKey]) && $engine == "mysql")
				$this->__dataChanged[$this->primaryKey] = $this->db->lastInsertId();

			M(get_class($this))->clearCache();
			$this->__cache = array();

			$this->__data = $this->__dataChanged;
			$this->__dataChanged = array();

			return $this->__data[$this->primaryKey];
		} else {
			return false;
		}
	}

	public function delete($options = array()) {
		$this->db->execQuery("DELETE FROM $this->tableName WHERE $this->primaryKey = ".$this->v($this[$this->primaryKey]));
		M(get_class($this))->clearCache();
		$this->__cache = array();
	}

	public function update($options = array()) {
		$sets = array();
		foreach($this->__dataChanged as $k => $v) {
			if($this->__data[$k] != $v || (($this->__data[$k] === NULL) ^ ($v === NULL) )) {
				$sets[] = $this->n($k)." = ".$this->v($v);
			}
		}
		if($sets) {
			$sets = implode(", ", $sets);
			$this->db->execQuery("UPDATE $this->tableName SET $sets WHERE $this->primaryKey = ".$this->v($this[$this->primaryKey]));
			M(get_class($this))->clearCache();
			$this->__cache = array();
		}

		$this->__data = $this->__dataChanged + $this->__data;
		$this->__dataChanged = array();

		return $this->__data[$this->primaryKey];
	}

	function save($options = array()) {
		if($this->__newRecord) {
			return $this->insert($options);
		} else {
			return $this->update($options);
		}
	}

	function getId() {
		return $this[$this->primaryKey];
	}

// method for creating record
	public function create($data = array()) {
		$className = get_class($this);
		$ar = new $className($data);
		$ar->__newRecord = true;
		return $ar;
	}


// methods operating on many records from table
	public $tableName; ///< tabela z którą klasa wywiedziona z tej klasy jest powiązana
	public $db; ///< obiekt połączenia z bazą danych wykorzystywany przez tą klasę do zadawania zapytań

	static $__instaces = array(); ///< instancje singletonów klas wywiedzonych z tej klasy, singletony związane są z tabelami i służą odwoływania się do grup rekordów

	public static function getInstance($name)
	{
		if(!isset(self::$__instaces[$name])) {
			$name = ucfirst($name);
			if(strncmp($name, "ActiveRecord_", strlen("ActiveRecord_")) != 0) {
				$className = "ActiveRecord_$name";
			} else {
				$className = $name;
			}
			self::$__instaces[$name] = new $className();
		}
		return self::$__instaces[$name];
	}

	function __construct($data = array())
	{
		$this->db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_PREF);
		$this->__data = $data;
	}

	function clearCache() {
		$this->__cache = array();
	}

	function first($cond = "true", $options = array()) {
		$temp = $this->find($cond, $options);
		if ($temp)
			return $temp[0];
		else
			return array();
	}

	function find($cond = "true", $options = array()) {
		if($cond === '' || $cond === NULL) return array();
		if(is_numeric($cond)) {
			$cond = "$this->primaryKey = $cond";
		} else if(is_array($cond)) {
			if($cond) {
				$name = $this->db->n($this->primaryKey);
				if($options['keepOrder']) {
					$order = "position('^' || replace(($name)::text, '^', '^^') || '^' IN '^' || ".$this->v(implode('^', array_map(create_function('$a', 'return str_replace("^","^^",$a);'), $cond)))." || '^')";
					if(isset($options['order'])) {
						 if(is_array($options['order'])) {
						 	$options['order'][] = $order;
						 } else {
						 	$options['order'] = array($options['order'], $order);
						 }
					} else {
						$options['order'] = $order;
					}
				}
				$cond = trim(implode(", ", $cond));
				if($cond != '') {
					$cond = "$name IN (".$cond.")";
				} else {
					return array();
				}
			} else {
				return array();
			}
		}
		$order = isset($options['order']) ? 'ORDER BY ' .(is_array($options['order']) ? implode($options['order']) : $options['order'])  : '';
		$limit = isset($options['limit']) ? 'LIMIT '.(is_array($options['limit']) ? ((int)$options['limit'][1]).' OFFSET '.((int)$options['limit'][0]) : (int)$options['limit']) : '';

		if(isset($options['fields'])) {
			if(is_string($options['fields'])) {
				$fields = $options['fields'];
			} else {
				$fields = array();
				foreach($options['fields'] as $k => $v) {
					if(is_numeric($k)) {
						$fields[] = $v;
					} else {
						$fields[] = '"'.$k.'" AS "'.$v.'"';
					}
				}
				$fields = implode(', ', $fields);
			}
		} else {
			$fields = '*';
		}
		$sql = "SELECT $fields FROM $this->tableName WHERE $cond $order $limit";
		//print $sql; die;
		return $this->findBySql($sql, $options);
	}

	function findOrCreate($cond = "true", $options = array())
	{
		$o = $this->find($cond, $options);
		if (!$o) $o[0] = $this->create();

		return $o;
	}

	function wrap($rows) {
		if($rows) {
			return new Core_ActiveRecordSet($rows, get_class($this));
		} else {
			return array();
		}
	}

// helper functions
	public function n($name) {
		return $this->db->n($name);
	}

	public function v($value) {
		return $this->db->v($value);
	}
	public function escapeBytea($value) {
		return $this->db->escapeBytea($value);
	}
	public function unescapeBytea($value) {
		return $this->db->unescapeBytea($value);
	}
}

/**
 * Shorthand for Core_ActiveRecord::getInstance($name)
 *
 * @param $name of ActiveRecord singleton to retrieve
 */
function M($name)
{
	return Core_ActiveRecord::getInstance($name);
}


/**
 * Escapes and quotes data for sql() function. Can be used/sold separatedly.
 */
function sqlEscape($val, $mod = 'value', $dbId = '')
{
	if ($dbId != "")
	{
		$db = Core_Database::getInstanceByDbId($dbId);
	}
	else
		$db = Core_Database::getInstance('mysql', DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_PREF);

	if(is_bool($val)){
		if($val)
			return 'true';
		else
			return 'false';
	}
	if($val === NULL) {
		return "NULL";
	}
	if($mod == 'sql') {
		return $val;
	}
	if(is_numeric($val)) {
		// is a number
		if((int)$val != $val) {
			// is not integer
			$val = rtrim(rtrim(sprintf("%.15F", $val), '0'), '.');
		} else {
			// is integer
			$val = (int)$val;
		}
		$is_numeric = true;
	} else {
		$is_numeric = false;
	}
	switch($mod) {
		case 'namePart':
			if(!preg_match('`^[a-zA-Z0-9_$]*$`', $val, $m)) {
				throw new Exception($val." is not a valid part of postgres identifier");
			}
		break;
		case 'name':
			if(!preg_match('`^[a-zA-Z][a-zA-Z0-9_$]*$`', $val, $m)) {
				throw new Exception($val." is not a valid postgres identifier");
			}
			if ($db->getEngine() == "mysql")
				$val = "`".$val."`";
			else
				$val = '"'.$val.'"';
		break;
		case 'valuePart':
			if(!$is_numeric) {
				if ($db->getEngine() == "mysql")
					$val = pg_escape_string($db->conn, $val);
				else
					$val = pg_escape_string($db->conn, $val);
			}
		break;
		default:
			if(!$is_numeric) {
				if ($db->getEngine() == "mysql")
					$val = '"'.mysql_real_escape_string($val).'"';
				else
					$val = "'".pg_escape_string($db->conn, $val)."'";
			}
		break;
	}
	return $val;
}
/** Returns $arr[0] with %% replaced with % and %ident and %(ident) replaced with $arr['ident']
* escaped with pg_escape_string and quoted with ' if it is not numeric
* You can reference %(ident|mode) where mode can be:
*   - name (quotes with ", thows exception if $arr['ident'] is not valid postgres identifier),
*   - namePart (throws exception if $arr['ident'] is not valid part of postgres identifier),
*   - valuePart (escapes witn pg_escape_string)
*   - sql (returns $arr['ident'] without any quoting or escaping)
* @example echo sql(array("SELECT * FROM %(table|name) shop_id = %(shop_id) AND name = 'shop_%1|valuePart' AND shop_type=%type", 'shop_id' => 1.15, "te%st", 'table'=>'shop', 'type'=>'market' ));
*/
function sql($arr, $dbId = "")
{
	if(!is_array($arr))
	{
		$arr = array($arr);
	}

	if (is_array($dbId))
	{
 		$arr = $arr + $dbId;
 		$dbId = "";
 	}

	return preg_replace(
		array(
			'`%%`',
			'`%(\\()?([a-zA-Z0-9_]+?)(?:\\|([a-zA-Z0-9_]+?))?(?(1)\\)|(?![a-zA-Z0-9_]))`e'
		), array(
			'%',
			'sqlEscape($arr["\\2"], "\\3", $dbId)'
	) , $arr[0]);
}


?>
