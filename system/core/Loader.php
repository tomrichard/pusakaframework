<?php 
namespace Pusaka\Core;

use Pusaka\Exceptions\InvalidArgumentException;
use Pusaka\Exceptions\ViewNotFoundException;
use Pusaka\Exceptions\ResourceNotFoundException;
use Pusaka\Exceptions\LibraryNotFoundException;
use Pusaka\Exceptions\ModelNotFoundException;

use Pusaka\Console\Console;

use stdClass;

class Loader {

	private $env;
	private $auth;
	private $var;
	private $console;

	public function __construct( $env ) {

		$this->env 		= $env;
		$this->var  	= [];
		$this->console 	= new Console();

	}

	public function __get( $property ) {
		
		if(in_array($property, ['var', 'console'])) {
			return $this->{$property};
		}

		return NULL;

	}

	public function add( $vars ) {

		$this->var = array_merge($this->var, $vars);

	}

	public function view( $__ui = NULL, $__vars = [] ) {

		if(!is_array($__vars)) {
			throw new InvalidArgumentException("Loader::view($__ui = NULL, $__vars = []) | Second parameter must be an associative array.", 3455);
		}

		if(!is_assoc($__vars)){
			throw new InvalidArgumentException("Loader::view($__ui = NULL, $__vars = []) | Second parameter must be an associative array.", 3455);
		}

		$__file 	= '';

		$directory 	= $this->env['directory'];

		// Check on current directory
		//--------------------------------------------
		if( $__ui === NULL) {
			$__file = ( $directory . basename($directory) . '.ui.php' );
		}else if( is_string($__ui) ) {
			$__file = $directory . 'sub.ui/' . $__ui . '.ui.php';
		}

		// Check on ui directory
		//--------------------------------------------
		if(!file_exists($__file)) {
			$__file = ( APPDIR . 'web/ui/' . $__ui . '.ui.php' );	
		}

		$this->var = array_merge($this->var, $__vars);

		extract($this->var);

		if(file_exists($__file)) {
			
			require_once($__file);

			// $__file_on_edit = $__file;
			// $__dir 			= dirname($__file_on_edit);
			// $__basename 	= basename($__file_on_edit);
			
			// if(file_exists($__file_load = path($__dir) . strtr($__basename, ['.ui.php'=>'.easyui.php']))) {
			// 	$__file_on_edit = $__file_load;
			// }
			
			return;
		}

		throw new ViewNotFoundException('view file : ' . $__file . ' not found.');

	}

	public function javascript($__vars = []) {

		$vars = $this->var;

		if (!is_array($__vars)) {
			throw new InvalidArgumentException("Loader::javascript( $__vars = [] ) | Second parameter must be an associative array.", 3456);
		}

		if (!is_assoc($__vars)) {
			throw new InvalidArgumentException("Loader::javascript( $__vars = [] ) | Second parameter must be an associative array.", 3456);
		}

		$directory 	= $this->env['directory'];

		$file 	  	= $directory . 'res/script.js';

		if (!file_exists($file) ) {
			throw new ResourceNotFoundException("Javascript file not found in ($file) ", 3678);
		}

		$js 	  = file_get_contents($file);

		if ($js === '') {
			return;
		}

		$replace  = [];

		if ( preg_match_all('/\$_var_(\w+)/', $js, $matches) > 0 ) {

			foreach ($matches[1] as $value) {
				
				if ( isset($this->var[$value]) ) {

					if ( is_array($this->var[$value]) || ( $this->var[$value] instanceof stdClass ) ) {

						$replace['$_var_'.$value] = "JSON.parse('" . json_encode($this->var[$value]) . "')";

					}

					else if ( is_string($this->var[$value]) ) {

						$replace['$_var_'.$value] = "'" . $this->var[$value] . "'";

					}

				}

			}

		}

		$js 	  = strtr($js, $replace);

		$url 	  = "data:application/javascript;base64,".base64_encode($js);

		echo PHP_EOL;
		echo '<script src="'.$url.'"></script>';
		echo PHP_EOL;

	}

	public function style() {

		$directory 	= $this->env['directory'];

		$file 	  	= $directory . 'res/style.css';

		if (!file_exists($file)) {
			throw new ResourceNotFoundException("CSS file not found in ($file) ", 3679);
		}

		$css = file_get_contents($file);

		if ($css === '') {
			return;
		}

		$url = "data:text/css;base64,".base64_encode($css);

		echo PHP_EOL;
		echo '<link rel="stylesheet" type="text/css" href="'.$url.'">';
		echo PHP_EOL;

	}

	public function library( $lib ) {

		$lib  = strtr($lib, ['\\' => '/']);

		$file = ROOTDIR . 'libraries/' . $lib . '/' . basename($lib) . '.php';

		if(file_exists($file)) {
			require_once($file);
		}else {
			throw new LibraryNotFoundException("Library class not found ( $lib )", 3671);
		}

	}

	public function model( $model ) {

		$model  = strtr($lib, ['\\' => '/']);

		$file 	= ROOTDIR . 'app/database/models/' . $model . '.php';

		if(file_exists($file)) {
			require_once($file);
		}else {
			throw new ModelNotFoundException("Model class not found ( $file )", 3672);
		}

	}


}