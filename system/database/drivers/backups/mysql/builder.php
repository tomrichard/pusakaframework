<?php 
namespace Pusaka\Database\Mysql;

use closure;
use is_assoc;

class Builder {

	private $file 		= NULL;

	private $cache 		= NULL;

	private $ch_name 	= NULL;

	private $is_cache 	= NULL;

	private $on_funct 	= NULL;

	private $driver 	= NULL;

	private $distinct 	= FALSE;

	private $use_table 	= NULL; 

	private $table 		= NULL;

	private $select 	= NULL;

	private $where 		= NULL;

	private $union 		= NULL;

	private $into 		= NULL;

	private $map 		= NULL;

	private $last_query = NULL;

	private $group_by 	= NULL;

	private $order_by 	= NULL;

	private $having 	= NULL;

	private $limit 		= NULL;

	private $conns 		= [];

	public function __construct($driver) {
		$this->driver = $driver;
	}

	/*
	|============================================================
	| ON ANOTHER CONNECTION
	|============================================================
	*/
	public function on($connection) {

		$conns = \Pusaka\Webapps\System\Database::on($connection);

		$conns = $conns->builder();

		$this->conns[] = $conns;

		return $conns;

	}

	public function transaction() {

		$this->driver->open();

		$this->driver->transaction();

		return $this;

	}

	public function commit() {

		$this->driver->commit();

		return $this;		

	}

	public function rollback() {

		$this->driver->rollback();

		return $this;		

	}

	/*
	|============================================================
	| OUT OF BOX METHOD
	|============================================================
	*/	
	public function cache($name) {

		$this->ch_name 	= '_list'. md5($name);
		$this->is_cache = TRUE;

		$saveas 		= ROOTDIR . 'storage/caches/queries/' . $this->ch_name;
		
		if(!file_exists($saveas)) {
			@file_put_contents($saveas, json_encode([]));
		}

		unset($backtrace);
		unset($link);
		unset($model);
		unset($method);
		unset($args);
		unset($hashKey);

	}

	/*
	|============================================================
	| PRIVATE METHOD
	|============================================================
	*/

	// build prefix | table didepan field
	private function __prefixTable() {
		return ($this->use_table !== NULL) ? $this->use_table . '.' : '';
	}

	// build quote | build kutip
	private function __buildQuotes($string) {

		if(is_string($string)) {

			// check if number format
			if(preg_match('/^\'[1-9]\d*\'$/', $string) > 0) {
				return $string;
			}

			if(preg_match('/^[1-9]\d*$/', $string) > 0) {
				return $string;
			}

			return "'".addslashes($string)."'";
		}

		if(is_numeric($string)) {

			return ''.$string;

		}

		if($string === NULL) {

			return 'null';

		}

		return '';

	}

	private function __buildSelectorColumn($string, $auto = TRUE) {

		if(!$auto) {
			
			$string = $this->__buildQuotes($string);			

			return $string;

		}

		$string = trim($string);

		$string = preg_replace('/^(\w+)\.[`]?(\w+)[`]?$/', '$1.`$2`', $string);

		if(preg_match('/^\w+$/', $string, $match) > 0) {
			// column
			$string = $this->__prefixTable() . '`' . $string . '`';
		}

		return $string;

	}

	private function __useFunction( $column, $funct, array $args = [] ) {

		$params = '';

		foreach ($args as $key => $value) {
			$params .= $this->__buildSelectorColumn($value, FALSE) . ',';
		}

		if($params !== '') {
			$params = ',' . rtrim($params, ',');
		}

		if(is_string($column) OR is_numeric($column)) {
		
			$funct = $funct . '(' . $this->__buildSelectorColumn($column) . $params . ')';

			$this->on_funct = NULL;

			unset($args);
			unset($column);
			
			return $funct;
		
		}

		if($column instanceof closure) {
			
			$query = new Builder($this->driver);
			$column($query);
			$funct = $funct . '(' . $query->getQuery() . $params . ')';

			if($this->on_funct === 'where') {
				$this->where[] = $funct;	
			}else {
				$this->select[] = $funct;
			}

			$this->on_funct = NULL;

			unset($args);
			unset($query);
			unset($column);
			unset($funct);
	
			return $funct;

		}

		return '';

	}

	private function __buildUnion() {

		$union = '';

		if( is_array($this->union) ) {

			$union .= implode(' ', $this->union);

		}

		return $union;

	}

	private function __buildTable() {

		$table = '';

		if( is_array($this->table) ) {

			$table .= implode(' ', $this->table);

		}

		return $table;

	}

	private function __buildWhere() {

		$where  = '';

		if( $this->where !== NULL ) {

			$where = 'WHERE ';
			
			foreach ($this->where as $key => $value) {
				
				$where .= ' ' . $value;
				
				if(isset($this->where[$key + 1])) {
					$next = $this->where[$key + 1];
					if(!in_array($next, ['AND', 'OR', ')']) AND !in_array($value, ['IN', 'AND', 'OR', '('])) {
						$where .= ' '. 'AND';
					}
				}

				unset($value);

			}

		}

		return $where;

	}

	private function __buildGroup() {

		$group = '';

		if( $this->group_by !== NULL ) {

			$group = 'GROUP BY ';

			foreach ($this->group_by as $value) {
				
				$group .= ' ' . $value . ',';

			}

			$group = rtrim($group, ',');

		}

		return $group;

	}

	private function __buildHaving() {

		$having = '';

		if( $this->having !== NULL ) {

			$having = 'HAVING ';

			foreach ($this->having as $value) {
				
				$having .= ' ' . $value . 'AND';

			}

			$having = rtrim($having, 'AND');

		}

		return $having;

	}

	private function __buildOrder() {

		$order = '';

		if( $this->order_by !== NULL ) {

			$order = 'ORDER BY ';

			foreach ($this->order_by as $value) {
				
				$order .= ' ' . $value . ',';

			}

			$order = rtrim($order, ',');

		}

		return $order;

	}

	private function __subQuery($sub_query) {

		if($sub_query instanceof closure) {

			$query 			= new Builder($this->driver);
			$sub_query($query);
			$sub_query  	= '(' . rtrim($query->getQuery(), ' ;') . ')';

			unset($query);
			unset($table);

			return $sub_query;

		}

		return NULL;

	}


	/*
	|============================================================
	| PUBLIC METHOD
	|============================================================
	*/

	public function select() {

		$attributes = func_get_args();

		// if( $this->is_cache ) return $this;

		foreach ($attributes as $attribute) {

			if(is_string($attribute)) {
				$this->select[] = '' . $attribute;
			}
			
			if($attribute instanceof closure) {
			
				$attribute($this);

			}

			if(is_array($attribute)) {

				foreach ($attribute as $child) {
					$this->select($child);
				}

			}

			unset($attribute);

		}

		return $this;
	}

	public function distinct() {

		$this->distinct = TRUE;

	}

	public function map($column) {

		$this->map[] = $column;

	}

	public function alias($alias, $column, $quote = FALSE) {

		// if( $this->is_cache ) return $this;

		if(is_string($column) OR is_numeric($column)) {
		
			$column = $quote ? $this->driver->quote($column) : $column;

			$this->select[] = $column . ' AS ' . $alias;
			
			return $this;
		
		}

		if($column instanceof closure) {
			
			$this->select[] = $this->__subQuery($column) . ' AS ' . $alias;

			return $this;

		}

		return $this;

	}

	public function min( $column ) {

		// if( $this->is_cache ) return $this;

		return $this->__useFunction( $column, 'MIN' );

	}

	public function max( $column ) {

		// if( $this->is_cache ) return $this;

		return $this->__useFunction( $column, 'MAX' );

	}

	public function count( $column ) {

		// if( $this->is_cache ) return $this;

		return $this->__useFunction( $column, 'COUNT' );

	}

	public function avg( $column ) {

		// if( $this->is_cache ) return $this;

		return $this->__useFunction( $column, 'AVG' );

	}

	public function sum( $column ) {

		// if( $this->is_cache ) return $this;

		return $this->__useFunction( $column, 'SUM' );

	}

	public function from( $table ) {

		// if( $this->is_cache ) return $this;

		if(is_string($table)) {

			$table = trim($table);

			$match = [];

			if(preg_match('/^\w+$/', $table, $match)){
				$this->use_table 	= $table;
			}else if(preg_match('/^\w+\s(\w+)$/', $table, $match)) {
				$this->use_table 	= $match[1] ?: NULL;
			}

			$this->table[] 		= $table;

			unset($match);

		}

		if($table instanceof closure) {
			$this->table[] = $this->__subQuery($table);
		}

		return $this;
	}

	public function tableAlias($alias, $table) {

		if(is_string($table)) {

			$this->table[] = '(' . $table . ')' . $alias;

		}

		if($table instanceof closure) {
			$this->table[] = '(' . $this->__subQuery($table) . ')' . $alias;
		}

		return $this;

	}

	public function join( $table , $id_one, $id_two, $as = '') {

		// if( $this->is_cache ) return $this;

		$temp_table = NULL;

		if($table instanceof closure) {
			$temp_table    = $this->__subQuery($table);
		}

		$this->table[] = 'JOIN ' . ($temp_table ?: $table) . ' ' . $as . ' ON ' . $id_one . '=' .$id_two;

		unset($temp_table);

		return $this;
	}

	public function joinLeft( $table , $id_one, $id_two, $as = '') {

		// if( $this->is_cache ) return $this;
		
		$temp_table = NULL;

		if($table instanceof closure) {
			$temp_table    = $this->__subQuery($table);
		}

		$this->table[] = 'LEFT JOIN ' . $table . ' ' . $as . ' ON ' . $id_one . '=' .$id_two;

		unset($temp_table);

		return $this;
	}

	public function joinRight( $table , $id_one, $id_two, $as) {

		// if( $this->is_cache ) return $this;

		$temp_table = NULL;

		if($table instanceof closure) {
			$temp_table    = $this->__subQuery($table);
		}

		$this->table[] = 'RIGHT JOIN ' . $table . ' ' . $as . ' ON ' . $id_one . '=' .$id_two;

		unset($temp_table);

		return $this;
	}

	public function joinOuter( $table , $as = '') {

		// if( $this->is_cache ) return $this;

		$temp_table = NULL;

		if($table instanceof closure) {
			$temp_table    = $this->__subQuery($table);
		}

		$this->table[] = 'CROSS JOIN ' . $table . ' ' . $as;

		unset($temp_table);

		return $this;
	}

	public function union($union) {

		if($union instanceof closure) {
			$this->union[] = ' UNION ' . $this->__subQuery($union);
			return $this;
		}

		if(is_string($union)) {
			$this->union[] = $union;
		}

		return $this;

	}

	public function unionAll($union) {

		if($union instanceof closure) {
			$this->union[] = ' UNION ALL ' . $this->__subQuery($union);
			return $this;
		}

		if(is_string($union)) {
			$this->union[] = $union;
		}

		return $this;

	}

	public function unionDistinct($union) {

		if($union instanceof closure) {
			$this->union[] = ' UNION DISTINCT ' . $this->__subQuery($union);
			return $this;
		}

		if(is_string($union)) {
			$this->union[] = $union;
		}

		return $this;

	}

	public function where() {

		$param 	= func_get_args(); 

		// if( $this->is_cache ) return $this;
		
		$count 	= count($param);

		if($this->where == NULL) {
			$this->where = [];
		}

		if( $count == 1 ) {

			if(is_string($param[0]) OR is_numeric($param[0])) {
			
				$this->where[] = $param[0];
			
			}else if($param[0] instanceof closure) {
			
				$this->where[] = '(';
				$param[0]($this);
				$this->where[] = ')';
				unset($param[0]);
			
			}

		}else
		if( $count == 2 ) {
			
			$column 		= $param[0]; 
			
			if($column instanceof closure) {
				$column = $this->__subQuery($column);
			}

			$this->where[] 	= $this->__buildSelectorColumn($column) . (' = ') . $this->driver->quote($param[1]);
		
		}else 
		if( $count == 3 ) {
			
			$column 		= $param[0];

			if($column instanceof closure) {
				$column = $this->__subQuery($column);
			}

			$this->where[] 	= $this->__buildSelectorColumn($column) . (' '.$param[1].' ') . $this->driver->quote($param[2]);
		
		}
		
		unset($count);
		unset($param);

		return $this;

	}

	public function whereOr() {

		$param 	= func_get_args();

		// if( $this->is_cache ) return $this;
		
		$prefix = trim(implode('', $this->where));

		// edit on november 13/2019
		$count  = preg_match('/\($/', $prefix);

		if($prefix !== '(' AND $prefix !== '' AND $count <= 0) {
			$this->where[] = 'OR';
		}

		call_user_func_array([$this, 'where'], $param);

		unset($param);

		return $this;

	}

	public function whereNotIn( $id, $array ) {

		// if( $this->is_cache ) return $this;
		
		return $this->whereIn($id, $array, ' NOT ');

	}

	public function whereIn( $column, $array, $add = '' ) {

		// if( $this->is_cache ) return $this;

		$prefix = $this->__prefixTable();//($this->use_table !== NULL) ? $this->use_table . '.' : '';

		if( is_array($array) ) {

			foreach ($array as $key => $value) {
				$array[$key] = $this->driver->quote($value);
				unset($value);
			}

			$this->where[] = $this->__buildSelectorColumn($column) . ' IN (' . implode(',', $array) . ')';

			unset($array);

			return $this;

		}

		if( $array instanceof closure ) {

			$this->where[] = $this->__buildSelectorColumn($column) . $add . ' IN ' . $this->__subQuery($array);

			unset($array);

			return $this;

		}

		return $this;

	}

	public function whereNotBetween( $column, array $array ) {

		// if( $this->is_cache ) return $this;
		
		return $this->whereBetween($column, $array, ' NOT ');

	}

	public function whereBetween( $column, array $array, $add = '') {

		// if( $this->is_cache ) return $this;

		if( is_array($array) ) {

			$array 	= array_slice($array, 0, 2);

			$prefix = $this->__prefixTable();

			foreach ($array as $key => $value) {
				$array[$key] = $this->driver->quote($value);
				unset($value);
			}

			$this->where[] = '(' . $this->__buildSelectorColumn($column) . $add . ' BETWEEN '. implode('AND', $array) . ')';

			unset($array);

			return $this;

		}

		return $this;

	}

	public function whereNotNull( $column ) {

		// if( $this->is_cache ) return $this;

		return $this->whereNull( $column, 'NOT' );

	}

	public function whereNull( $column, $add = '') {

		// if( $this->is_cache ) return $this;

		if( is_string($column) ) {

			$this->where[] = '(' . $this->__buildSelectorColumn($column) . ' IS ' . $add . ' NULL ' . ')';

			unset($column);

		}

		return $this;

	}

	public function whereNotExists( $select ) {

		// if( $this->is_cache ) return $this;

		return $this->whereExists( $select, 'NOT' );

	}

	public function whereExists( $select, $add = '') {

		// if( $this->is_cache ) return $this;

		if( $select instanceof closure ) {

			$this->where[] = $add . 'EXISTS' . $this->__subQuery($select);

			unset($select);

		}

		return $this;

	}

	/*
	|
	|============================================================ */
	public function uuid() {
		return "REPLACE(UPPER(UUID()), '-', '')";
	}

	public function column($column) {
		return $this->__buildSelectorColumn($column);
	}

	/*
	| DATE FUNCTION
	|============================================================ */
	public function date() {

		$args 	= func_get_args();

		$column = $args[0];

		array_unshift($args);

		// if( $this->is_cache ) return $this;

		return $this->__useFunction($column, 'DATE', $args);

	}

	public function whereDate($column, $equal) {

		// if( $this->is_cache ) return $this;

		$this->on_funct = 'where';

		$this->where[] 	= 'DATE('.$this->__buildSelectorColumn($column).') = ' . "'".(is_string($equal) ? $equal: '')."'";

		return $this;

	}

	public function whereDay($column, $equal) {

		// if( $this->is_cache ) return $this;

		$this->on_funct = 'where';

		$equal = $this->driver->quote($equal);

		$this->where[] 	= 'DAY('.$this->__buildSelectorColumn($column).') = ' . $equal;

		return $this;

	}

	public function whereMonth($column, $equal) {

		// if( $this->is_cache ) return $this;

		$this->on_funct = 'where';

		$equal = $this->driver->quote($equal);

		$this->where[] 	= 'MONTH('.$this->__buildSelectorColumn($column).') = ' . $equal;

		return $this;

	}

	public function whereYear($column, $equal) {

		// if( $this->is_cache ) return $this;

		$this->on_funct = 'where';

		$equal = $this->driver->quote($equal);

		$this->where[] 	= 'YEAR('.$this->__buildSelectorColumn($column).') = ' . $equal;

		return $this;

	}

	public function clear() {

		unset($this->on_funct);
		unset($this->distinct);
		unset($this->use_table);
		unset($this->table);
		unset($this->select);
		unset($this->where);
		unset($this->order_by);
		unset($this->limit);

		$this->on_funct 	= FALSE;
		$this->distinct 	= FALSE;
		$this->use_table 	= NULL;
		$this->table 		= NULL;
		$this->select 		= NULL;
		$this->where 		= NULL;
		$this->order_by 	= NULL;
		$this->limit 		= NULL;

	}

	public function lastExecuteQuery() {

		return $this->last_query;

	}

	public function limit($count, $offset = 0) {

		$this->limit = "LIMIT $offset, $count";

		return $this;

	}

	public function orderBy($column, $sort = 'ASC') {

		$this->order_by[] = $this->__buildSelectorColumn($column) . ' ' . strtoupper($sort);

		return $this;

	}

	public function groupBy($column) {

		$this->group_by[] = $this->__buildSelectorColumn($column);

		return $this;

	}

	public function having($raw) {

		$this->having[] = $raw;

		return $this;

	}

	public function havingNot($raw) {
		return $this->having( 'NOT ' . $raw );
	}

	public function get() {

		$query = $this->getQuery();

		$this->last_query = $query;

		if($this->is_cache) {

			$checksum = md5($query);
			
			if(isset($_COOKIE['$hash'.$checksum])) {

				if(file_exists($fname = ROOTDIR . 'storage/caches/queries/' . '_hash'. $checksum)) {
					
					$contents 	= @file_get_contents(strtr($fname, ['_hash' => '']));
					$md5content = @file_get_contents($fname);
					$compare 	= $_COOKIE['$hash'.$checksum];

					if($md5content === $compare) {
						return json_decode($contents);
					}

				}

			}

		}

		$this->driver->open();

		$result = $this->driver->query($query);

		$values = $result->all();

		if($this->is_cache) {

			$listName 		= $this->ch_name; 
			$content 		= json_encode($values);
			$md5content 	= md5($content);

			$saveas 		= ROOTDIR . 'storage/caches/queries/' . $listName;

			$listQuery 		= json_decode(@file_get_contents($saveas), true);

			$listQuery[] 	= $md5content;
			$listQuery 		= array_unique($listQuery);

			@file_put_contents($saveas, $listQuery);

			$saveas 	= ROOTDIR . 'storage/caches/queries/' . '_hash'. $checksum;
			@file_put_contents($saveas, $md5content);

			// query
			$saveas 	= ROOTDIR . 'storage/caches/queries/' . '_query'. $checksum;
			@file_put_contents($saveas, $content);
			
			// content
			$saveas 	= ROOTDIR . 'storage/caches/queries/' . $checksum;
			@file_put_contents($saveas, $content);

			setcookie('$hash'.$checksum, $md5content, time() + (60 * 60 * 24 * 30));

			unset($content);
		
		}

		unset($result);

		return $values;

	}

	public function getQuery() {

		$query 	= '';

		$select = 'SELECT ' . ($this->distinct ? ' DISTINCT ' : '') ;

		$from 	= 'FROM';

		$table 	= $this->__buildTable();

		$where 	= $this->__buildWhere();

		$union 	= $this->__buildUnion();

		$order 	= $this->__buildOrder();

		$group 	= $this->__buildGroup();

		$having = $this->__buildHaving();

		if($this->select 	=== NULL) {
			$select = '';
		}

		if($this->table 	=== NULL) {
			$from = '';
		}

		if( is_array($this->select) ) {

			foreach ($this->select as $key => $value) {

				$value = $this->select[$key] ?: NULL;

				if($value === NULL) {
					unset($value);
					continue;
				}			

				if(isset($this->select[$key + 1])) {
					
					$next = $this->select[$key + 1];
					
					if(preg_match('/\{\[\:query_as\:\]\}/', $next) > 0) {
						$select .= strtr($next, ['{[:query_as:]}' => $value]);
						$this->select[$key + 1] = NULL;
						array_shift($this->select);
					}else {
						$select .= $value;
					}

					$select .= ',';

				}else {
					
					$select .= $value;
				
				}

				unset($value);

			}

		}

		$select = rtrim($select, ',');

		$limit 	= $this->limit ?: '';

		$query  = implode(' ', [$select, $from, $table, $where, $union, $group, $having, $order, $limit, ';']);

		$query 	= preg_replace('/^UNION (ALL)?/i', '', trim($query));

		$this->clear();

		return $query;

	}

	public function first() {

		$query = $this->getQuery();

		$this->last_query = $query;

		$this->driver->open();

		$result = $this->driver->query($query);

		$values = $result->first();

		unset($result);

		return $values;

	} 

	public function last() {

		$query = $this->getQuery();

		$this->last_query = $query;

		$this->driver->open();

		$result = $this->driver->query($query);

		$values = $result->last();

		unset($result);

		return $values;

	} 

	public function getCount() {

		$query = $this->getQuery();

		$this->last_query = $query;

		$this->driver->open();

		return $this->driver->count($query);

	}

	function into($table) {

		$this->into = $table;

		return $this;		

	}

	function insert(array $values = NULL) {

		$this->driver->open();

		$query 	= $this->insertQuery($values);

		$this->driver->execute($query);

		$result = $this->driver->affected();

		return $result;

	}

	function insertQuery($values = NULL) {

		$query = "INSERT INTO";

		if($values === NULL) {

			$columns 	= '(' . implode(',', $this->map) . ')';

			$table 		= $this->into;

			$query 		= implode(' ', [$query, $table, $columns, $this->getQuery()]);

			return $query;
		
		}

		if(is_assoc($values)) {

			// single insert
			$copy 	= [];

			$copy[] = $values;
			
			$values = $copy;

		}

		if(is_assoc($values[0])) {

			$rows 		= '';
			$columns 	= [];

			// bacth insert
			foreach ($values as $i => $value) {
				
				$row = [];

				foreach ($value as $column => $param) {
					
					if($i === 0) {
						// save $key
						$columns[] = $column;
					
					}

					$param = $this->__buildQuotes($param);

					$row[] = $param;

				}

				// build row
				$rows .= '(' . implode(',', $row) . '),';

			}

			$columns 	= '(' . implode(',', $columns) . ')';

			$table 		= $this->into;

			$query 		= implode(' ', [$query, $table, $columns, 'VALUES', rtrim($rows, ','), ';']);

			return $query;

		}

		$this->clear();

		// select insert
		return $query;

	}

	function update(array $values = NULL) {

		$this->driver->open();

		$query 	= $this->updateQuery($values);

		$this->driver->execute($query);

		$result = $this->driver->affected();

		return $result;

	}

	function updateQuery($values = NULL) {

		$query 		= "UPDATE ";

		$conditon 	= '';

		$table 		= $this->into;

		$where 		= $this->__buildWhere();

		if(is_assoc($values)) {

			$rows 	= '';

			foreach ($values as $column => $param) {

				$rows .= $this->__buildSelectorColumn($column) . '=' . $this->__buildQuotes($param) . ',';

			}

			// single update			
			$query 	= implode(' ', [$query, $table, 'SET', rtrim($rows, ','), $where, ';']);

			return$query;

		}

		if(is_assoc($values[0])) {

			$on 		= '';
			$rows 		= '';
			$sets 		= '';

			// bacth update
			
			foreach ($values as $i => $value) {

				$row   = '';
				$first = 0;

				foreach ($value as $column => $param) {

					if($i === 0) {

						$set 	 = $this->__buildSelectorColumn($column);
						$sets 	.= $table . '.' . $set . ' = ' . 'TempTableCreated.'. $set . ', ';
						
						if($first === 0) {
							$on 	= $set;
							$sets 	= '';
							$first++;
						}

					}

					// builld select union
					$param 	 = $this->__buildQuotes($param);

					$row 	.= $param . ' AS ' . $column . ', ';

				}

				$row 	 = implode(' ', [rtrim($row, ', '), 'UNION ALL', '']);
				$rows 	.= implode(' ', ['SELECT', $row]);

			}

			$sets 	= rtrim($sets, ', ');

			// single update			
			$query 	= implode(' ', [$query, $table, 'JOIN', '(', rtrim($rows, 'UNION ALL'), ')', 'TempTableCreated', "ON {$table}.{$on} = TempTableCreated.{$on}", 'SET', $sets, $where, ';']);

			$this->clear();

			return $query;

		}


	}

	function delete() {

		$this->driver->open();

		$query 	= $this->deleteQuery();

		$this->driver->execute($query);

		$result = $this->driver->affected();

		return $result;
	
	}

	function deleteQuery() {

		$query 	= 'DELETE';

		$table 	= $this->into;

		$where 	= $this->__buildWhere();

		$query  = implode(' ', [$query, 'FROM', $table, $where, ';']);

		$this->clear();

		return $query;

	}

	function __distruct() {

		foreach($this->conns as $key => $conn) {
			unset($this->conns[$key]);
		}

		unset($this->conns);
		unset($this->ch_name);
		unset($this->is_cache);
		unset($this->driver);
		unset($this->on_funct);
		unset($this->distinc);
		unset($this->use_table);
		unset($this->table);
		unset($this->select);
		unset($this->where);
		unset($this->order_by);
		unset($this->limit);

	}

}