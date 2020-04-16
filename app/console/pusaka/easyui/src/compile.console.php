<?php 
namespace Pusaka\Easyui;

use Pusaka\Easyui\Lib\Compiler;
use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;

include(ROOTDIR . 'app/console/pusaka/easyui/lib/Compiler.php');

class Compile extends Command {

	protected $signature 	= 'pusaka\easyui:compile {--debug}';

	protected $description 	= 'Compile EasyUI - Pusaka Template Framework';

	public function handle() {

		$dir = ROOTDIR . 'app/web/';

		IOUtils::directory($dir)->scan(true, function($file) {

			if( preg_match('/\w+\.easyui\.php/', basename($file) ) > 0 ) {
				//var_dump($file);

				$script = file_get_contents($file);

				$script = Compiler::compile($script);

				$name 	= basename($file, '.easyui.php');

				$save 	= path(dirname($file)) . $name . '.ui.php';

				IOUtils::file($save)->write($script);

			}

		});

	}

}