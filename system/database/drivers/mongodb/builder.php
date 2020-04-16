<?php 
namespace Pusaka\Database\MongoDb;

use closure;

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

	private $match 		= NULL;

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

	public function select() 
	{
		$argc = func_num_args();

		if ($argc == 1) 
		{
		
			$arg = func_get_arg(0);

			if( is_string($arg) ) {
				$this->selects[] = $arg;
			}

		}else if ($argc > 1)
		{

			foreach (func_get_args() as $param) {
				$this->select($param);
			}

		}

		return $this;

	}

	public function table($alias, $closure = NULL) 
	{

		$this->tables[] = $alias;

		return $this;

	}

	public function match( $query ) {

		$this->match = $query;

		return $this;

	}

	public function where()
	{

		$argc = func_num_args();

		if ($argc == 2) 
		{

			$col = func_get_arg(0);

			$val = func_get_arg(1);

			if( is_string($col) ) {
				
				// value string
				if(is_string($val)) {

					$this->wheres[] = [
						'$and' => [
							$col => $val
						]
					];

				}

			}

		}

		return $this;

	}

	public function whereNot($column, $value) 
	{
		return $this;
	}

	public function whereIn($column, $values)
	{
		return $this;
	}

	public function whereNotIn($column, $values)
	{
		return $this;
	}

	public function whereBetween($column, $between)
	{
		return $this;

	}

	public function whereNotBetween($column, $between)
	{

		return $this;

	}

	public function whereNull($column)
	{

		return $this;

	}

	public function whereNotNull($column)
	{

		return $this;

	}

	public function orWhere()
	{

		return $this;

	}
	
	public function orWhereNot($column, $value)
	{

		return $this;

	}
	
	public function orWhereIn($column, $values)
	{

		return $this;

	}

	public function orWhereNotIn($column, $values)
	{
		
		return $this;

	}

	public function orWhereBetween($column, $between)
	{

		return $this;

	}

	public function orWhereNotBetween($column, $between)
	{

		return $this;

	}
	
	public function orWhereNull($column)
	{

		return $this;

	}

	public function orWhereNotNull($column)
	{

		return $this;

	}

	/**
	 * group, order & limit
	 */
	public function orderBy($column, $order) {

		return $this;

	}

	public function groupBy() {

		return $this;

	}

	public function limit($start, $length) {

		$this->limit = [
			'$skip' 	=> $start,
			'$limit'	=> $length 
		];

		return $this;

	}

	public function having()
	{
		return $this;
	}

	public function join() 
	{

		return $this;

	}

	public function joinLeft() {

		return $this;

	}
	
	public function joinRight() {

		return $this;

	}
	
	public function joinFull() {

		return $this;

	}

	public function on($col1, $operator, $col2) 
	{

		return $this;

	}

	public function orOn($col1, $operator, $col2) 
	{

		return $this;

	}

	public function union($query) 
	{

		return $this;

	}

	public function unionAll($query) 
	{

		return $this;

	}

	public function funct($funct, $sub)
	{

		return NULL;

	}

	public function alias($as, $col)
	{

		return NULL;

	}

	public function max($col)
	{
		return $this;
	}
	
	public function min($col)
	{
		return $this;
	}
	
	public function avg($col)
	{
		return $this;
	}

	public function sum($col)
	{
		return $this;
	}

	public function count($col)
	{
		return $this;
	}

	public function value($value) 
	{

		return 'null';

	}

	public function subQuery($closure, $state = NULL) 
	{

	}

	/**
	 * 
	 * @method getQuery
	 * @return string
	 * 
	 */
	public function getQuery()
	{

		$project 	= [];

		$match 		= [];

		$table 		= NULL;

		$limit 		= NULL;

		$query 		= [];

		if(!empty($this->limit)) {
			$limit 	= $this->limit;			
		}

		if(!empty($this->tables)) {
			$table = $this->tables[0];
		}

		if(!empty($this->selects)) {

			if(in_array('*', $this->selects)) {
			
			}else {

				foreach ($this->selects as $field) {
					if(is_string($field)) {
						$project[$field] = 1;
					}
				}

			}

		}

		// var_dump('aaaa');
		// var_dump($this->wheres);

		if(!empty($this->wheres)) {

			foreach ($this->wheres as $operator => $operand) {
				if(is_string($operator)) {
					$match[$operator] = $operand;
				}
			}


		}

		if(!empty($this->match)) {
			$match = $this->match;
		}

		if(!empty($table)) {
			$query['__table'] 	= $table;
		}

		if(!empty($project)) {
			$query['$project'] 	= $project;
		}

		if(!empty($match)) {
			$query['$match'] 	= $match;
		}

		if(!empty($limit)) {
			$query['$skip'] 	= $limit['$skip'] ?? NULL;
			$query['$limit'] 	= $limit['$limit'] ?? NULL;
		}

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

		$query = $this->getQuery();

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

	}

	/**
	 * 
	 * @method insertQuery
	 * @return void
	 * 
	 */
	public function insertQuery($records) {

		return $query;

	}

	public function insert($records) {

		return $result;

	}

	/**
	 * 
	 * @method updateQuery
	 * @return void
	 * 
	 */
	public function updateQuery($records, $on = NULL) {

		return $query;

	}

	public function update($records, $on = NULL) {

		return $result;

	}

	public function deleteQuery()
	{

		return $query;

	}

	public function delete()
	{

		return $result;

	}

	public function set($column, $value) 
	{

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