<?php 
namespace Pusaka\Microservices;

use ReflectionFunction;

class Controller {
	
	protected $load;
	protected $auth;
	
	public function __construct($env) {

		if(isset($env['loader'])) {
			$this->load = $env['loader'];
		}else {
			$this->load = new Loader($env);
		}

		if(isset($env['auth'])) {
			$this->auth = $env['auth'];
		}

	}

	public function __getClosure($funct, $var) {

		$reflection = new ReflectionFunction($funct);
		$arguments  = $reflection->getParameters();

		$arg_value  = [];

		foreach ($arguments as $value) {

			if( !is_null($class = $value->getClass()) ) {

				switch ($class->name) {
					
					case 'Pusaka\\Core\\Loader' :
						$arg_value[] 	= $this->load;
					break;

				}

			}else {

				$arg_value[] = $var[0] ?? NULL;

				array_shift($var);

			}

		}

		unset($reflection);

		return call_user_func_array($funct, $arg_value);

	}

}