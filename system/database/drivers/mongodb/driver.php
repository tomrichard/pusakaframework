<?php 
namespace Pusaka\Database\MongoDb;

use Pusaka\Database\Factory\DriverInterface;

use Pusaka\Database\Exceptions\DatabaseException;
use Pusaka\Database\Exceptions\ConnectionException;
use Pusaka\Database\Exceptions\SqlException;

use MongoDB;

class Driver implements DriverInterface {

	private $db;

	private $config 		= [];

	private $error 			= NULL;

	private $database 		= NULL;

	function __construct($config = []) {

		$this->config = $config;

	}

	function builder() {
		return new Builder($this);
	}

	function factory() {
		return new Factory($this);
	}

	function database() {
		return $this->database;
	}

	function open() {

		if(isset($this->db)) {
			return $this;
		}

		$connection = "mongodb+srv://_user:_pass@_host/test?retryWrites=true&w=majority";

		try {

			$_host = $this->config['hostname'] 	?? '127.0.0.1';
			$_user = $this->config['username'] 	?? '';
			$_pass = $this->config['password'] 	?? '';
			$_db   = $this->config['database'] 	?? '';
			$_port = $this->config['port'] 		?? '27017';

			$connection 	= strtr($connection, compact('_user', '_pass', '_host'));

			$this->db 		= new MongoDB\Client($connection);

			$this->database = $this->db->{$_db};

		}catch(Exceptions $e) {
			throw new ConnectionException($e->getMessage());
		}

	}

	function close() {

		if(isset($this->db)) {
			if($this->db !== NULL) {
				unset($this->db);
			}
		}

	}

	function transaction() {
	}

	function commit() {
	}

	function rollback() {
	}

	function query($query) {

		if(!isset($query['__table'])) {
			return NULL;
		}

		$table 		= $query['__table'];

		unset($query['__table']);

		$collection = $this->database->{$table};

		$param 		= [];

		if(isset($query['$project'])) {
			$param[] = [ 
				'$project' 	=> $query['$project']
			];
		}

		if(isset($query['$match'])) {
			$param[] = [
				'$match' 	=> $query['$match']
			];
		}

		if(isset($query['$skip'])) {
			$param[] = [
				'$skip' 	=> (int) $query['$skip']
			];
		}

		if(isset($query['$limit'])) {
			$param[] = [
				'$limit' 	=> (int) $query['$limit']
			];	
		}

		$result	 	= $collection->aggregate($param);

		return new Result($result);

	}

	function execute($query) {
	}

	function capsulate($string) {

		return '`'.$string.'`';

	}

	function error() {

	}

	function __destruct() {

		$this->close();
		unset($this->config);
		unset($this->error);

	}

}

// class Driver implements DriverInterface {

// 	private $db;

// 	private $config 		= [];

// 	private $error 			= NULL;

// 	function __construct($config = []) {

// 		$this->config = $config;

// 	}

// 	function builder() {
// 		return new Builder($this);
// 	}

// 	function factory() {
// 		return new Factory($this);
// 	}

// 	function open() {

// 		if(isset($this->db)) {
// 			return $this;
// 		}

// 		try {

// 			$this->db = new mysqli(
// 				$this->config['hostname'] ?? '127.0.0.1', 
// 				$this->config['username'] ?? 'root', 
// 				$this->config['password'] ?? '', 
// 				$this->config['database'] ?? '',
// 				$this->config['port'] ?? '3306'
// 			);

// 		}catch(mysqli_sql_exception $e) {
// 			throw new ConnectionException($e->getMessage());
// 		}

// 	}

// 	function close() {

// 		if(isset($this->db)) {
// 			if($this->db !== NULL) {
// 				$this->db->close();
// 				unset($this->db);
// 			}
// 		}

// 	}

// 	function transaction() {
// 		$this->db->begin_transaction();
// 	}

// 	function commit() {
// 		$this->db->commit();
// 	}

// 	function rollback() {
// 		$this->db->rollback();
// 	}

// 	function query($query) {

// 		return new Result($result);

// 	}

// 	function execute($query) {

// 		$result = $this->db->real_query($query);

// 		if(!$result) {

// 			$this->error = $this->db->error;

// 			throw new SqlException($this->error() . "\r\n\r\n" . $query);
		
// 		}

// 		return $result;

// 	}

// 	function capsulate($string) {

// 		return '`'.$string.'`';

// 	}

// 	function error() {
		
// 		$error = '';

// 		if(is_string($this->error)) {
			
// 			return $this->error;

// 		}

// 		if(is_array($this->error)) {

// 			foreach ($this->error as $value) {
			
// 				$error = $value . "\r\n\r\n";

// 			}

// 			return $error;

// 		}

// 		return $this->error;

// 	}

// 	function __destruct() {

// 		$this->close();
// 		unset($this->config);
// 		unset($this->error);

// 	}

// }