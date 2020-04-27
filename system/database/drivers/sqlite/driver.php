<?php 
namespace Pusaka\Database\Sqlite;

use SQLite3;
use Exception;

use Pusaka\Database\Factory\DriverInterface;

use Pusaka\Database\Exceptions\DatabaseException;
use Pusaka\Database\Exceptions\ConnectionException;
use Pusaka\Database\Exceptions\SqlException;

class Driver implements DriverInterface {

	private $db;

	private $config 		= [];

	private $error 			= NULL;

	function __construct($config = []) {

		$this->config = $config;

	}

	function builder() {
		return new Builder($this);
	}

	function factory() {
		return new Factory($this);
	}

	function open() {

		if(isset($this->db)) {
			return $this;
		}

		$this->db = new SQLite3($this->config['database']);
		
		if(!$this->db) {
      		throw new ConnectionException($this->db->lastErrorMsg());
		}
		
		$this->db->enableExceptions(true);

	}

	function close() {

		if(isset($this->db)) {
			if($this->db !== NULL) {
				$this->db->close();
				unset($this->db);
			}
		}

	}

	function transaction() {
		$this->db->begin_transaction();
	}

	function commit() {
		$this->db->commit();
	}

	function rollback() {
		$this->db->rollback();
	}

	function query($query) {

		try {

			$result = $this->db->query($query);

		} catch (Exception $e) {

			$this->error = $this->db->lastErrorMsg();

			throw new SqlException($e->getMessage() . "\r\n\r\n" . $query);

		}

		return new Result($result);

	}

	function execute($query) {

		try {

			$result = $this->db->exec($query);

		} catch (Exception $e) {

			$this->error = $this->db->lastErrorMsg();

			throw new SqlException($e->getMessage() . "\r\n\r\n" . $query);

		}

		return $result;

	}

	function capsulate($string) {

		return '`'.$string.'`';

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
		unset($this->error);

	}

}