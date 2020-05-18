<?php 
namespace Pusaka\Hmvc;

use Pusaka\Core\Loader;
use Pusaka\Http\Response;
use Pusaka\Http\Request;

use Pusaka\Exceptions\ClassNotFoundException;
use Pusaka\Exceptions\MethodNotFoundException;
use Pusaka\Exceptions\IOException;
use Pusaka\Exceptions\ControllerNotFoundException;

use ReflectionClass;
use ReflectionMethod;

use RuntimeMiddleware;

class Router {

	private $env;
	private $cwdir;
	private $ctrl;
	private $fctrl;
	private $segments;
	private $loader;

	public static function run() {

		$Router = new Router();

		$Router->handle();

	}

	public function middleware( $middleware, $method ) {

		$env = $this->env;

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
							$arg_value[] 	= $env['loader'];
						break;

				}

			}

			$rm_mid->invokeArgs($middleware, $arg_value);

		}

	}

	public function find_controller( $segments ) {



	}

	public function handle() {

		$current_dir 	= [];

		$controller_dir = ROOTDIR . 'app/web/www/';

		$copy_ctrl_dir 	= $controller_dir;

		$path_info 	 	= $_SERVER['PATH_INFO'] ?? '/';

		$routes 	 	= config('routes');

		$def_controller = $routes[':default'];

		if($path_info == '/') {
			
			// default controller
			//------------------------------		
			$path_info 	= $routes[':default'];
		
		}else {

			// search controller
			//------------------------------
			$path_info = str_replace_first('/', '', $path_info, 1);

			foreach($routes as $match => $route) {
		
				$match 		= 
					strtr( $match, [
						'/'		 => '\/',
						'(:any)' => '([\w|\/|\-|\_|\.]*)',
						'(:num)' => '(\d+)'
					]);

				if( preg_match('/^'.$match.'$/', $path_info) > 0 ) {

					// Route Match => break
					//-------------------------------
					$pathinfo = preg_replace('/^'.$match.'$/', $route, $path_info);
					break;

				}

				unset($routes[$match]);

			}

		}

		/* 
		| Check and Found Controller
		|-------------------------------------------- */
		$fragment 			= '';

		$segments 			= explode('/', $path_info);

		$copy_segments 		= $segments;

		$controller 		= NULL;
		$file_controller 	= NULL;

		do {

			$try_controller = $segments[0];

			// check directory with name as controller | /www/welcome
			//--------------------------------------------------------
			if(is_dir($controller_dir . $try_controller)) {

				$file_controller = $controller_dir . $try_controller .'/'. $try_controller . '.cs.php';

				// if file controller found -> break
				//----------------------------------------------------
				if(file_exists($file_controller)) {
					array_shift($segments);
					$this->segments = $segments;
					break;
				}
				
				// if controller found -> break
				//----------------------------------------------------
				else {
					$file_controller = NULL;
					$try_controller  = NULL;
				}

				$current_dir[] = $segments[0];
			
			}
			
			$controller_dir = $controller_dir . $segments[0] . '/';

			$fragment 		= $fragment . $segments[0] . '/';
			
			array_shift($segments);

		}while(!empty($segments));

		unset($routes);

		// try to call controller
		//------------------------------------------
		if( !file_exists($file_controller) ) {

			$try_controller  = basename($def_controller);

			$file_controller = $copy_ctrl_dir . $def_controller . '/'. $try_controller . '.cs.php';

			if( !file_exists($file_controller) ) {
				
				throw new IOException("File not found [$file_controller]", 3568);
				return;
			
			}

			$segments = $copy_segments;

			$fragment = '/';

		}

		include( $file_controller );

		$class = ucfirst($try_controller).'CS';

		if(!class_exists($class)) {
			// catch
			//throw new ClassNotFoundException(0);
			echo "error 02";
			return;
		}

		//---------------------------------------------------------
		// set environtment
		//---------------------------------------------------------
		$env = [
			'segments' 	=> $segments,
			'fragment' 	=> $fragment,
			'directory'	=> path(dirname($file_controller))
		];

		$env['loader'] = new Loader($env);

		$this->env = $env;

		//----------------------------------------------------------
		// search method
		//----------------------------------------------------------
		$method   = 'index';

		if ( !empty($segments) ) 
		{
			
			if ( $segments[0] === $method ) 
			{
				array_shift($segments);	
			}
			else if ( method_exists($class, $segments[0]) ) 
			{
				$method = $segments[0];
				array_shift($segments);
			}

		}

		//---------------------------------------------------------
		// get anotation
		//---------------------------------------------------------
		$rc 	= new ReflectionMethod($class, $method);
		$docm 	= $rc->getDocComment();
		unset($rc);

		//---------------------------------------------------------
		// get anotation
		//---------------------------------------------------------
		$rc 	= new ReflectionClass($class);
		$doc 	= $rc->getDocComment();
		unset($rc);
		//---------------------------------------------------------

		//---------------------------------------------------------
		// runtime::begin()
		//---------------------------------------------------------
		$runtime = NULL;

		if(class_exists('RuntimeMiddleware')) {
			
			$runtime = new RuntimeMiddleware();

			$this->middleware($runtime, 'begin');

		}

		//---------------------------------------------------------
		// middleware
		//---------------------------------------------------------
		$preg 		= preg_match('/@middleware\s(.+)/', $docm, $match);

		if( !($preg > 0) ) {
			$preg 	= preg_match('/@middleware\s(.+)/', $doc, $match);
		}

		$mid_dir 	= ROOTDIR . 'app/middleware/';

		$middleware = NULL;

		if(count($match) > 0) {

			$middleware = trim($match[1]);
			
			if($middleware === '--none') {
				
				$middleware = NULL;

			}else {

				$mid_file 	= $mid_dir . $middleware . '.mw.php';
				
				if(!file_exists($mid_file)) {
					echo "error03";
					return;
				}

				include( $mid_file );

				$mid_class 	= ucfirst(basename($middleware)) . 'Middleware';

				if(!class_exists($mid_class)) {
					echo "error04";
					return;
				}

				$middleware = new $mid_class;

			}

		}

		//---------------------------------------------------------
		// middleware::begin()
		//---------------------------------------------------------
		if( $middleware !== NULL ) {

			$this->middleware( $middleware, 'begin' );

		}

		//---------------------------------------------------------
		// create instance controller
		//---------------------------------------------------------
		$instance = new $class($env);

		if ( !method_exists($instance, $method) ) {
			echo "error05";
			return;
		}

		$rm 	= new ReflectionMethod($instance, $method);

		$output = $rm->invokeArgs($instance, $segments);

		//var_dump($output);

		//---------------------------------------------------------
		// middleware::end()
		//---------------------------------------------------------
		if( $middleware !== NULL ) {

			$this->middleware( $middleware, 'end' );

		}

		//---------------------------------------------------------
		// runtime::end()
		//---------------------------------------------------------
		if(class_exists('RuntimeMiddleware')) {
			
			$this->middleware($runtime, 'end');

		}

	}

	// function segments() {
	// 	return $this->segmens;
	// }

	// function cwdir() {
	// 	return $this->cwdir;
	// }

	// function controller() {
	// 	return $this->ctrl;
	// }

	// function www() {
	// 	return ROOTDIR . 'application/www/';
	// }

	// function make() {

	// 	$this->cwdir 	= [];

	// 	$pathinfo 	 	= $_SERVER['PATH_INFO'] ?? '/';

	// 	$actual_link 	= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".DOMAIN."$_SERVER[REQUEST_URI]";

	// 	/* 
	// 	| Build PathInfo
	// 	|-------------------------------------------- */

	// 	$routes 	 	= $GLOBALS['config']['route'];

	// 	$routepath 		= '';

	// 	if($pathinfo == '/') {
		
	// 		$pathinfo 	= $routes[':default'];
		
	// 	}else {
			
	// 		$pathinfo  	= \str_replace_first('/', '', $pathinfo, 1);

	// 		foreach($routes as $match => $route) {
	
	// 			$match 		= 
	// 				strtr( $match, [
	// 					'/'		 => '\/',
	// 					'(:any)' => '([\w|\/|\-|\_|\.]*)',
	// 					'(:num)' => '(\d+)'
	// 				]);

	// 			if( preg_match('/^'.$match.'$/', $pathinfo) > 0 ) {

	// 				// Route Match => break
	// 				//-------------------------------
	// 				$pathinfo = preg_replace('/^'.$match.'$/', $route, $pathinfo);
	// 				break;

	// 			}

	// 			unset($routes[$match]);

	// 		}

	// 	}

	// 	/* 
	// 	| Check and Found Controller
	// 	|-------------------------------------------- */

	// 	$segments 		= explode('/', $pathinfo);
	// 	$fcontroller 	= NULL;

	// 	$dir 			= $this->www();

	// 	do {

	// 		$controller = $segments[0];

	// 		if(is_dir($dir . $controller)) {

	// 			$fcontroller 		= $dir . $controller .'/'. $controller . '.cs.php';

	// 			if(file_exists($fcontroller)) {
	// 				array_shift($segments);
	// 				$this->segments = $segments;
	// 				break;
	// 			}else {
	// 				$controller 	= NULL;
	// 				$fcontroller  	= NULL;
	// 			}
	// 			$this->cwdir[] = $segments[0];
			
	// 		}
			
	// 		$dir = $dir . $segments[0] . '/'; 
	// 		array_shift($segments);

	// 	}while(!empty($segments));

	// 	$this->ctrl 	= $controller;

	// 	$this->fctrl 	= $fcontroller;

	// 	unset($router);

	// }

	// function handle() {

	// 	$utils = $GLOBALS['config']['app']['utils'] ?? [];

	// 	foreach($utils as $fileutils) {
	// 		$fileutils = ROOTDIR . 'system/utils/' . $fileutils . '.php';
	// 		require_once($fileutils);
	// 	}

	// 	foreach (glob(APPDIR . 'core/' . '*Controller.php') as $filecore) {
	// 		require_once($filecore);
	// 	}

	// 	if( file_exists($this->fctrl) ){
	// 		include( $this->fctrl );
	// 		Injection::set('controller', $this->fctrl);
	// 	}

	// 	$class = ucfirst($this->ctrl).'CS';

	// 	if(!class_exists($class)) {
	// 		// catch
	// 		throw new ClassNotFoundException(0);
	// 		return;
	// 	}

	// 	$inst 		= new $class;
	// 	$segments 	= $this->segments;

	// 	$methodname = 'index';

	// 	if(!empty($segments)) {

	// 		if($segments[0] == "") {
	// 			$methodname = 'index';
	// 		}else if(method_exists($inst, $segments[0])) {
	// 			$methodname = $segments[0];
	// 			array_shift($segments);
	// 		}

	// 	}

	// 	if($methodname==NULL) {
	// 		// catch
	// 		throw new MethodNotFoundException(0);
	// 		return;	
	// 	}

	// 	if(!method_exists($inst, '__set_environment')) {
	// 		// catch
	// 		throw new MethodNotFoundException(0);
	// 		return;
	// 	}

	// 	if(!method_exists($inst, $methodname)) {
	// 		// catch
	// 		throw new MethodNotFoundException(0);
	// 		return;
	// 	}

	// 	$strcwdir 	= implode('/', $this->cwdir);

	// 	$strcwdir 	= ($strcwdir!='') ? ($strcwdir . '/') : $strcwdir;

	// 	$cwurl 		= ($strcwdir . $this->ctrl . '/');

	// 	$ctrlpath 	= APPDIR . 'www/';

	// 	$inst->__set_environment([
	// 		'ctrl'		=> $this->ctrl,
	// 		'cwurl' 	=> $cwurl,
	// 		'base'		=> $ctrlpath . $cwurl,
	// 		'view'		=> $ctrlpath . $cwurl . $this->ctrl . '.ui.php',
	// 		//'js'		=> $ctrlpath . $cwurl . $this->ctrl . '.js',
	// 		//'css'		=> $ctrlpath . $cwurl . $this->ctrl . '.css'
	// 		'js'		=> $ctrlpath . $cwurl . 'script' . '.js',
	// 		'css'		=> $ctrlpath . $cwurl . 'style' . '.css'
	// 	]);

	// 	$method 	= new \ReflectionMethod($inst, $methodname);

	// 	if(method_exists($inst, '__middleware')) {
	// 		$inst->__middleware();
	// 	}

	// 	$output 	= $method->invokeArgs($inst, $segments);

	// 	Response::out($output);

	// }

}
