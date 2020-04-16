<?php 
namespace Pusaka\Core;

class Injection {
	
	static $vars;

	public static function add($key, $value) {

		if(!isset(self::$vars[$key])) {
			self::$vars[$key] = [];
		}

		self::$vars[$key][] = $value;

	}

	public static function set($key, $value) {

		self::$vars[$key] = $value;

	}

	public static function get($key) {

		return self::$vars[$key] ?? NULL;

	}

}