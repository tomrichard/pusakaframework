<?php 
namespace Pusaka\Vcontrol;

use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;

class push extends Command {

	protected $signature 	= 'pusaka\vcontrol:push {comment}';

	protected $description 	= 'Version Controlling Pusaka';

	public function handle() {

		$gituser = IOUtils::file(ROOTDIR . 'app/console/pusaka/vcontrol/data/gituser.json')->json();

		$user 	 = $gituser->username;

		$pass 	 = $gituser->password;

		$url 	 = $gituser->repo;
		
		$giturl  = preg_replace('/(https?:\/\/)/', '$1'.$user.':'.$pass.'@', $url);

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

		echo "\r\n";

		$this->exec('git push "'.$giturl.'" "origin" master', function($output) {
			echo $output;
		});

	}

}