<?php 
namespace Pusaka\Database;

use ReflectionClass;
use ReflectionProperty;

//use Pusaka\Database\Model;
use Pusaka\Database\Manager;
use Pusaka\Database\SqlException;
use Pusaka\Database\ConnectionException;
use Pusaka\Database\Blueprint\Column;
use Pusaka\Database\Blueprint\Value;

class Model {

	public function __construct($value = NULL) {

		if(!is_null($value)) {
			
			foreach ($value as $key => $value) {
				$this->{$key} = $value;
			}
			
		}

	}

	public function __set($prop, $value) {

		$this->{$prop} = $value;

	}

    public function save() {

    	$class 		= get_called_class();

    	$uniqe 		= 'id';
    	$table 		= strtolower(basename($class));
    	$connection = 'default';

    	try {
    		$uniqe		 	= $class::UNIQE;
    	}catch(\Error $e) {}

    	try {
    		$table  		= $class::TABLE;
    	}catch(\Error $e) {}

    	try {
    		$connection  	= $class::CONNECTION;
    	}catch(\Error $e) {}

    	if(!isset($this->$uniqe)) {
    		$this->$uniqe = strtoupper(date('YmdHis').uniqid());
    	}
    	
    	$query = Manager::on($connection)->builder();

    	$data  = (array) $this;

    	$query->table($table)->insert($data);

    	unset($query);

    }

    public function update() {

    	$class 		= get_called_class();

    	$uniqe 		= 'id';
    	$table 		= strtolower(basename($class));
    	$connection = 'default';

    	try {
    		$uniqe		 	= $class::UNIQE;
    	}catch(\Error $e) {}

    	try {
    		$table  		= $class::TABLE;
    	}catch(\Error $e) {}

    	try {
    		$connection  	= $class::CONNECTION;
    	}catch(\Error $e) {}

    	if(!isset($this->$uniqe)) {
    		$this->$uniqe = strtoupper(date('YmdHis').uniqid());
    	}
    	
    	$query = Manager::on($connection)->builder();

    	$data  = (array) $this;

    	$query->table($table)
    		->where($uniqe, $this->{$uniqe})
    		->update($data);

    	unset($query);

    }

    public function delete() {

    	$class 		= get_called_class();

    	$uniqe 		= 'id';
    	$table 		= strtolower(basename($class));
    	$connection = 'default';

    	try {
    		$uniqe		 	= $class::UNIQE;
    	}catch(\Error $e) {}

    	try {
    		$table  		= $class::TABLE;
    	}catch(\Error $e) {}

    	try {
    		$connection  	= $class::CONNECTION;
    	}catch(\Error $e) {}

    	if(!isset($this->$uniqe)) {
    		$this->$uniqe = strtoupper(date('YmdHis').uniqid());
    	}
    	
    	$query = Manager::on($connection)->builder();

    	$data  = (array) $this;

    	$query->table($table)
    		->where($uniqe, $this->{$uniqe})
    		->delete();

    	unset($query);

    }

    public static function all() {

    	$class 		= get_called_class();

    	$uniqe 		= 'id';
    	$table 		= strtolower(basename($class));
    	$connection = 'default';

    	try {
    		$uniqe		 	= $class::UNIQE;
    	}catch(\Error $e) {}

    	try {
    		$table  		= $class::TABLE;
    	}catch(\Error $e) {}

    	try {
    		$connection  	= $class::CONNECTION;
    	}catch(\Error $e) {}

    	$query 		= Manager::on($connection)->builder();

    	$query->table($table)->select('*');

    	$records 	= $query->get();

    	foreach ($records as $key => $value) {
    		$records[$key] = new $class($value);
    	}

    	unset($query);

    	return $records;

    }

    public static function __callStatic($name, $arguments) {

    	$class 		= get_called_class();

    	$uniqe 		= 'id';
    	$table 		= strtolower(basename($class));
    	$connection = 'default';

    	try {
    		$uniqe		 	= $class::UNIQE;
    	}catch(\Error $e) {}

    	try {
    		$table  		= $class::TABLE;
    	}catch(\Error $e) {}

    	try {
    		$connection  	= $class::CONNECTION;
    	}catch(\Error $e) {}

    	$query 		= Manager::on($connection)->builder();

    	$query->table($table);

    	$query->select('*');

		array_unshift($arguments, $query);

		$method 	= 'scope'.ucfirst($name);

		if(method_exists($class, $method)) {

			forward_static_call_array(array($class,$method) , $arguments);

			return $query;

		}

		return NULL;

    }

}