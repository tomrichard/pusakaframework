<?php 
namespace Pusaka\Assets;

use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;

class Bootstrap extends Command {

	protected $signature 	= 'pusaka\assets:bootstrap {--download}';

	protected $description 	= 'Assets management : Bootstrap';

	public function handle() {

		$repo 	= ROOTDIR . 'app/pusaka/assets/repo/';

		$dest 	= ROOTDIR . 'static/vendors/bootstrap/';

		if(is_dir($repo . 'bootstrap')) {

			IOUtils::directory($repo . 'bootstrap')->copy( $dest );
		
		}

		$url = 'https://github.com/twbs/bootstrap/releases/download/v4.0.0/bootstrap-4.0.0-dist.zip';

		file_put_contents( $repo . 'bootsrap.zip', file_get_contents($url) );

	}

}