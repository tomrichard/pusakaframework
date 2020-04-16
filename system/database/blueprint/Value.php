<?php 
namespace Pusaka\Database\Blueprint;

class Value {

	private $original;
	private $type;
	private $val;

	function __construct($val = NULL) {

		$this->original = $val;

		if(is_null($val)) {
			$this->val 	= 'null';
			$this->type = 'NULL';
		}else 

		if(strtolower($val) === 'null') {
			$this->val 	= 'null';
			$this->type = 'NULL';
		}else 

		if(is_numeric($val)) {

			$this->val 	= $val;
			$this->type = 'NUMERIC';

		}else 

		if(is_string($val)) {
			$this->val 	= "'" . $val . "'";
			$this->type = 'STRING';
		}

	}

	function type() {
		return $this->type;
	}

	function get() {
		return $this->val;
	}

}