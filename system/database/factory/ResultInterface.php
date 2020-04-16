<?php 
namespace Pusaka\Database\Factory;

interface ResultInterface {

	public function __construct($result);
	public function count();
	public function all();
	public function first();
	public function last();
	public function close();
	public function __desctruct();

}