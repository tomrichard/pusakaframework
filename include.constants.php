<?php 
define('BENCHMARK_START', 	microtime(true));
define('BENCHMARK_MEMORY', 	memory_get_peak_usage());
// version of pusaka framework
define('PUSAKA_VERSION', 	'2.1.1');
// set root directory
define('ROOTDIR', 		strtr(__DIR__, ['\\' => '/']) . '/' );
// set application directory
define('APPDIR', 		ROOTDIR . 'app/');
// set components
define('COMPONENTS', 	ROOTDIR . 'app/web/components/components.php');
// set storage directory
define('STORAGEDIR', 	ROOTDIR . 'storage/');
// set media directory
define('MEDIADIR', 		ROOTDIR . 'storage/media/');
// set log directory
define('LOGDIR', 		ROOTDIR . 'storage/logs/');
// set cache directory
define('CACHEDIR', 		ROOTDIR . 'storage/caches/');

// non cli constants
//---------------------------------------------------------------
if( php_sapi_name() !== 'cli' ) :

// set webapps constant
define('USEPORT',  		$_SERVER['SERVER_PORT'] == 80 ? '': ':'.$_SERVER['SERVER_PORT'] );
define('DOMAIN',  		(($_SERVER['SERVER_NAME'] == '::1') ? 'localhost' : $_SERVER['SERVER_NAME']) );
define('BASEURL', 		(isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . DOMAIN . USEPORT . (!file_exists('.htaccess') ? $_SERVER['SCRIPT_NAME'] . '/' : strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']) ) );
define('URL', 			preg_replace('/index\.php[\/]?/', '', BASEURL));
// set environtment
define('ENVIRONMENT', 	((DOMAIN === 'localhost') ? 'DEVELOPMENT' : 'PRODUCTION'));

endif;