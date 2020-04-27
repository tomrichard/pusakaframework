<?php 
namespace Pusaka\Microservices;

use Pusaka\Core\Loader;
use Pusaka\Http\Response;
use ReflectionClass;
use ReflectionMethod;
use closure;

class Router {

	public static function middleware( $middleware, $method, $env ) {

		if( method_exists( $middleware, $method ) ) {

			$rm_mid 	= new ReflectionMethod($middleware, $method);

			$arg_value 	= [];

			foreach($rm_mid->getParameters() AS $arg) {

				$arg_class = $arg->getClass();

				if($arg_class !== NULL) {
					$arg_class = $arg_class->getName();
				}

				switch ($arg_class) {
					
					case 'Pusaka\\Core\\Loader' :
							$arg_value[] 	= &$env['loader'];
						break;

				}

			}

			$rm_mid->invokeArgs($middleware, $arg_value);

		}

	}

	public static function handle( $app ) {

		$mid_dir 		= ROOTDIR . 'app/middleware/';

		$micro 			= ROOTDIR . 'app/microservice/';
		
		$path_info 	 	= $_SERVER['PATH_INFO'] ?? '/';

		$path_info 		= trim($path_info, '/');

		$segments 		= explode('/', $path_info);

		$current 		= $micro;

		$file 			= NULL;

		$add 			= NULL;

		$found 			= FALSE;

		while( !empty($segments) ) {

			$add 		= $segments[0];

			$add 		= strtolower($add);

			$file 		= $current . trim($add, '/') . '.php';

			if( file_exists( $file ) ) {
				$found = TRUE;
				break;
			}

			$current 	= $current . $add . '/';

			array_shift($segments);

			unset($add);
			unset($file);

		}

		if( !$found ) {

			unset($add);
			unset($current);
			return;

		}

		array_shift($segments);

		$url 	= implode('/', $segments);

		unset($add);
		unset($current);

		include($file);

		unset($file);

		$found 	= FALSE; 

		foreach ($app->route as $route => $funct) {

			if ( preg_match($route, $url, $match) > 0 ) {

				$found = TRUE;
				array_shift($match);
				break;

			}

			unset($route);
			unset($match);
			unset($funct);

		}

		if( $found ) {

			$var = $match;

			$env = [
				'loader' => new Loader([])
			];
			
			$out = NULL;

			try {

				unset($micro);
				unset($path_info);
				unset($app);
				unset($segments);

				if(is_array($funct['action'])) {

					$mids 		= $funct['action']['middleware'] ?? [];

					if(is_string($mids)) {
						$mids = [$mids];
					}

					foreach ($mids as $mid) {
								
						$mid_file 	= $mid_dir . $mid . '.mw.php';

						if(!file_exists($mid_file)) {

							$e = [
								'error' => 'Middleware not found.',
								'url'	=> (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
								'file'	=> $mid_file
							];

							writelog($e);

							Response::json([
								'error' => '500 File middleware not found.'
							]);

							Response::http(500);

						}

						require_once($mid_file);

						$mid_class 		= ucfirst(basename($mid)) . 'Middleware';

						if(!class_exists($mid_class)) {
							echo "Error 04 - Middleware class not found.";
							exit(1);
						}

						$middleware[] 	= new $mid_class;

						unset($mid);
						unset($mid_file);

					}

					if(isset($middleware)) {

						foreach ($middleware as $mid) {
							self::middleware( $mid, 'begin', $env );
							unset($mid);
						}

					}

					$controller = new Controller($env);

					$response 	= $controller->__getClosure( $funct['action'][0], $var );

					unset($middleware);
					unset($funct);
					unset($controller);
				
					Response::out( $response );

				}else {

					$controller = new Controller($env);

					$response 	= $controller->__getClosure( $funct['action'], $var );

					unset($funct);
					unset($controller);
					
					Response::out( $response );

				}

			}catch(\Error $e) {

				writelog($e);

				Response::http(500);

			}catch(\Exception $e) {

				writelog($e);

				Response::http(500);

			}

			unset($env);
			unset($out);

		}

	}

	// public static function handle( $class ) {

	// 	if(!class_exists($class)) {
			
	// 		http_response_code(404);
	// 		die("Page not found. (404)");

	// 	}

	// 	//---------------------------------------------------------
	// 	// create instance
	// 	//---------------------------------------------------------
	// 	$path_info 	 	= $_SERVER['PATH_INFO'] ?? '/';

	// 	$path_info 		= str_replace_first('/', '', $path_info, 1);

	// 	$segments 		= explode('/', $path_info);

	// 	if( $segments[0] === "" ) {
			
	// 		http_response_code(404);
	// 		die("Page not found - S. (404)");

	// 		return;

	// 	}

	// 	$method = $segments[0];

	// 	if( !method_exists($class, $method) ) {
			
	// 		http_response_code(404);
	// 		die("Page not found - M. (404)");

	// 		return;
		
	// 	}
		
	// 	array_shift($segments);

	// 	//---------------------------------------------------------
	// 	// set environtment
	// 	//---------------------------------------------------------
	// 	$reflector 		= new \ReflectionClass($class);

	// 	$env 			= [
	// 		'segments' 	=> $segments,
	// 		'directory'	=> path(dirname($reflector->getFileName()))
	// 	];

	// 	$env['loader'] 	= new Loader($env);

	// 	//---------------------------------------------------------
	// 	// get anotation class
	// 	//---------------------------------------------------------
	// 	$rc 	= new ReflectionClass($class);
	// 	$doc 	= $rc->getDocComment();
	// 	//---------------------------------------------------------

	// 	//---------------------------------------------------------
	// 	// get anotation method
	// 	//---------------------------------------------------------
	// 	$doc_ov = $rc->getMethod($method)->getDocComment();
	// 	unset($rc);
	// 	//---------------------------------------------------------

	// 	//---------------------------------------------------------
	// 	// middleware
	// 	//---------------------------------------------------------
	// 	$preg 		= preg_match('/@middleware\s(.+)/', $doc, $match);

	// 	$mid_dir 	= ROOTDIR . 'app/middleware/';

	// 	$middleware = NULL;

	// 	if(count($match) > 0) {

	// 		$middleware = trim($match[1]);

	// 	}

	// 	$preg 		= preg_match('/@middleware\s(.+)/', $doc_ov, $match);

	// 	if(count($match) > 0) {

	// 		$middleware = trim($match[1]);

	// 	}

	// 	if( $middleware !== NULL ) {

	// 		$mid_file 	= $mid_dir . $middleware . '.mw.php';
			
	// 		if(!file_exists($mid_file)) {
	// 			echo "Error 03 - Middleware file not found.";
	// 			return;
	// 		}

	// 		include( $mid_file );

	// 		$mid_class 	= ucfirst(basename($middleware)) . 'Middleware';

	// 		if(!class_exists($mid_class)) {
	// 			echo "Error 04 - Middleware class not found.";
	// 			return;
	// 		}

	// 		$middleware = new $mid_class;

	// 	}

	// 	Response::header()->compile([$doc, $doc_ov]);

	// 	//---------------------------------------------------------
	// 	// middleware::begin()
	// 	//---------------------------------------------------------
	// 	if( $middleware !== NULL ) {

	// 		self::middleware( $middleware, 'begin', $env );

	// 	}

	// 	//---------------------------------------------------------
	// 	// create instance
	// 	//---------------------------------------------------------
	// 	$controller 	= new $class($env);
		
	// 	$rm 			= new ReflectionMethod($controller, $method);

	// 	$output 		= $rm->invokeArgs($controller, $segments);

	// 	Response::out( $output );

	// 	//---------------------------------------------------------
	// 	// middleware::end()
	// 	//---------------------------------------------------------
	// 	if( $middleware !== NULL ) {

	// 		self::middleware( $middleware, 'end', $env );

	// 	}

	// }

	// public static function microservice( $app ) {

	// 	$env 				= [];
	// 	$var 				= [];

	// 	$__mid_dir 			= ROOTDIR . 'app/middleware/';

	// 	$__root 			= ROOTDIR . 'app/microservice';

	// 	$__path_info 		= $_SERVER['PATH_INFO'] ?? '/';

	// 	$__segments 		= explode('/', $__path_info);

	// 	array_shift($__segments);

	// 	if( $__segments[0] === '' ) {
	// 		return;
	// 	}

	// 	$__control 			= NULL;

	// 	$__current 			= $__root;

	// 	$__dump 			= [];

	// 	while( !empty($__segments) ) {

	// 		$__add 			= $__segments[0];

	// 		$__add 			= strtolower($__add);

	// 		if( file_exists( $__file = $__current . '/' . $__add . '.php' ) ) {

	// 			$__control = $__file;
	// 			break;

	// 		}

	// 		$__dump[] 		= $__add;

	// 		$__current 	    = $__current . '/' . $__add;

	// 		array_shift($__segments);

	// 	}

	// 	$__dump 			= '/' . implode('/', $__dump);

	// 	if( $__dump !== '/' ) {
	// 		$__dump 		= strtr($__dump, ['/' => '\/']);
	// 		$__path_info 	= preg_replace('/^'.$__dump.'/', '', $__path_info);
	// 	}

	// 	$__path_info 		= str_replace_first('/', '', $__path_info);

	// 	$__path_info 		= explode('/', $__path_info);

	// 	array_shift($__path_info);

	// 	$__path_info 		= implode('/', $__path_info);

	// 	if( $__control !== NULL ) {

	// 		include( $__control );

	// 		foreach ( $app->route as $route => $val ) {

	// 			$route 		= preg_replace('/\{\w+\}/', '(\w+)', strtr($route, ['/' => '\/']));

	// 			$pattern 	= '/^'.$route.'\/?$/';

	// 			if( preg_match($pattern, $__path_info, $match) > 0 ) {

	// 				array_shift($match);

	// 				$var 			= $match;

	// 				$funct 			= $val['action'] ?? function(){};

	// 				// start call controller
	// 				$env 			= [];

	// 				$env['loader'] 	= new Loader($env);

	// 				$middleware = [];

	// 				if( is_array($funct) ) {

	// 					$mids 		= $funct['middleware'] ?? [];

	// 					if(is_string($mids)) {
	// 						$mids 	= [$mids];
	// 					}

	// 					if(!empty($mids)) {

	// 						foreach ($mids as $mid) {
								
	// 							$__mid_file 	= $__mid_dir . $mid . '.mw.php';

	// 							if(file_exists($__mid_file)) {

	// 								require_once($__mid_file);

	// 								$__mid_class 		= ucfirst(basename($mid)) . 'Middleware';

	// 								if(!class_exists($__mid_class)) {
	// 									echo "Error 04 - Middleware class not found.";
	// 									exit(1);
	// 								}

	// 								$middleware[] 	= $__mid_class;

	// 							}

	// 						}

	// 					}

	// 					$funct 	= $funct[0];

	// 				}

	// 				if( $funct instanceof closure) {

	// 					// middleware start

	// 					foreach ($middleware as $mid) {

	// 						self::middleware( new $mid, 'begin', $env );
						
	// 					}

	// 					// go to
						
	// 					$controller = new Controller($env);

	// 					$response 	= $controller->__getClosure( $funct, $var );

	// 					Response::out( $response );
					
	// 					// middleware end

	// 					foreach ($middleware as $mid) {

	// 						self::middleware( new $mid, 'end', $env );
						
	// 					}

	// 				}

	// 				return;
				
	// 			}

	// 			// release memory
	// 			unset($app->route[$route]);

	// 		}

	// 	}

	// 	Response::json([
	// 		'error' => '( 404 ) Page not found!'
	// 	]);

	// 	Response::http(404);

	// 	return;

	// }

}