<?php 
namespace Pusaka\Database\Mysql;

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

		return $this->result->num_rows;

	}

	function all() {

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetch_object()) {
			$this->rows[] = $row;
			unset($row);
		}

		$this->result->free();

		return $this->rows;

	}

	function first() {

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetch_object()) {
			$this->rows[] = $row;
			unset($row);
			break;
		}

		$this->result->free();

		return $this->rows[0] ?? NULL;

	}

	function last() {

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetch_object()) {
			$this->rows[0] = $row;
			unset($row);
		};

		return $this->rows[0] ?? NULL;		

	}

}