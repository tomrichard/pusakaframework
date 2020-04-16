<?php 
namespace Pusaka\Database\Sqlite;

use SQLite3;
use Pusaka\Database\Exception;

class Driver {

	private $db 			= NULL;
	
	private $config 		= [];

	private $chache_name 	= NULL;

	private $is_cache 		= FALSE;

	private $last_query 	= NULL;

	private $error 			= NULL;

	function __construct($config = []) {

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

			$this->db = new SQLite3(
				$this->config['database'] ?? ''
			);

			//$this->db->enableExceptions(true);

		}catch(\Exception $e) {
			throw new Exception($e->getMessage(), Exception::CONNECTION_ERROR);
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

			throw new Exception($this->error(), Exception::QUERY_ERROR);
		
		}

		$count = $result->num_rows;

		$result->close();

		return $count;

	}

	function affected() {

		return $this->db->affected_rows;

	}

	function execute($query) {

		$result = $this->db->query($query);

		if(!$result) {

			$this->error = $this->db->error;

			throw new Exception($this->error() . "\r\n\r\n" . $query, Exception::QUERY_ERROR);
		
		}

		return $result; 
	}

	function query($query) {

		$result = $this->db->query($query);

		if(!$result) {
			
			$this->error = $this->db->error;

			throw new Exception($this->error() . "\r\n\r\n" . $query, Exception::QUERY_ERROR);
		
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