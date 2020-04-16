<?php 
namespace Pusaka\Core;

use is_assoc;
use Pusaka\Core\Injection;
use Pusaka\Hmvc\Assets;
use Pusaka\Memory\Session;

class Loader {

	/* 
	| [ctrl, cwurl, base, view, js]
	|--------------------------------------------- */
	private 	$env;
	
	/* 
	| save variable - render in view
	|--------------------------------------------- */
	private 	$vars;
	
	/* 
	| loader class
	|--------------------------------------------- */
	protected 	$load;

	/* 
	| assets class
	|--------------------------------------------- */
	private 	$assets;

	/* 
	| session class
	|--------------------------------------------- */
	private 	$session;


	// EDIT : 2019-20-08
	//----------------------------------------------------------
	function __construct($env) {
		$this->env 		= $env;
		$this->load 	= $this;
		$this->vars 	= [];
		$this->session  = Session::class; 
		$this->assets 	= Assets::class;
	}

	// EDIT : 2019-20-08
	//----------------------------------------------------------
	function view($__subui = NULL, $vars = []) {

		if(!is_array($vars)) {
			throw new ParameterInvalidException("seconds parameter - ($vars) must be associative array. Loader::view(\$ui_name = [string | NULL], \$vars = [assoc_array])");
		}

		if(!is_assoc($vars)){
			throw new ParameterInvalidException("seconds parameter - ($vars) must be associative array. Loader::view(\$ui_name = [string | NULL], \$vars = [assoc_array])");
		}

		$this->vars = array_merge($this->vars, $vars);

		extract($this->vars);

		$__file = '';

		// Check on current directory
		//--------------------------------------------
		if( $__subui === NULL) {
			$__file = ( $this->env['view'] );
		}else if( is_string($__subui) ) {
			$__file = $this->env['base'].'sub.ui/'.$__subui.'.ui.php';
		}

		// Check on ui directory
		//--------------------------------------------
		if(!file_exists($__file)) {
			$__file = ( APPDIR . 'ui/' . $__subui . '.ui.php' );	
		}

		if(file_exists($__file)) {
			
			require_once($__file);

			$__file_on_edit = $__file;
			$__dir 			= dirname($__file_on_edit);
			$__basename 	= basename($__file_on_edit);
			
			if(file_exists($__file_load = path($__dir) . strtr($__basename, ['.ui.php'=>'.easyui.php']))) {
				$__file_on_edit = $__file_load;
			}

			Injection::add('view', $__file_on_edit);
			
			return;
		}

		throw new ViewNotFoundException('view file : ' . $__file . ' not found.');

		
		// // Load file view
		// //--------------------------------------------
		// if(file_exists($__file)) {
			

		// 	if(ENVIRONMENT === 'DEVELOPMENT') {
										
		// 		if(file_exists($xdebug = path(ROOTDIR) . 'storage/debug/xdebug.php')) {
					
		// 			echo(strtr(file_get_contents($xdebug), [
		// 				'<{@file}>' => $__file,
		// 				'<{@text}>' => basename($__file)
		// 			]));

		// 		}

		// 	}

		// 	require_once($__file);
		
		// }else {
			
		// 	if(ENVIRONMENT == 'PRODUCTION') {
		// 		// log production
		// 	}else {
		// 		// log development
		// 		echo "View not found. ( $__file )";
		// 	}

		// }

	}

	function addVar($vars) {

		foreach ($vars as $key => $value) {
			$this->vars[$key] = $value;
		}

	}

	function var($key) {
		return $this->vars[$key] ?? '';
	}

	// EDIT : 2019-20-08
	//----------------------------------------------------------
	function javascript() 	{

		$vars  	  = $this->vars; 

		$compile  = function($script) use ($vars) {

			$js = file_get_contents($script);

			$js = preg_replace("/<script ?.*>/", "", $js);

			$js = preg_replace("/<\/script>/", "", $js);

			// $js = preg_replace_callback("/{{\s*\$((\w+)(->(\w+))?)\s*}}/", 
			// 			function ($matches) use ($vars) {

			// 				return '';

			// 				$parent = $matches[2];

			// 				if($parent !== '') {
			// 					if(isset($vars[$parent])) {

			// 						if($vars[$parent] instanceof \stdClass) {
			// 							return "JSON.parse('".json_encode($vars[$parent])."');";
			// 						}

			// 						if(is_array($vars[$parent])) {
			// 							return "JSON.parse('".json_encode($vars[$parent])."');";
			// 						}

			// 					}
			// 				}

			// 				return '';

			// 			},
			// 				$js
			// 		);

			$js = preg_replace_callback('/{{\s*\$((\w+)(->(\w+))?)\s*}}/', function($matches) use($vars) {
					
					$parent = $matches[2];

					$key 	= $matches[4] ?? NULL;

					if($parent !== '') {
						if(isset($vars[$parent])) {

							$var = $vars[$parent];

							if($key !== NULL) {
									
								$var = $var->$key;
							
							}

							if(is_string($var)) {
								return "'".$var."'";
							}

							if($var instanceof \stdClass) {
								return "JSON.parse('".json_encode($var)."');";
							}

							if(is_array($var)) {
								return "JSON.parse('".json_encode($var)."');";
							}

						}
					}

					return '';

				}, $js);
			
			$url = "data:application/javascript;base64,".base64_encode($js);

			echo PHP_EOL;
			echo '<script src="'.$url.'"></script>';
			echo PHP_EOL;

		};

		$basename = basename($this->env['js']);

		$file 	  = strtr($this->env['js'], [$basename => 'resources/'.$basename]);

		if(file_exists($file)) {
			$compile($file);
			return;
		}

		if(!file_exists($this->env['js'])) {
			echo 'Javascript not found. ( '.$this->env['js'].' )';
			return;
		}

		$compile($this->env['js']);

	}

	// EDIT : 2019-20-08
	//----------------------------------------------------------
	function style() {

		$compile = function($xcss) {

			echo PHP_EOL;
			echo '<style type="text/css">';
			echo PHP_EOL;
			//echo Compilers::css($xcss);
			echo $xcss;
			echo '</style>';
			echo PHP_EOL;

		};

		$basename = basename($this->env['css']);

		$file 	  = strtr($this->env['css'], [$basename => 'resources/'.$basename]);

		if(file_exists($file)) {
			$compile(file_get_contents($file));
			return;
		}

		if(!file_exists($this->env['css'])) {
			echo 'CSS not found. ( '.$this->env['css'].' )';
			return;
		}

		$compile( file_get_contents($this->env['css']) );

		// if(is_dir(APPDIR . 'uix')) {
		// 	foreach (glob(APPDIR . 'uix/' . "*.xcss.php") as $filexcss) {
		// 		$xcss 		= require_once($filexcss);
		// 		$compile($xcss);
		// 	}
		// }

		// if(!file_exists($this->env['css'])) {
		// 	echo 'CSS not found. ( '.$this->env['css'].' )';
		// 	return;
		// }

		// $xcss = require_once($this->env['css']);

		// $compile($xcss);



	}

	function env($key) {

		return $this->env[$key] ?? NULL;

	}

	function library($lib) {

		$file = ROOTDIR . 'libraries/' . $lib . '/' . basename($lib) . '.php';

		if(file_exists($file)) {
			require_once($file);
		}else {
			echo "Library not found.";
		}

	}

	function model( $model ) {

		$file 	 	= APPDIR . 'models/' . strtr($model , '.', '') . '.php';

		if(file_exists($file)) {
			require_once($file);
		}else {
			echo "Model not found.";
		}

		$class = ucfirst(basename($model));

		if(!class_exists($class)) {
			echo "Class $class not found.";
		}

	}

	// // new in 2018.10.5
	// function ngComponent($name) {
		
	// 	$base 	= $this->env['base'];

	// 	$name 	= 'ng-'.$name;

	// 	$viewComponent 		= '';
	// 	$scriptComponent	= '';

	// 	if(!file_exists($base.'sub.component/'.$name.'/'.$name.'.ui.php')) {
	// 		die('View : {'.$name.'.ui.php} not found.');
	// 	}else {
	// 		$viewComponent = file_get_contents($base.'sub.component/'.$name.'/'.$name.'.ui.php');
	// 	}

	// 	if(!file_exists($base.'sub.component/'.$name.'/'.$name.'.js.php')) {
	// 		die('ngComponent : {'.$name.'} not found.');
	// 	}else {
	// 		$scriptComponent = file_get_contents($base.'sub.component/'.$name.'/'.$name.'.js.php');
	// 	}

	// 	$jsContents = preg_replace("/<script ?.*>/", "", $scriptComponent);

	// 	$jsContents = preg_replace("/<\/script>/", "", $jsContents);

	// 	$jsContents = str_replace('@template', '`'.$viewComponent.'`', $jsContents);

	// 	$url = "data:application/javascript;base64,".base64_encode($jsContents);

	// 	echo '<script src="'.$url.'"></script>';

	// }

	// function view($subui = NULL, $path = NULL) {

	// 	$lang 			= $this->lang;

	// 	if(!empty($this->vars) and is_array($this->vars)) {
	// 		foreach ($this->vars as $key => $value) {
	// 			${$key} = $value;
	// 		}
	// 	}

	// 	if($path!==NULL) {
	// 		$fview = APPDIR . 'www/' . $path . '/' . $subui . '.ui.php';
	// 		if(!file_exists($fview)) {
	// 			die('View : {'.$subui.'.ui.php} not found.');
	// 		}
	// 		require_once($fview);
	// 		return;
	// 	}

	// 	if($subui!==NULL) {
	// 		if(!file_exists($this->env['base'].'sub.ui/'.$subui.'.ui.php')) {
	// 			die('View : {'.$subui.'.ui.php} not found.');
	// 		}else {
	// 			require_once($this->env['base'].'sub.ui/'.$subui.'.ui.php');
	// 		}
	// 	}else {
	// 		if(!file_exists($this->env['view'])) {
	// 			die('View : {'.$this->env['cwdir'].'.ui.php} not found.');
	// 		}
	// 		require_once($this->env['view']);
	// 	}
	// }

	// function var($vars) {
	// 	foreach ($vars as $key => $value) {
	// 		$this->vars[$key] = $value;
	// 	}
	// }

	// function addvars(array $vars) {
	// 	foreach ($vars as $key => $value) {
	// 		$this->vars[$key] = $value;
	// 	}
	// }

	// function js() {
		
	// 	$jsContents = file_get_contents($this->env['js']);
		
	// 	$jsContents = preg_replace("/<script ?.*>/", "", $jsContents);

	// 	$jsContents = preg_replace("/<\/script>/", "", $jsContents);

	// 	preg_match_all("/@{{(\w+)}}/", $jsContents, $output);
		
	// 	foreach ($output[0] as $idx => $value) {
	// 		$jsContents = str_replace($value, $this->vars[$output[1][$idx]] ?? '', $jsContents);
	// 	}

	// 	$url = "data:application/javascript;base64,".base64_encode($jsContents);

	// 	require_once(ROOTDIR . 'system/helpers/javascript.php');

	// 	echo '<script src="'.$url.'"></script>';

	// }

	// function lib($lib) {

	// 	$file = APPDIR . 'libraries/' . $lib . '/' . basename($lib) . '.lib.php';

	// 	if(file_exists($file)) {
	// 		require_once($file);
	// 	}else {
	// 		echo "Library not found.";
	// 	}

	// }

	// function lang($lang, $files) {
	// 	$this->lang->load($lang, $files);
	// }

}