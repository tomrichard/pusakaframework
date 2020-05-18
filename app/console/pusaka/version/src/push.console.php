<?php 
namespace Pusaka\Vcontrol;

use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;

class push extends Command {

	protected $signature 	= 'pusaka\vcontrol:push {comment}';

	protected $description 	= 'Version Controlling Pusaka';

	public function handle() {

		$this->line("comment : " . $comment);

	}

}