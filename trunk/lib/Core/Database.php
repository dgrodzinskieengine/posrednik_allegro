<?php

class Core_Database
{
	public $conn = null;
	private $engine = "postgresql";
	private $dbId = "";
	private $rsAssoc = array();

	static private $thisInstance = array();

	private function __construct ($engine, $host, $port, $user, $password, $name)
	{
		$this->engine = $engine;
		switch ($engine)
		{
			case 'mysql':
				if ($port == null) $port = 3306;
				$this->conn = mysql_connect($host.($port != null ? ':'.$port : ''), $user, $password);
				mysql_select_db($name, $this->conn);
				mysql_query("SET NAMES 'utf8'", $this->conn);
				mysql_query("SET CHARACTER SET 'utf8'", $this->conn);
				mysql_query("SET collation_connection = 'utf8_polish_ci'", $this->conn);
				break;
			default:
				if ($port == null) $port = 5432;
				$this->conn = pg_connect("host={$host} port={$port} dbname={$name} user={$user} password={$password}");
		}
	}

	static public function getInstanceByDbId($dbId)
	{
		if (isset(self::$thisInstance[$dbId]))
			return self::$thisInstance[$dbId];
		else
			die("Nie istnieje instancja bazy dla dbId = {$dbId}");
	}

	static public function getInstance($engine, $host, $port, $user, $password, $name)
	{
		$dbId = $engine."-".$host."-".$port."-".$user."-".$password."-".$name;

		if(!isset(self::$thisInstance[$dbId]) || self::$thisInstance[$dbId] == null)
		{
			switch ($engine)
			{
				case 'mysql':
					self::$thisInstance[$dbId] = new Core_Database($engine, $host, $port, $user, $password, $name);
					break;
				default:
					self::$thisInstance[$dbId] = new Core_Database($engine, $host, $port, $user, $password, $name);
			}
			self::$thisInstance[$dbId]->dbId = $dbId;
		}
		//mysql_select_db($name, self::$thisInstance[$dbId]->conn);
		return self::$thisInstance[$dbId];
	}

	function __destruct ()
	{
		$this->rsAssoc = array();
	}

	public function execQuery($query, $returnAffected=false, $uniqueId='', $ignoreErrors = false)
	{
		if ($this->engine == "mysql") {
			if($ignoreErrors) {
				$_rs = @mysql_unbuffered_query($query, $this->conn);
			} else {
				$_rs = mysql_unbuffered_query($query, $this->conn);
			}
		}
		elseif ($this->engine == "postgresql")
		{
			if($ignoreErrors) {
				$_rs = @pg_query($this->conn, $query); //$GLOBALS['log_sql'][] = $query;
			} else {
				$_rs = pg_query($this->conn, $query); //$GLOBALS['log_sql'][] = $query;
			}
		}

		if ($returnAffected == true)
		{
			if ($this->engine == "mysql")
			{
				$affectedRows = mysql_affected_rows($_rs);
			}
			elseif ($this->engine == "postgresql")
			{
				$affectedRows = pg_affected_rows($_rs);
			}
			Return $affectedRows;
		}

		return true;
	}

	public function getField($query, $uniqueId='')
	{
		$_assoc = $this->getRow($query, $uniqueId);

		if($_assoc!==false && count($_assoc)>0)
			return array_shift($_assoc);
		else
			return false;
	}

	public function getValue($query, $uniqueId='')
	{
		return $this->getField($query, $uniqueId);
	}

	public function getNext($tableName, $primaryKey)
	{
		if ($this->engine == "mysql")
		{
			throw new Exception("MySQL not supported by ".__CLASS__."::".__FUNCTION__."()");
		}
		elseif ($this->engine == "postgresql")
		{
			$default = $this->getField("SELECT column_default FROM information_schema.columns WHERE table_schema='public' AND table_name='".$tableName."' AND column_name='".$primaryKey."'");
			return $this->getField("SELECT ".$default);
		}

	}

	public function getRow($query, $uniqueId='')
	{
		$_assoc = $this->getAssoc($query, $uniqueId);

		if($_assoc!==false&&count($_assoc)>0)
			Return array_shift($_assoc);
		else
			Return false;
	}

	public function getResult($query, $uniqueId='')
	{
		//$query = "BEGIN TRANSACTION READ ONLY; ".$query."; COMMIT;";

		if ($this->engine == "mysql")
			$_rs = mysql_query($query, $this->conn);
		elseif ($this->engine == "postgresql")
		{
			$_rs = pg_query($this->conn, $query);

		}
		$_result  = new Core_Result($_rs, $this->engine);
		Return $_result;
	}

	public function getIdAssoc($query, $uniqueId='')
	{
		Return $this->getAssoc($query, $uniqueId, true);
	}

	public function getAssoc($query, $uniqueId='', $firstKeyAsId=false, $sigleDimension=false)
	{
		$_assoc = array();
		if ($this->engine == "mysql")
		{
			$_rs = mysql_query($query, $this->conn);

			if ($_rs === false)
				throw new Exception(mysql_error($this->conn)." ($query)");

			$rn = intval(mysql_num_rows($_rs));
			if($rn>0 && $firstKeyAsId == true )
			{
				$firstKey = mysql_field_name($_rs,0);
				if($sigleDimension == true)
				{
					$secondKey = mysql_field_name($_rs,1);
				}
			}

			while ($_row = mysql_fetch_assoc($_rs))
			{
				if($firstKeyAsId&&$rn>0)
				{
					if($sigleDimension==true)
					{
						$_assoc[$_row[$firstKey]] = $_row[$secondKey];
					}
					else
					{
						$_assoc[$_row[$firstKey]] = $_row;
					}
				}
				else
				{
					$_assoc[] = $_row;
				}
			}
		}
		elseif ($this->engine == "postgresql")
		{
			$_rs = pg_query($this->conn, $query);

			if ($_rs === false)
				throw new Exception(pg_last_error($this->conn)." ($query)");

			$rn = intval(pg_num_rows($_rs));
			if($rn>0 && $firstKeyAsId == true )
			{
				$firstKey=pg_field_name($_rs,0);
				if($sigleDimension == true)
				{
					$secondKey=pg_field_name($_rs,1);
				}
			}
			while ($_row = pg_fetch_assoc($_rs))
			{
				if($firstKeyAsId&&$rn>0)
				{
					if($sigleDimension==true)
					{
						$_assoc[$_row[$firstKey]] = $_row[$secondKey];
					}
					else
					{
						$_assoc[$_row[$firstKey]] = $_row;
					}
				}
				else
				{
					$_assoc[] = $_row;
				}
			}
		}

		if(count($_assoc)>0)
			return $_assoc;
		else
			Return false;

	}

	public function deleteQuery($table, $idValue, $idxName='id')
	{
		if(is_array($idValue))
		{
			if(count($idValue)>0)
			{
				$cnt=0;
				foreach($idValue as $whereFieldName => $whereFieldValue)
				{
					$where.= ($cnt>0?' AND ':'')." ".$this->fieldQuotas($whereFieldName)." = ".$this->valueQuotas($whereFieldValue)." ";
					$cnt++;
				}
			}
		}
		$_q = "DELETE FROM";
		if ($this->engine == "mysql")
		{
			$_q.=' `'.$table.'` ';
		}
		elseif ($this->engine == "postgresql")
		{
			$_q.=' "'.$table.'" ';
		}
		$_q.=' WHERE '.(isset($where)?$where:$this->fieldQuotas($idxName).' = '.$this->valueQuotas($idValue));

		$this->execQuery($_q);

		if ($this->engine == "mysql")
		{
			Return mysql_affected_rows($this->conn);
		}
		Return true;
	}

	public function updateValues($table, $values, $idValue, $idxName='id', $dieandshow=0)
	{
		if(count($values)>0)
		{
			$_q = 'UPDATE ';
			if ($this->engine == "mysql")
			{
				$_q.=' `'.$table.'` ';
			}
			elseif ($this->engine == "postgresql")
			{
				$_q.=' "'.$table.'" ';
			}
			$_q.=' SET  ';
			$cnt=0;

			foreach($values as $fieldName=>$fieldValue)
			{
				$_q.=($cnt>0?', ':'').$this->fieldQuotas($fieldName);
				$_q.=' = ';
				if(is_array($fieldValue))
				{
					$_preparedFieldValue = $this->prepareValue($fieldValue);
				}
				else
				{
					$_preparedFieldValue = $this->valueQuotas($fieldValue);
				}

				$_q.=$_preparedFieldValue;
				$cnt++;
			}
			$_q.=' WHERE '.$this->fieldQuotas($idxName).' = '.$this->valueQuotas($idValue)." ;\n";

			if($dieandshow==1) die($_q);
			if($dieandshow==2) return $_q;
			if($dieandshow==3) echo $_q."\n";

			$this->execQuery($_q);
			if($dieandshow==4) return $_q."\n";
			if ($this->engine == "mysql")
			{
				Return mysql_affected_rows($this->conn);
			}
			Return true;
		}
	}

	public function prepareValue($args)
	{
		if ($this->engine == "mysql")
		{
			Return self::prepareValueMySql($args);
		}
		elseif ($this->engine == "postgresql")
		{
			Return self::prepareValuePgSql($args);
		}
	}

	public static function prepareValueMySql($args)
	{
		if(!isset($args['value']))
		{
			trigger_error("brak klucza value w tablicy wartosci", E_USER_ERROR);
		}
		else
		{
			// dopisane na szybko 2008-12-30, bo tutaj nic nie było...
			return "'".mysql_escape_string($args['value'])."'";
		}
	}

	private static function intToPgSql($val)
	{
		Return intval($val);
	}

	private static function floatToPgSql($val)
	{
		$val=floatval($val);
		if(!is_numeric($val)) $val=0;
		$val = number_format($val, 2, '.', '');

		Return $val;;
	}

	private static function boolToPgSql($val)
	{
		Return (bool) $val;
	}

	private static function dateToPgSql($val)
	{
		Return $val;
	}

	private static function strToPgSql($val)
	{
		Return strval($val);
	}

	public static function prepareValuePgSql($args)
	{
		$allowedTypesAndQuotation=array(
				'integer'=>false,'bigint'=>false, 'numeric'=>false,'bool'=>false, 'double precision'=>true,
				'text'=>true,'varchar'=>true,'date'=>true,'timestamp'=>true,
			);
		$allowedTypesAndCast=array(
				'integer'=>'int','bigint'=>'int', 'numeric'=>'float', 'double precision'=>'float',
				'text'=>'str','varchar'=>'str','date'=>'date','timestamp'=>'date',
				'bool'=>'bool'
			);

		$returnString='';
		$preparedValue='';
		$typeCast='';
		if (!array_key_exists('value',$args) || !array_key_exists('type',$args))
		{
			dump($args,'xxxxxxxxxxxxxxxxxxxx',false,true);
			trigger_error("brak klucza value lub type w tablicy pole=>definicja", E_USER_ERROR);
		}
		else
		{
			$_value=$args['value'];//self::grabValue($args);
			$_type=$args['type'];
			$validateFunction=$allowedTypesAndCast[$_type].'ToPgSql';
			if((array_key_exists('null_empty',$args) && $args['null_empty']==true && empty($args['value']) ) || strtoupper($args['value'])==='NULL' || strtoupper($args['value'])==='N;')
			{
				$_value='NULL';
				$_null=true;
			}
			else
			{
				$_value=self::$validateFunction($_value);
			}
			if(array_key_exists('function_call',$args)&&$args['function_call']==true)
				$call=true;
			else
				$call=false;


			if(!array_key_exists($_type,$allowedTypesAndQuotation))
			{
				trigger_error("Niedozwolony typ w tablicy pole=>definicja", E_USER_ERROR);
			}
			else
			{
				$typeQuot=$allowedTypesAndQuotation[$_type];
			}

			if(isset($args['cast'])&&$args['cast']==true)
				$_cast=true;
			else
				$_cast=false;


			if(!$call&&$_value!=='NULL'&&($typeQuot||$_cast))
			{
				$returnString.= "'".self::clearToPgSql($_value)."'";
			}
			else
			{
				$returnString.=$_value;
			}

			if($_cast&&$_value!=='NULL')
			{
				$returnString .= '::'.$_type;
			}
		}
		Return $returnString;

	}

	public static function clearToPgSql($str)
	{
		$returnString = addcslashes(eregi_replace("[\\]{2,}","\\",stripslashes($str)),'\'\\');
		Return $returnString;
	}

	public function insertValues($table, $values, $dieandshow=0)
	{
		if(count($values)>0)
		{
			$_q = 'INSERT INTO ';
			if ($this->engine == "mysql")
			{
				$_q.=' `'.$table.'` ';
			}
			elseif ($this->engine == "postgresql")
			{
				$_q.=' "'.$table.'" ';
			}
			$_q.=' ( ';
			$_qf='';
			$_qv='';
			$cnt=0;
			foreach($values as $fieldName=>$fieldValue)
			{
				if(is_array($fieldValue))
				{
					$_preparedFieldValue = $this->prepareValue($fieldValue);
				}
				else
				{
					$_preparedFieldValue = $this->valueQuotas($fieldValue);
				}
				$_qf.=($cnt>0?', ':'').$this->fieldQuotas($fieldName);
				$_qv.=($cnt>0?', ':'').$_preparedFieldValue;
				$cnt++;
			}
			$_q.=$_qf;
			$_q.=' ) VALUES ( ';
			$_q.=$_qv;
			$_q.=" ) ; \n";

			if($dieandshow==1) die($_q);
			if($dieandshow==2) return $_q;
			if($dieandshow==3) echo $_q."\n";

			$this->execQuery($_q);

			if($dieandshow==4) return $_q."\n";
			if ($this->engine == "mysql")
			{
				Return mysql_insert_id($this->conn);
			}
			Return true;
		}
	}

	private function fieldQuotas($field)
	{
		if ($this->engine == "mysql")
		{
			Return self::fieldQuotasMySql($field);
		}
		elseif ($this->engine == "postgresql")
		{
			Return self::fieldQuotasPgSql($field);
		}
	}
	public function getQuery()
	{
		return $this->lastQuery;
	}

	public static function fieldQuotasMySql($field)
	{
		Return '`'.$field.'`';
	}

	public static function fieldQuotasPgSql($field)
	{
		Return '"'.$field.'"';
	}

	public static function valueQuotasMySql($value)
	{
		$nonquoted_pattern = '(^NULL|^NOW)';

		if(ereg($nonquoted_pattern,$value))
			Return $value;
		else
			Return "'".addslashes($value)."'";
	}

	function valueQuotasPgSql($value)
	{
		$nonquoted_pattern='(^NULL$|^ASCII\\s*[(]\\.*?[)]$|^CHAR\\s*[(]\\.*?[)]$|^MD5\\s*[(]\\.*?[)]$|^SHA1\\s*[(]\\.*?[)]$|^ENCRYPT\\s*[(]\\.*?[)]$|^RAND\\s*[(]\\.*?[)]$|^LAST_INSERT_ID$';
		$nonquoted_pattern.='|^SOUNDEX$|^LCASSE$|^UCASE$|^NOW\\s*[(]\\s*[)]$|^PASSWORD|^CURDATE|^CURTIME';
		$nonquoted_pattern.='|^FROM_DAYS|^FROM_UNIXTIME|^PERIOD_ADD|^PERIOD_DIFF';
		$nonquoted_pattern.='|^TO_DAYS|^UNIX_TIMESTAMP|^USER|^WEEKDAY|^CONCAT\\s*[(]\\.*?[)]$|^TRUE$|^FALSE$)';

		if(preg_match($nonquoted_pattern,$value))
			Return $value;
		else
			Return "'".addslashes($value)."'";
	}

	private function valueQuotas($value)
	{
		if ($this->engine == "mysql")
		{
			Return self::valueQuotasMySql($value);
		}
		elseif ($this->engine == "postgresql")
		{
			Return self::valueQuotasPgSql($value);
		}

	}
	public function v($value) {
		if ($this->engine == "mysql") {
			return "'".mysql_real_escape_string($value)."'";
		}
		if ($this->engine == "postgresql") {
			if($value === NULL) return "NULL";
			return "'".pg_escape_string($value)."'";
		}

	}
	public function escapeBytea($data) {
		return "'".pg_escape_bytea($this->conn, $data)."'";
	}

	public function unescapeBytea($data) {
		return pg_unescape_bytea($this->conn, $data);
	}
	public function n($name) {
		if ($this->engine == "mysql") {
			return '`'.mysql_real_escape_string($name).'`';
		}
		if ($this->engine == "postgresql") {
			return '"'.pg_escape_string($name).'"';
		}
	}
	public function lastInsertId($seqName="") {
		switch($this->engine) {
			case "mysql":
				return mysql_insert_id($this->conn);
				break;
			case "postgresql":
				if($seqName) {
					$sql = "SELECT currval(".$this->v('"'.$seqName.'"').")";
				} else {
					$sql = "SELECT lastval()";
				}
				$result = pg_fetch_result(pg_query($this->conn, $sql), 0, 0);

				return $result;
			break;
		}
	}
	public function fetchQuery($sql) {
		$data = array();
		if ($this->engine == "mysql") {
			throw new Exception("MySQL not supported by ".__CLASS__."::".__FUNCTION__."()");
		} else if($this->engine == "postgresql") {
			if(($result = pg_query($this->conn, $sql)) !== FALSE) {
				$data = pg_fetch_all($result);  //$GLOBALS['log_sql'][] = $sql;
			}
		}
		return $data;
	}

	public function getEngine()
	{
		return $this->engine;
	}
	public function getDbId()
	{
		return $this->dbId;
	}
}

?>
