<?php 
namespace Pusaka\Database\Blueprint;

class Column {

	private $name;
	private $type;
	private $length;
	private $null;

	function __construct($name = NULL) {
		$this->name = $name;
	}

	function __set($prop, $value) {
		$this->{$prop} = $value;
	}

	function __get($prop) {
		return $this->{$prop};
	}

}