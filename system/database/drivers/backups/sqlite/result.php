<?php 
namespace Pusaka\Database\Sqlite;

class Result {

	private $rows;
	private $result;

	function __construct($result) {
		
		$this->result 	= $result;
		$this->rows 	= [];

	}

	function count() {

		if(!$this->result) {
			return 0;
		}

		return 0;
		//return $this->result->num_rows;

	}

	function all() {

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
			$this->rows[] = (object) $row;
			unset($row);
		}

		$this->result->finalize();

		return $this->rows;

	}

	function first() {

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
			$this->rows[] = (object) $row;
			unset($row);
		}

		$this->result->finalize();

		return isset($this->rows[0]) ? $this->rows[0] : NULL;

	}

	function last() {

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
			$this->rows[0] = (object) $row;
			unset($row);
		}

		return $this->rows[0] ?: NULL;		

	}

}