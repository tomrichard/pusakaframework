<?php 
namespace Pusaka\Console;

class Console {

	function execute($command, $args = []) {

		$bin = ROOTDIR . 'pusaka';

		$cmd = $bin . " $command " . implode(' ', $args);

		shell_exec('php ' . $cmd);

	}
	
	public static function run( $argv ) {

		$app_dir = APPDIR . 'console/';

		array_shift($argv);

		if(isset($argv[0])) {

			$cmd 	= $argv[0];

			if(preg_match('/([\w|\/]+):(\w+)/', $cmd, $match) > 0) {

				$app 		= $match[1];
				$file 		= $match[2];

				$console 	= $app_dir . path($app) . 'src/' . $file . '.console.php';


				if(!file_exists( $console )) {
					echo 'file not found. ( '.$console.' )';
				}

				include($console);

				$parts 		= explode('/', $app);

				foreach ($parts as $i => $val) {
					$parts[$i] = ucfirst($val);
				}

				$parts[$i+1] = $file;

				$class 		 = implode('\\', $parts);

				array_shift($argv);

				$instance 	 = new $class($argv);

				$instance->handle();

				return;

			}

		}	

		echo 'command not found.';

	}

}