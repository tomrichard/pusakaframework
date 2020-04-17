<?php 
namespace Pusaka\Component;

use ReflectionClass;

class Widget {

	public $vars;

	function __construct( $vars = [] ) {

		$this->vars = (object) $vars;

		$this->widget();

		$this->render();

	}

	function __compile( $text, $___vars ) {

		$echo  	  = '/<<\s*([\[\]\w\s\(\)\=\$\"\'><-]+)\s*>>/';

		$if  	  = '/@if\(\s*\$(\w+)\s*\)/';

		$lines 	  = preg_split("/((\r?\n)|(\r\n?))/", $text);

		foreach ($lines as $i => $line) {	

			// echo variable
			if(preg_match($echo, $line)) {
				
				$lines[$i] = preg_replace_callback(
								$echo, 
									function($matches) use ($___vars)
									{
										extract((array) $___vars);

										$__var = strtr(trim($matches[1]), ['$' => '']);

										if(!isset(${$__var})) {
											return '';
										}

										return ${$__var};

									}, 
										$line
							);
				
				//($token['pattern'], $token['replace'], $line);

			}

			// if(preg_match($if, $line)) {

			// 	$lines[$i] = preg_replace_callback(
			// 					$if, 
			// 						function($matches) use ($___vars)
			// 						{
			// 							extract((array) $___vars);

			// 							$__var = strtr(trim($matches[1]), ['$' => '']);

			// 							if(!isset(${$__var})) {
			// 								return '';
			// 							}

			// 							return ${$__var};

			// 						}, 
			// 							$line
			// 				);

			// }

		}

		$compiled = implode("\n", $lines);

		return $compiled;

	}

	function render() {

		$vars 	= $this->vars;

		$rc 	= new ReflectionClass($this);

		$file 	= $rc->getFilename();

		$dir  		= dirname($file);

		$basename 	= basename($dir);

		$view 		= $dir . '/' . $basename . '.ui.php';

		$contents 	= file_get_contents($view);

		$contents 	= $this->__compile($contents, $vars);

		echo $contents;

	}

}