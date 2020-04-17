<?php 
namespace Pusaka\Schedule;

use Pusaka\Schedule\Lib\Schedule;
use Pusaka\Schedule\Lib\TaskScheduler;
use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;
use Pusaka\Utils\DateUtils;

include(ROOTDIR . 'app/console/pusaka/schedule/lib/Schedule.php');
include(ROOTDIR . 'app/console/pusaka/schedule/lib/TaskScheduler.php');

class Run extends Command {

	protected $signature 	= 'pusaka\schedule:run';

	protected $description 	= 'Scheduler - Run schedule';

	public function handle() {

		// $stack 		= [];

		// $Schedules 	= [];

		// $Schedule 		= new Schedule();

		// $Schedule
		// 	->command('tom')
		// 	->everyMinute();

		// $Schedules[] 	= $Schedule;

		// // ------------------------------------

		// $Schedule 		= new Schedule();

		// $Schedule
		// 	->command('')
		// 	->everyFiveMinute();

		// $Schedules[] 	= $Schedule;

		// // ------------------------------------

		// //$TaskScheduler  = new TaskScheduler();

		// //$TaskScheduler->add( $Schedule );

		// //$TaskScheduler->run();

		// foreach ($Schedules as $schedule) {
		// 	$stack[] = new AsyncTask($schedule);
		// }

		// foreach ($stack as $t) {
			
		// 	$t->start();

		// }

		//$TaskScheduler 	= new TaskScheduler('PusakaScheduler');

		/*

		// Report every minute
		//--------------------------------------------------------
			$Schedule 		= new Schedule();

			$Schedule
				->command('tom\example:minute_report --time=1')
				->everyMinute();

			$TaskScheduler->add( $Schedule );
		//--------------------------------------------------------

		// Report every five minute
		//--------------------------------------------------------
			$Schedule 		= new Schedule();

			$Schedule
				->command('tom\example:minute_report --time=5');
				//->everyFiveMinutes();

			$TaskScheduler->add( $Schedule );
		//--------------------------------------------------------

		// Report every ten minute
		//--------------------------------------------------------
			$Schedule 		= new Schedule();

			$Schedule
				->command('tom\example:minute_report --time=10')
				->everyTenMinutes();

			$TaskScheduler->add( $Schedule );
		//--------------------------------------------------------

		*/

		// Report every 18:25
		//--------------------------------------------------------
			$Schedule 		= new Schedule();

			$Schedule
				->command('tom\example:minute_report --time=10')
				->everyMinute();

			$Schedule->run();

			//$TaskScheduler->add( $Schedule );
		//--------------------------------------------------------

		//$TaskScheduler->run();	

	}

}