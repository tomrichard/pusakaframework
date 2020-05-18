<?php 
namespace Pusaka\Vcontrol;

use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;

class push extends Command {

	protected $signature 	= 'pusaka\vcontrol:push {comment}';

	protected $description 	= 'Version Controlling Pusaka';

	public function handle() {

		$comment = $this->argument('comment');

		$this->line("comment : " . $comment);

		$this->exec('git add .' , function($output) {
			echo $output;
		});

		$this->exec('git status' , function($output) {
			echo $output;
		});

		$this->exec('git commit -m "'.$comment.'"' , function($output) {
			echo $output;
		});

	}

}