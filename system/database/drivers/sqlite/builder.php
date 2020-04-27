<?php 
namespace Pusaka\Database\Sqlite;

use closure;

use mysqli;
use mysqli_sql_exception;

use Pusaka\Database\Factory\BuilderInterface;

use Pusaka\Database\Blueprint\Column;
use Pusaka\Database\Blueprint\Value;

use Pusaka\Database\Exceptions\DatabaseException;
use Pusaka\Database\Exceptions\ConnectionException;
use Pusaka\Database\Exceptions\SqlException;

class Builder implements BuilderInterface {

	private $driver;

	private $state 		= NULL;
	private $limit 		= NULL;
	private $having 	= NULL;

	private $selects 	= [];
	private $tables 	= [];
	private $wheres 	= [];
	private $orders 	= [];
	private $groups 	= [];
	private $joins 		= [];
	private $unions 	= [];
	private $sets 		= [];

	public function __construct($driver) 
	{
		
		$this->driver = $driver;

	}

	public function __set($key, $value) 
	{
		
		if(in_array($key, ['state'])) {
			$this->{$key} = $value;
		}

	}

	public function transaction()
	{
		$this->driver->open();
		$this->driver->transaction();
	}

	public function commit()
	{
		$this->driver->commit();
	}

	public function rollback()
	{
		$this->driver->rollback();
	}

	public function select() 
	{
		$argc = func_num_args();

		if ($argc == 1) 
		{

			$arg = func_get_arg(0);

			if (is_string($arg)) 
			{
				$this->selects[] = $arg;
			}

			else if (is_array($arg)) 
			{
				foreach ($arg as $param) {
					$this->select($param);
				}
			}

			else if ($arg instanceof closure) 
			{
				$this->selects[] = $this->subQuery($arg);
			}

			else if ($arg instanceof Value) 
			{
				$this->selects[] = $arg->get();
			}

			return $this;

		}

		else if ($argc > 1)
		{

			foreach (func_get_args() as $param) {
				$this->select($param);
			}

		}

		return $this;

	}

	public function table($alias, $closure = NULL) 
	{

		// simple table
		//---------------------------------------
		if ($closure === NULL) 
		{

			if( is_string($alias) ) {

				if(preg_match('/(\w+)(\(.*\))/', $alias, $match) > 0) {
					$alias = $this->driver->capsulate($match[1]) . $match[2];
				}else {
					$alias = $this->driver->capsulate($alias);
				};
				
				$this->tables[] = $alias;

			}

			return $this;

		}

		// temporary table
		//---------------------------------------
		if( !is_string($alias) ) {
			throw new \Exception('Builder::table() first argument must be string.');
		}

		if( !($closure instanceof closure) ) {
			throw new \Exception('Builder::table() second argument must be string.');
		}

		$this->tables[] = $this->subQuery($closure) . ' ' . $alias;

		return $this;
	
	}

	public function where()
	{

		$argc = func_num_args();

		if ($argc == 1) 
		{

			$arg = func_get_arg(0);

			// raw where
			if (is_string($arg)) {
				
				if(!empty($this->wheres)) {
					$this->wheres[] = ' AND ' . $arg;
				}else {
					$this->wheres[] = $arg;
				}

			}
			else if ($arg instanceof closure) {

				if(!empty($this->wheres)) {
					$this->wheres[] = ' AND ' . $this->subQuery($arg, 'where');
				}else {
					$this->wheres[] = $this->subQuery($arg, 'where');
				}

			}

		}

		else if($argc == 2)
		{

			$column = func_get_arg(0);
			$value 	= func_get_arg(1);

			if($column instanceof Column) {
				$column = $value->name;
			}else if ($column instanceof closure) {
				$column = $this->subQuery($column);
			}

			if($value instanceof Column) {
				$value = $value->name;
			}else if ($value instanceof closure) {
				$value = $this->subQuery($value);
			}else {
				$value = $this->value($value);
			}

			if(!empty($this->wheres)) {
				$this->wheres[] = ' AND ' . $column . '=' . $value;
			}else {
				$this->wheres[] = $column . '=' . $value;
			}

		}

		else if($argc == 3)
		{
			
			$column 	= func_get_arg(0);
			$operator 	= func_get_arg(1);
			$value 		= func_get_arg(2);

			if($column instanceof Column) {
				$column = $value->name;
			}else if ($column instanceof closure) {
				$column = $this->subQuery($column);
			}

			if($value instanceof Column) {
				$value = $value->name;
			}else if ($value instanceof closure) {
				$value = $this->subQuery($value);
			}else {
				$value = $this->value($value);
			}

			if(!empty($this->wheres)) {
				$this->wheres[] = ' AND ' . $column . $operator . $value;
			} else {
				$this->wheres[] = $column . $operator . $value;
			}

		}

		return $this;

	}

	public function whereNot($column, $value) 
	{
		
		$this->where($column, ' NOT ', $value);

		return $this;
	}
	
	public function whereIn($column, $values)
	{
		
		$this->where($column, ' IN ', $values);

		return $this;
	
	}
	
	public function whereNotIn($column, $values)
	{

		$this->where($column, ' NOT IN ', $values);

		return $this;

	}

	public function whereBetween($column, $between)
	{

		if(!is_array($between)) {
			throw new \Exception("Builder::whereBetween($column, $between) | $between must be an array");
		}

		$this->where($column . ' NOT BETWEEN ' . implode(' AND ', $between));

		return $this;

	}

	public function whereNotBetween($column, $between)
	{

		if(!is_array($between)) {
			throw new \Exception("Builder::whereNotBetween($column, $between) | $between must be an array");
		}

		$this->where($column . ' NOT BETWEEN ' . implode(' AND ', $between));

		return $this;

	}

	public function whereNull($column)
	{

		$this->where($column . ' IS NULL ');

		return $this;

	}

	public function whereNotNull($column)
	{

		$this->where($column . ' IS NOT NULL ');

		return $this;

	}

	public function orWhere()
	{

		$argc = func_num_args();

		if ($argc == 1) 
		{

			$arg = func_get_arg(0);

			// raw where
			if (is_string($arg)) {
				
				if(!empty($this->wheres)) {
					$this->wheres[] = ' OR ' . $arg;
				}else {
					$this->wheres[] = $arg;
				}

			}
			else if ($arg instanceof closure) {

				if(!empty($this->wheres)) {
					$this->wheres[] = ' OR ' . $this->subQuery($arg, 'where');
				}else {
					$this->wheres[] = $this->subQuery($arg, 'where');
				}

			}

		}

		else if($argc == 2)
		{

			$column = func_get_arg(0);
			$value 	= func_get_arg(1);

			if($value instanceof Column) {
				$value = $value->name;
			}else {
				$value = $this->value($value);
			}

			if(!empty($this->wheres)) {
				$this->wheres[] = ' OR ' . $column . '=' . $value;
			}else {
				$this->wheres[] = $column . '=' . $value;
			}

		}

		else if($argc == 3)
		{
			
			$column 	= func_get_arg(0);
			$operator 	= func_get_arg(1);
			$value 		= func_get_arg(2);

			if($column instanceof closure) {
				$column = $this->subQuery($column);
			}

			if($value instanceof Column) {
				$value = $value->name;
			}else {
				$value = $this->value($value);
			}

			if(!empty($this->wheres)) {
				$this->wheres[] = ' OR ' . $column . $operator . $value;
			}else {
				$this->wheres[] = $column . $operator . $value;
			}

		}

		return $this;

	}
	
	public function orWhereNot($column, $value)
	{

		$this->orWhere($column, ' NOT ', $value);

		return $this;

	}
	
	public function orWhereIn($column, $values)
	{

		$this->orWhere($column, ' NOT ', $values);

		return $this;

	}

	public function orWhereNotIn($column, $values)
	{
		
		$this->orWhere($column, ' NOT IN ', $values);

		return $this;

	}

	public function orWhereBetween($column, $between)
	{

		if(!is_array($between)) {
			throw new \Exception("Builder::orWhereBetween($column, $between) | $between must be an array");
		}

		$this->orWhere($column . ' NOT BETWEEN ' . implode(' AND ', $between) );

		return $this;

	}

	public function orWhereNotBetween($column, $between)
	{

		if(!is_array($between)) {
			throw new \Exception("Builder::orWhereNotBetween($column, $between) | $between must be an array");
		}

		$this->orWhere($column . ' NOT BETWEEN ' . implode(' AND ', $between) );

		return $this;

	}
	
	public function orWhereNull($column)
	{

		$this->orWhere($column . ' IS NULL ');

		return $this;

	}

	public function orWhereNotNull($column)
	{

		$this->orWhere($column . ' IS NOT NULL ');

		return $this;

	}

	/**
	 * group, order & limit
	 */
	public function orderBy($column, $order) {

		$this->orders[] = $column . ' ' . strtoupper($order);

		return $this;

	}

	public function groupBy() {

		$args = func_get_args();

		$this->groups[] = implode(',', $args);

		return $this;

	}

	public function limit($start, $length) {

		if(!is_int($start)) {
			throw new \Exception("Builder::limit($start, $length) | $start must be an Integer");
		}

		if(!is_int($length)) {
			throw new \Exception("Builder::limit($start, $length) | $length must be an Integer");
		}

		$this->limit = "$start, $length";

		return $this;

	}

	public function having()
	{

		$argc = func_num_args();

		if ($argc == 1) 
		{

			$arg = func_get_arg(0);

			// raw having
			if (is_string($arg)) {
				
				$this->having = $arg;

			}

		}

		return $this;

	}

	public function join() 
	{

		$prefix 	= '';
		$command 	= '';
		$argc 		= func_num_args();

		if ($argc == 2) 
		{

			//JOIN table2 ON table1.a = table2.b

			$table = func_get_arg(0);
			$join  = func_get_arg(1);
			$on    = '';

			if(is_string($table)) {
				$command = $prefix . 'JOIN '.$table. ' ';
			}

			if($join instanceof closure) {

				$builder = new Builder($this->driver);

				$join($builder);

				$command .= $builder->getQuery();

			}

			$this->joins[] = $command;

		}

		return $this;

	}

	public function joinLeft() {

		$prefix 	= 'LEFT ';
		$command 	= '';
		$argc 		= func_num_args();

		if ($argc == 2) 
		{

			//JOIN table2 ON table1.a = table2.b

			$table = func_get_arg(0);
			$join  = func_get_arg(1);
			$on    = '';

			if(is_string($table)) {
				$command = $prefix . 'JOIN '.$table. ' ';
			}

			if($join instanceof closure) {

				$builder = new Builder($this->driver);

				$join($builder);

				$command .= $builder->getQuery();

			}

			$this->joins[] = $command;

		}

		return $this;

	}
	
	public function joinRight() {

		$prefix 	= 'RIGHT ';
		$command 	= '';
		$argc 		= func_num_args();

		if ($argc == 2) 
		{

			//JOIN table2 ON table1.a = table2.b

			$table = func_get_arg(0);
			$join  = func_get_arg(1);
			$on    = '';

			if(is_string($table)) {
				$command = $prefix . 'JOIN '.$table. ' ';
			}

			if($join instanceof closure) {

				$builder = new Builder($this->driver);

				$join($builder);

				$command .= $builder->getQuery();

			}

			$this->joins[] = $command;

		}

		return $this;

	}
	
	public function joinFull() {

		$prefix 	= '';
		$command 	= '';
		$argc 		= func_num_args();

		if ($argc == 1) 
		{

			//JOIN table2 ON table1.a = table2.b

			$table = func_get_arg(0);
			
			$this->tables[] = $table;

		}

		return $this;

	}

	public function on($col1, $operator, $col2) 
	{

		if(empty($this->joins)) {
			$this->joins[] = ' ON ' . $col1 . ' ' . $operator . ' ' . $col2;
		}else {
			$this->joins[] = ' AND ' . $col1 . ' ' . $operator . ' ' . $col2;
		}

		return $this;

	}

	public function orOn($col1, $operator, $col2) 
	{

		if(empty($this->joins)) {
			$this->joins[] = ' ON ' . $col1 . ' ' . $operator . ' ' . $col2;
		}else {
			$this->joins[] = ' OR ' . $col1 . ' ' . $operator . ' ' . $col2;
		}

		return $this;

	}

	public function union($query) 
	{

		if(empty($this->unions)) {
			$this->unions[] = $this->subQuery($query);
		}else {
			$this->unions[] = ' UNION ' . $this->subQuery($query);
		}

		return $this;

	}

	public function unionAll($query) 
	{

		if(empty($this->unions)) {
			$this->unions[] = $this->subQuery($query);
		}else {
			$this->unions[] = ' UNION ALL ' . $this->subQuery($query);
		}

		return $this;

	}

	public function funct($funct, $sub)
	{

		if($sub instanceof closure) {
			return strtr( $funct, [ '<<sub>>' => $this->subQuery($sub) ] );
		}else {
			return strtr( $funct, [ '<<sub>>' => $sub ] );
		}

		return NULL;

	}

	public function alias($as, $col)
	{

		if(!is_string($as)) 
		{
			throw new \Exception('Builder::alias($as, $col) | $as parameter must be string');
		}

		if(is_string($col)) 
		{
			return $this->value($col) . ' AS ' . $as;
		}

		else if($col instanceof closure) 
		{
			return $this->subQuery($col) . ' AS ' .$as;
		}

		elseif ($col instanceof Column) {
			return $col->name . ' AS ' .$as;
		}

		return NULL;

	}

	public function uuid() 
	{
		return "REPLACE(UPPER(UUID()), '-', '')";
	}

	public function max($col)
	{

		return 'MAX('.$col.')';

	}
	
	public function min($col)
	{

		return 'MIN('.$col.')';

	}
	
	public function avg($col)
	{

		return 'AVG('.$col.')';

	}

	public function sum($col)
	{

		return 'SUM('.$col.')';

	}

	public function count($col)
	{
		
		return 'COUNT('.$col.')';

	}

	public function value($value) 
	{

		if(is_string($value)) {
			return "'". $value . "'";
		}

		if(is_numeric($value)) {
			return $value;
		}

		if(is_array($value)) {

			$in = [];

			foreach ($value as $val) {
				$in[] = $this->value($val);
			}

			return '('.implode(',', $in).')';

		}

		if($value instanceof closure) {

			return $this->subQuery($value);

		}

		if($value instanceof Column) {

			$column = $value->name;
			unset($value);

			return $column;

		}

		return 'null';

	}

	public function subQuery($closure, $state = NULL) 
	{

		$query 			= new Builder($this->driver);
		$query->state 	= $state;

		$closure($query);

		$sub = '( ' . $query->getQuery() . ' )';

		unset($query);

		return $sub;

	}

	/**
	 * 
	 * @method getQuery
	 * @return string
	 * 
	 */
	public function getQuery()
	{

		$unions  = $this->unions;
		$selects = $this->selects;
		$tables  = $this->tables;
		$joins   = $this->joins;
		$wheres  = $this->wheres;
		$groups  = $this->groups;
		$having  = $this->having;
		$orders	 = $this->orders;
		$limit 	 = $this->limit;

		$count_union 	= count($unions);
		$count_select 	= count($selects);
		$count_table 	= count($tables);
		$count_join 	= count($joins);
		$count_where 	= count($wheres); 
		$count_group 	= count($groups);
		$count_order 	= count($orders);

		if($count_select <= 0) {
			$selects[] = '*';
		}

		$union 	 = implode(' ', $unions);
		$select  = implode(',', $selects);
		$table 	 = implode(',', $tables);
		$join 	 = implode(' ', $joins);
		$where 	 = implode(' ', $wheres);
		$order 	 = implode(',', $orders);
		$group 	 = implode(',', $groups);

		$query 	 = '';

		if ($count_select > 0) {
			$query 	.= 'SELECT ' . $select; 
		}

		if ($count_table  > 0) {
			$query .= ' FROM '.$table;
		}

		if ($count_join > 0) {
			$query .= ' ' . $join;
		}

		if ($count_where  > 0) {

			if($this->state == NULL) {
				
				$query .= ' WHERE '. $where;
			
			}else if($this->state == 'where') {
			
				$query .= $where;
			
			}

		}

		if ($count_group > 0) {
			$query .= ' GROUP BY ' . $group;
		}

		if (!is_null($having)) {
			$query .= ' HAVING ' . $having;
		}

		if ($count_order > 0) {
			$query .= ' ORDER BY ' . $order;
		}

		if (!is_null($limit)) {
			$query .= ' LIMIT ' . $limit;
		}

		if ($count_union > 0) {
			$query 	= $union; 
		}

		$this->__clear();

		return $query;

	}

	/**
	 * 
	 * @method get
	 * @return object
	 * 
	 */
	public function get() 
	{

		$query 	= $this->getQuery();

		$this->driver->open();

		$result = $this->driver->query($query);

		return $result->all();

	}

	/**
	 * 
	 * @method first
	 * @return object
	 * 
	 */
	public function first() 
	{

		$query 	= $this->getQuery();

		$this->driver->open();

		$result = $this->driver->query($query);

		return $result->first();

	}

	/**
	 * 
	 * @method insertQuery
	 * @return void
	 * 
	 */
	public function insertQuery($records) {

		$column = [];
		$values = [];
		$table  = $this->tables[0] ?? NULL;
		$query  = 'INSERT INTO ';

		if(is_null($table)) {
			throw new Exception("Builder::insert($record) | table cannot be empty.");
		}

		$query 		.= $table;

		$column_def  = '';
		$values_def  = '';

		if(is_array($records)) {

			// sigle record
			if(array_keys($records) !== range(0, count($records) - 1)) {
				
				// set to multiple records
				$records = [
					$records
				];

			}

			$init 	= true;

			// multiple records
			foreach ($records as $i => $record) {
				
				$value 	= [];
				
				foreach ($record as $col => $val) {
				
					if($init) {
						$column[] = $col;
					}

					$value[]  = $this->value($val);
				}
				
				$values[] 	= '( ' . implode(',', $value) . ' )';
				$init 		= false;
				unset($value);
			}

			$column_def = '( '. implode(',', $column) . ' )';
			$values_def = ' VALUES ' . implode(',', $values);

		}

		else if ($records instanceof closure) {

			$values_def = trim($this->subQuery($records), '()');

		}

		$query .= $column_def;
		$query .= $values_def;

		unset($records);
		unset($table);
		unset($column);
		unset($values);

		$this->__clear();

		return $query;

	}

	public function insert($records) {

		$query = $this->insertQuery($records);

		$this->driver->open();

		$result = $this->driver->execute($query);

		return $result;

	}

	/**
	 * 
	 * @method updateQuery
	 * @return void
	 * 
	 */
	public function updateQuery($records, $on = NULL) {

		// UPDATE table SET d=3, e=5 WHERE id=10

		$column = [];
		$values = [];
		$table  = $this->tables[0] ?? NULL;
		$query  = 'UPDATE ';
		$where 	= '';

		if(is_null($table)) {
			throw new \Exception('Builder::update($record) | table cannot be empty.');
		}

		$query 		.= $table;

		$column_def  = '';
		$values_def  = '';

		if(empty($this->wheres)) {
			throw new \Exception('Builder::update($record) | where clause cannot be empty.');
		}

		$where 		 = implode(' ', $this->wheres);

		if(is_array($records)) {

			// sigle record
			if(array_keys($records) !== range(0, count($records) - 1)) {
				
				$query .= ' SET ';

				foreach ($records as $column => $value) {

					$values[] = $column . '=' . $this->value($value);

				}

				$values_def = implode(',', $values);

			}else {

				if(is_null($on)) {
					throw new \Exception('Builder::updateQuery($records, $on) | $on cannot be NULL');
				}

				if(empty($this->sets)) {
					throw new \Exception('Batch update need to set. need [ Builder::set() ]');	
				}

				$temp_table = 'temp_table_join_update';

				$subQuery 	= new Builder($this->driver);

				foreach ($records as $i => $record) {

					$subQuery->unionAll(function($subQuery) use ($record) {

						foreach ($record as $col => $val) {
						
							$subQuery->select($this->value($val) . ' AS ' . $col);
							
						}

					});

				}

				$query .= 'JOIN ( ' . $subQuery->getQuery() . ')' . $temp_table . ' ON ' . $on;

				unset($subQuery);

				$query .= implode(',', $this->sets);

				$query 	= strtr($query, ['{join}' => $temp_table]);

			}

		}

		else if ($records instanceof closure) {

			$values_def = trim($this->subQuery($records), '()');

		}

		$query .= $column_def;
		$query .= $values_def;
		$query .= ' WHERE '. $where;

		unset($records);
		unset($table);
		unset($column);
		unset($values);

		$this->__clear();

		return $query;

	}

	public function update($records, $on = NULL) {

		$query = $this->updateQuery($records, $on);
		
		$this->driver->open();

		$result = $this->driver->execute($query);

		return $result;

	}

	public function deleteQuery()
	{

		$table  = $this->tables[0] ?? NULL;
		$query  = 'DELETE ';
		$where 	= '';

		if(is_null($table)) {
			throw new \Exception('Builder::update($record) | table cannot be empty.');
		}

		if(empty($this->wheres)) {
			throw new \Exception('Builder::update($record) | where clause cannot be empty.');
		}

		$where 		 = implode(' ', $this->wheres);

		$query 		.= ' FROM '. $table;
		$query 		.= ' WHERE '. $where;

		$this->__clear();

		return $query;

	}

	public function delete()
	{

		$query = $this->deleteQuery();

		$this->driver->open();

		$result = $this->driver->execute($query);

		return $result;

	}

	public function set($column, $value) 
	{

		if(empty($this->sets)) {
			$this->sets[] = ' SET ' . $column . ' = ' . $this->value($value);
		}else {
			$this->sets[] = $column . ' = ' . $this->value($value);
		}

		return $this;

	}

	/**
	 * 
	 * @method __destruct
	 * @return void
	 * 
	 */
	public function __destruct() 
	{

		unset($this->state);
		unset($this->limit);
		unset($this->having);

		unset($this->selects);
		unset($this->tables);
		unset($this->wheres);
		unset($this->orders);
		unset($this->groups);
		unset($this->joins);
		unset($this->unions);

		$this->driver->close();

	}

	/**
	 * 
	 * @method __clear
	 * @return void
	 * 
	 */
	private function __clear() 
	{

		unset($this->state);
		unset($this->limit);
		unset($this->having);

		unset($this->selects);
		unset($this->tables);
		unset($this->wheres);
		unset($this->orders);
		unset($this->groups);
		unset($this->joins);
		unset($this->unions);
		unset($this->sets);

		$this->state 	= NULL;
		$this->limit 	= NULL;
		$this->having 	= NULL;

		$this->selects 	= [];
		$this->tables 	= [];
		$this->wheres 	= [];
		$this->orders 	= [];
		$this->groups 	= [];
		$this->joins 	= [];
		$this->unions 	= [];
		$this->sets 	= [];

	}

}