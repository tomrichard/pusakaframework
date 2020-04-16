<?php 
namespace Pusaka\Database\MongoDb;

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

		if(is_null($this->result)) {
			return 0;
		}

		return $this->result->count();

	}

	public function convert_value( $value ) 
	{

		if( $value instanceof \MongoDB\BSON\UTCDateTime ) {

			$datetime 	= $value->toDateTime();
			$time 		= $datetime->format('Y-m-d H:i:s');

			return $time;

		}

		return $value;

	}

	public function all() 
	{

		if(is_null($this->result)) {
			return [];
		}

		foreach($this->result as $document) {
		    
		    $document = (object) ((array) $document);

		    if(isset($document->_id)) {
		    	$document->_id = (string) $document->_id;
		    }

		   	foreach ($document as $key => $value) {
		   		$document->{$key} = $this->convert_value($value);
		   	}

		    $this->rows[] = $document;

		    unset($document);

		}

		return $this->rows;

	}

	public function first() 
	{

		if(is_null($this->result)) {
			return [];
		}

		foreach($this->result as $document) {
		    
		    $document = (object) ((array) $document);

		    if(isset($document->_id)) {
		    	$document->_id = (string) $document->_id;
		    }

		    $this->rows[] = $document;

		    unset($document);

		    break;

		}

		return isset($this->rows[0]) ? $this->rows[0] : NULL;

	}

	public function last() 
	{

		if(is_null($this->result)) {
			return [];
		}

		foreach($this->result as $document) {
		    
		    $document = (object) ((array) $document);

		    if(isset($document->_id)) {
		    	$document->_id = (string) $document->_id;
		    }

		    $this->rows[0] = $document;

		    unset($document);

		    break;

		}

		return isset($this->rows[0]) ? $this->rows[0] : NULL;	

	}

	public function close()
	{
		unset($this->rows);
	}

	public function __desctruct() 
	{

		$this->close();

	}

}