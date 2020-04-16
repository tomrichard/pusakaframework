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

		$pattern  = '/<<\s*([\[\]\w\s\(\)\=\$\"\'><-]+)\s*>>/';

		$lines 	  = preg_split("/((\r?\n)|(\r\n?))/", $text);

		foreach ($lines as $i => $line) {	

			if(preg_match($pattern, $line)) {
				
				$lines[$i] = preg_replace_callback(
								$pattern, 
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