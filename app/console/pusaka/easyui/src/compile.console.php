<?php 
namespace Pusaka\Easyui;

use Pusaka\Easyui\Lib\Compiler;
use Pusaka\Easyui\Lib\Registered;

use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;
use Pusaka\View\EasyUI;

include(ROOTDIR . 'app/console/pusaka/easyui/lib/Registered.php');

class Compile extends Command {

	protected $signature 	= 'pusaka\easyui:compile {--debug}';

	protected $description 	= 'Compile EasyUI - Pusaka Template Engine';

	public function handle() {

		$dir = ROOTDIR . 'app/web/';

		IOUtils::directory($dir)->scan(true, function($file) {

			if( preg_match('/\w+\.easyui\.php/', basename($file) ) > 0 ) {
				//var_dump($file);

				$script = file_get_contents($file);
				
				if($script !== '' AND is_string($script)) {

					$engine = new EasyUI( preg_split('/\n/', $script) );

					$engine->registerDirectives( Registered::directiveList() );

					$engine->registerComponents( Registered::componentList() );

					$engine->registerPipes(  	 Registered::pipeList() 	 );

					$script = $engine->compile()->getCompiled();

					unset($engine);

				}

				$name 	= basename($file, '.easyui.php');

				$save 	= path(dirname($file)) . $name . '.ui.php';

				IOUtils::file($save)->write($script);

			}

		});

	}

}