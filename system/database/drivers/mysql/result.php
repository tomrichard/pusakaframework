<?php 
namespace Pusaka\Database\Mysql;

use Pusaka\Database\Factory\ResultInterface;

class Result implements ResultInterface {

	private $rows;
	private $result;

	public function __construct($result) 
	{
		
		$this->result 	= $result;
		$this->rows 	= [];

	}

	public function count() 
	{

		if(!$this->result) {
			return 0;
		}

		return $this->result->num_rows;

	}

	public function all() 
	{

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

	public function first() 
	{

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetch_object()) {
			$this->rows[] = $row;
			unset($row);
			break;
		}

		$this->result->free();

		return isset($this->rows[0]) ? $this->rows[0] : NULL;

	}

	public function last() 
	{

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetch_object()) {
			$this->rows[0] = $row;
			unset($row);
		};

		return $this->rows[0] ?: NULL;		

	}

	public function close()
	{
		unset($this->rows);
		//unset($this->result);
	}

	public function __desctruct() 
	{

		$this->close();

	}

}