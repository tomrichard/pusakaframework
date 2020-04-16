<?php 
namespace Pusaka\Memory;

class Session {

	static function hashkey() {

		$security = config('security')['key'];

		return md5($security);

	}

	static function start() {
		
		session_start();

	}

	static function save($data) {

		if(!is_array($data)) {
			return false;
		}

		$security = self::hashkey();

		foreach ($data as $key => $value) {
			$_SESSION[$security][$key] = $value; 
		}

		return true;

	}

	static function load() {

		$security = self::hashkey();
	
		if( isset($_SESSION[$security]) ) {
			return $_SESSION[$security];
		}

		return NULL;

	}

	static function destroy() {

		session_destroy();

	}

}
