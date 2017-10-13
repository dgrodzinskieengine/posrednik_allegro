<?

class Core_Result
{
	private $engine = "postgresql";
	private $counter;

	public function __construct (&$_rs, $engine = "postgresql")
	{
		$this->rs = $_rs;
		$this->engine = $engine;

		if ($this->engine == "mysql")
			$this->counter = mysql_num_rows($this->rs);
		elseif ($this->engine == "postgresql")
			$this->counter = pg_num_rows($this->rs);
	}

	public function fetchRow()
	{
		if ($this->engine == "mysql")
			Return mysql_fetch_assoc($this->rs);
		elseif ($this->engine == "postgresql")
			Return pg_fetch_assoc($this->rs);
	}

	function getNumRows() {
		Return $this->counter;
	}

	function __destruct ()
	{
		if ($this->engine == "mysql")
			mysql_free_result($this->rs);
		elseif ($this->engine == "postgresql")
			pg_free_result($this->rs);
	}

}

?>