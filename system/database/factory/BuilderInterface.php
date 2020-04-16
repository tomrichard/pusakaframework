<?php 
namespace Pusaka\Database\Factory;

interface BuilderInterface {

	public function select();
	public function table($alias, $closure);

	public function where();
	public function whereNot($column, $value);
	public function whereIn($column, $values);
	public function whereNotIn($column, $values);
	public function whereBetween($column, $between);
	public function whereNotBetween($column, $between);
	public function whereNull($column);
	public function whereNotNull($column);

	public function orWhere();
	public function orWhereNot($column, $value);
	public function orWhereIn($column, $values);
	public function orWhereNotIn($column, $values);
	public function orWhereBetween($column, $between);
	public function orWhereNotBetween($column, $between);
	public function orWhereNull($column);
	public function orWhereNotNull($column);

	public function orderBy($column, $order);
	public function groupBy();
	public function limit($start, $length);
	public function having();

	public function alias($as, $col);

	public function max($col); 		// aggregate
	public function min($col); 		// aggregate
	public function avg($col); 		// aggregate
	public function sum($col); 		// aggregate
	public function count($col); 	// aggregate

	public function value($value);
	public function subQuery($closure, $state);

	public function join();
	public function joinLeft();
	public function joinRight();
	public function joinFull();

	public function on($col1, $operator, $col2);
	public function orOn($col1, $operator, $col2);

	public function union($query);
	public function unionAll($query);

	public function funct($funct, $sub);

	public function set($column, $value);

	public function insertQuery($records);
	public function insert($records);

	public function updateQuery($records, $on);
	public function update($records, $on);

	public function deleteQuery();
	public function delete();	

	public function __destruct();

}