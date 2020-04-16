<?php 
namespace Pusaka\Library;

use Pusaka\Database\Manager;
use Pusaka\Http\Request;

use closure;

class Datatable {

	private $string_query;
	private $debug;

	private $db;
	private $limit;
	private $table;
	private $select;
	private $filter;
	private $total;
	private $attribute;
	private $uniqe;
	private $search;

	private $page;

	function __construct() {

		$this->limit 	= 10;
		$this->page 	= 1;
		$this->uniqe 	= strtoupper('COUNT' . uniqid());
		$this->db 		= 'default';
		$this->search 	= NULL;

	}

	function on($database) {

		$this->db 		= $database;

	}

	function setLimit($limit = 10) {

		if( $limit < 0 ) { 
			$limit = 1;
		}

		$this->limit 	= $limit;

	}

	function table($query) {

		$this->table 	= $query;

	}

	function select($query) {

		$this->select 	= $query; 

	}

	function total($query) {

		$this->total 	= $query;

	}

	function filter($query) {

		$this->filter 	= $query;

	}

	function debugQuery() {

		$this->debug = true;

	}

	private function __search( $query, $key, $search ) {

		if( is_string($key) ) {

			// advance

			$key = $this->select[$key];

			$query->where(
				$query->funct('UPPER( <<sub>> )', $key), 
					' LIKE ', 
						'%'.strtoupper($search).'%'
			);

		}else if( is_array($key) ) {

			// globals

			if(isset($this->search)) {
				
				$query->where(function($query) use ($key, $search) {

					foreach ($key as $attr) {

						$attr = $this->select[$attr];

						$query->orWhere(
							$query->funct('UPPER( <<sub>> )', $attr), 
								' LIKE ', 
									'%'.strtoupper($search).'%'
						);

					}

				});

			}

		}

	}

	private function __filter( $query ) {

		// limit page
		//---------------------------------
		$this->page  	= (int) (Request::get('_page') ?? $this->page);

		$this->limit 	= (int) (Request::get('_limit') ?? $this->limit);

		$this->search 	= (string) (Request::get('_search') ?? NULL);

		$start 		 	= ( $this->page - 1 );

		$start 		 	= (int) ( ( ($start < 0) ? 0 : $start ) * $this->limit );

		$query->limit($start, $this->limit);

		$get 		 	= Request::get();

		// advance search
		//------------------------------------------------------
		foreach ( $get as $key => $search ) {
			
			if( in_array(trim($key), ['_page', '_limit', '_search', '_order']) ) {
				continue;
			}

			if( in_array($key, array_keys($this->select)) ) {
				$this->__search( $query, $key, $search );
			}

		}
		// global search
		//------------------------------------------------------

		if(!empty($this->search)) {

			$this->__search( $query, array_keys($this->select), $this->search );
		
		}


	}

	private function __count() {

		$query 	= Manager::on($this->db)->builder();

		$uniqe 	= $this->uniqe;

		$total 	= $this->total;

		$table 	= $this->table;

		$table($query);

		$total($query, $uniqe);

		$filter = $this->filter;

		if($filter instanceof closure) {
			
			$filter($query);

		}

		$row 	= $query->first();

		$count 	= $row->$uniqe ?? 0;

		if($count > 0) {
			$pages 		= $count / $this->limit;
		}else {
			$pages 		= 0;
		}

		$this->pages 	= (int) $pages;

		unset($query);

		return $count;

	}

	private function __record() {
		
		$query 	= Manager::on($this->db)->builder();

		$table 	= $this->table;

		$table($query);

		foreach ( $this->select as $as => $q ) {

			// $query->select(function($query) use ($as, $q) {
			// 	$query->alias($as, $q)
			// });
			if( $q instanceof closure ) {

				$query->select(
					$query->alias($as, $q)
				);

			}else 

			if( is_string($q) ) {

				$query->select("$q AS $as");

			}

		}

		$filter = $this->filter;

		if($filter instanceof closure) {
			
			$filter($query);

		}

		$this->__filter( $query );

		$row = [];

		if($this->debug) {
			$this->string_query = $query->getQuery();
		}else {
			$row 	= $query->get();
		}

		unset($query);

		return $row;

	}


	function json($override = NULL) {	

		$count 			= $this->__count();
		$record 		= $this->__record();

		if($override instanceof closure) {
			
			foreach ($record as $key => $val) {
				$override( $record[$key] );
			}

		}

		if($this->debug) {
			
			print_r($this->string_query);

		}

		return [
			'_page'			=> $this->page,
			'_pages' 		=> $this->pages,
			'count' 		=> $count,
			'record'		=> $record
		];

	}



}