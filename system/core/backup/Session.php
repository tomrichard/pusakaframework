<?php 
namespace Pusaka\Memory;

class Session {

	public static function start() {
		return session_start();
	}

	public static function destroy() {
		return session_destroy();
	}

	public static function set($key, $value) {
		$salt 	= '';//$GLOBALS['pf_config']['app']['security_key'];
		$key 	= md5($salt . $key . $salt);
		$_SESSION[$key] = $value;
	}

	public static function get($key) {
		$salt 	= '';//$GLOBALS['pf_config']['app']['security_key'];
		$key 	= md5($salt . $key . $salt);
		if(isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		return NULL;
	}

	public static function id() {
		return session_id();
	}

}