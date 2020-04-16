<?php 
if(!function_exists('url')) {
	function url($additional = '') {
		return BASEURL . $additional;
	}
}

if(!function_exists('str_replace_first')) {
	function str_replace_first($from, $to, $content) {
		$from = '/'.preg_quote($from, '/').'/';

		return preg_replace($from, $to, $content, 1);
	}
}

if(!function_exists('is_assoc')) {
	function is_assoc(array $arr)
	{
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
}

if(!function_exists('config')) {
	function config($keys)
	{
		return $GLOBALS['config'][$keys] ?? NULL;
	}
}

if(!function_exists('path')) {
	function path($path) {
		return rtrim($path, '/') . '/';
	}
}

if(!function_exists('writelog')) {
	function writelog($log) {

		$saveto 	= path(LOGDIR) . date('YmdHis') . uniqid() . '.log';

		$backtrace 	= debug_backtrace();

		array_shift($backtrace);

		$backtrace 	= json_encode($backtrace, JSON_PRETTY_PRINT);

		$log 		= is_array($log) ? json_encode($log, JSON_PRETTY_PRINT) : $log;

		@file_put_contents($saveto, $log . "\r\n\r\n" . $backtrace);
	
	}
}

if(!function_exists('is_development')) {
	function is_development() {
		return ENVIRONMENT === 'DEVELOPMENT';
	}
}

if(!function_exists('is_production')) {
	function is_production() {
		return ENVIRONMENT === 'PRODUCTION';
	}
}

if(!function_exists('composer')) {
	function composer() {
		
		if(!defined(ROOTDIR)) return;

		include_once(ROOTDIR . 'composer/vendor/autoload.php');
	
	}
}