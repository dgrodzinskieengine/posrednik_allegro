<?php
/**
 * Encapsulates iterable collection of ActiveRecord-s. Provides [] operator.
 * When accessed like this $a[5] gives sixth ActiveRecord in collection (they are numbered from zero)
 * Provides a shorthand to fields of first ActiveRecord in collection. $a['field_name'] is the same as $a[0]['field_name']
 */
class Core_ActiveRecordSet implements ArrayAccess, IteratorAggregate, Countable  {
	protected $activeRecords = array();

	function __construct($rows = array(), $rowClassName = '') {
		if(is_array($rows)) {
			foreach($rows as $row) {
				$this->activeRecords[] = new $rowClassName($row);
			}
		}
	}
	function first() {
		return $this->activeRecords[0];
	}
	function offsetGet($i) {
		if(is_numeric($i)) {
			return $this->activeRecords[$i];
		} else {
			return $this->activeRecords[0][$i];
		}
	}
	function offsetExists($i) {
		if(is_numeric($i)) {
			return isset($this->activeRecords[$i]);
		} else {
			return isset($this->activeRecords[0][$i]);
		}
	}
	function offsetSet($i, $value) {
		if(is_numeric($i)) {
			$this->activeRecords[$i] = $value;
		} else {
			$this->activeRecords[$i] = $value;
		}
	}
	function offsetUnset($i) {
		if(is_numeric($i)) {
			unset($this->activeRecords[$i]);
		} else {
			unset($this->activeRecords[0][$i]);
		}
	}
	public function getIterator() {
		return new Arrayiterator($this->activeRecords);
	}
	public function asJSON() {
		$results = array();
		foreach($this->activeRecords as $r) {
			$results[] = $r->asArray();
		}
		return json_encode($results);
	}
	function asArray() {
		$results = array();
		foreach($this->activeRecords as $r) {
			$results[] = $r->asArray();
		}
		return $results;
	}
	function getIds() {
		$ids = array();
		foreach($this->activeRecords as $r) {
			$ids[] = $r[$r->primaryKey];
		}
		return $ids;
	}
	public function count() {
		return count($this->activeRecords);
	}


	/**
	 * Dodaje do danych tego obiektu inne dane
	 * @params array|Core_ActiveRecordSet ... Dane jako tablice lub obiekty Core_ActiveRecordSet
	 */
	public function appendData() {
		foreach(func_get_args() as $data) {
			if($data) {
				if ($data instanceof Core_ActiveRecordSet) {
					$this->activeRecords = array_merge($this->activeRecords, $data->activeRecords);
				}
				elseif(is_array($data) && $data) {
					$this->activeRecords = array_merge($this->activeRecords, $data);
				}
			}
		}
	}

	public function reversed() {
		return new Core_ActiveRecordSetReverseIterator($this);
	}
}


class Core_ActiveRecordSetReverseIterator implements Iterator  {
	private $set;
	private $cur;
	function __construct(Core_ActiveRecordSet $set) {
		$this->set = $set;
	}
	public function rewind() {
	    $this->cur = count($this->set)-1;
	}
	public function key() {
    	return $this->cur;
  	}
	public function current() {
		return $this->set[$this->cur];
	}
	public function next() {
		--$this->cur;
	}
  public function valid()
  {
    return $this->cur >= 0;
  }
}
?>