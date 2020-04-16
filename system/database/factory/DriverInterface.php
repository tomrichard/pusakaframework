<?php 
namespace Pusaka\Database\Factory;

interface DriverInterface {

	public function __construct($config);
    public function builder();
    public function factory();
    public function open();
    public function close();
    public function execute($query);
    public function query($query);
    public function transaction();
    public function rollback();
    public function commit();
    public function error();
    public function __destruct();

}