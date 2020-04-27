<?php 
namespace Pusaka\Database\Sqlite;

use closure;

use mysqli;
use mysqli_sql_exception;

class Factory {

	private $driver;

	private $columns 	= [];

	public function __construct($driver) 
	{
		
		$this->driver = $driver;

	}

	function create( $table, $closure ) 
	{

		$closure($this);

		$columns = implode(",\r\n", $this->columns);

		$sql  	= 'CREATE TABLE ' . $table . ' ';

		$sql   .= "(\r\n" . $columns . "\r\n);";

		echo '<pre>';

		echo $sql;

		$this->driver->open();

		$this->driver->execute($sql);

		$this->driver->close();

	}

	function drop( $table ) 
	{

		$sql = 'DROP TABLE ' . $table . ';';

		$this->driver->open();

		$this->driver->execute($sql);

		$this->driver->close();

	}

	function string( $name, $length = 255, $null = true, $comment = '' ) {

		$this->columns[] = $name . ' ' . "varchar($length)" . ' ' . ($null ? 'NULL' : 'NOT NULL');

	}
	
}