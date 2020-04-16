<?php 
namespace Pusaka\Database\Postgre;

use Pusaka\Database\DatabaseException;

class Driver {

	private $db 			= NULL;
	
	private $config 		= [];

	private $chache_name 	= NULL;

	private $is_cache 		= FALSE;

	private $last_query 	= NULL;

	private $error 			= NULL;

	function __construct($config = []) {

		//mysqli_report(MYSQLI_REPORT_STRICT);

		$this->config = $config;

	}

	function cache( $name ) {

		$this->cache_name 	= $name;
		$this->is_cache 	= TRUE;

	}

	function builder() {
		return new Builder($this);
	}

	function open() {

		if($this->db !== NULL) {
			return $this;
		}

		try {

			$this->db = pg_connect(
				$this->config['hostname'] ?? '127.0.0.1', 
				$this->config['username'] ?? 'root', 
				$this->config['password'] ?? '', 
				$this->config['database'] ?? '',
				$this->config['port'] ?? '3306'
			);

		}catch(mysqli_sql_exception $e) {
			throw new DatabaseException($e->getMessage(), DatabaseException::CONNECTION_ERROR);
		}

	}

	function close() {

		if($this->db !== NULL) {
			$this->db->close();
		}

	}

	function quote($param) {

		if(is_string($param)) {

			if(preg_match('/^[1-9]\d*$/', $param) > 0) {
				return $param;
			} 

			return "'".addslashes($param)."'";
		}

		if(is_numeric($param)) {
			return $param;
		}

		return "'".addslashes($param)."'";
	}

	function transaction() {
		$this->db->begin_transaction();
	}

	function commit() {
		return $this->db->commit();
	}

	function rollback() {
		return $this->db->rollback();
	}

	function result() {
		return $this->db->use_result();
	}

	function count($query) {

		$result = $this->db->query($query);

		if(!$result) {

			$this->error = $this->db->error;

			throw new DatabaseException($this->error(), DatabaseException::QUERY_ERROR);
		
		}

		$count = $result->num_rows;

		$result->close();

		return $count;

	}

	function affected() {

		return $this->db->affected_rows;

	}

	function execute($query) {

		$result = $this->db->real_query($query);

		if(!$result) {

			$this->error = $this->db->error;

			throw new DatabaseException($this->error() . "\r\n\r\n" . $query, DatabaseException::QUERY_ERROR);
		
		}

		return $result; 
	}

	function query($query) {

		$result = $this->db->query($query, MYSQLI_USE_RESULT);

		if(!$result) {
			
			$this->error = $this->db->error;

			throw new DatabaseException($this->error() . "\r\n\r\n" . $query, DatabaseException::QUERY_ERROR);
		
		}

		return new Result($result);

	}

	function error() {

		$error = '';

		if(is_string($this->error)) {
			
			return $this->error;

		}

		if(is_array($this->error)) {

			foreach ($this->error as $value) {
			
				$error = $value . "\r\n\r\n";

			}

			return $error;

		}

		return $this->error;

	}

	function __destruct() {

		$this->close();
		unset($this->config);

	}

}