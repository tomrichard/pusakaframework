<?php 
namespace Pusaka\Database;

class Manager {

	public static function on($config) {

		if (!defined('DB_ROOT_SYSTEM_FOLDER')) {
			throw new \Exception("DB_ROOT_SYSTEM_FOLDER not defined.");
		}

		if (!defined('PUSAKA_DATABASE_CONFIG')) {
			throw new \Exception("PUSAKA_DATABASE_CONFIG not defined.");
		}

		$configs 	= PUSAKA_DATABASE_CONFIG;

		$name 		= $config;

		$config 	= $configs[$name] ?? NULL;

		if ($config 	== NULL) {
			throw new \Exception("Configuration database[$name] not found");
			return NULL;
		}

		if ($config['driver'] == 'mysql') {
			return new \Pusaka\Database\Mysql\Driver($config);
		}else 
		if ($config['driver'] == 'sqlsrv') {
			return new \Pusaka\Database\Sqlsrv\Driver($config);
		}else 
		if ($config['driver'] == 'sqlite') {
			return new \Pusaka\Database\Sqlite\Driver($config);
		}else 
		if ($config['driver'] == 'postgre') {
			return new \Pusaka\Database\Postgre\Driver($config);
		}else
		if ($config['driver'] == 'oracle') {
			return new \Pusaka\Database\Oracle\Driver($config);
		}else
		if ($config['driver'] == 'mongodb') {
			return new \Pusaka\Database\MongoDb\Driver($config);
		}

		return NULL;

	}

}