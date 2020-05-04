<?php 
namespace Pusaka\Service;

use Pusaka\Console\Command;
use Pusaka\Console\Service;

use Pusaka\Utils\IOUtils;

class Install extends Command {

	protected $signature 	= 'pusaka\service:install';

	protected $description 	= 'Install Service - Pusaka Service';

	public function handle() {
		
		$dir = ROOTDIR . 'app/web/www/';

		IOUtils::directory($dir)
			->filter('/.*\/module\.init\.json/')
			->scan(true, function($file) use ($dir) {			

				$module 		= json_decode(file_get_contents($file));

				$basedir 		= dirname($file);

				$path 			= preg_replace('/^'.strtr($dir, ['/'=>'\/']).'/', '', $basedir);

				$servicefile 	= $basedir . '/service/run.service.php';

				if(!file_exists($servicefile)) {
					
					$this->error('Service file not found. [ ' . $servicefile . ' ]');
					return;

				}
				
				$module->path 	= path($path);

				$service 		= new Service($module);

				include($servicefile);

			});

	}

}