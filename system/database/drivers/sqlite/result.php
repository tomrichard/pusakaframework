<?php 
namespace Pusaka\Database\Sqlite;

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
		
		$count = 0;

		while($this->result->fetchArray()) {
			$count++;	
		}

		$this->result->finalize();

		return $count;

	}

	public function all() 
	{

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetchArray()) {
			$this->rows[] = (object) $row;
			unset($row);
		}

		$this->result->finalize();

		return $this->rows;

	}

	public function first() 
	{

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetchArray()) {
			$this->rows[] = (object) $row;
			unset($row);
			break;
		}

		$this->result->finalize();

		return isset($this->rows[0]) ? $this->rows[0] : NULL;

	}

	public function last() 
	{

		if(!$this->result) {
			return [];
		}

		while($row = $this->result->fetchArray()) {
			$this->rows[0] = (object) $row;
			unset($row);
		};

		$this->result->finalize();

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