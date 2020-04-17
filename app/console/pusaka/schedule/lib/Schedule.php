<?php 
namespace Pusaka\Schedule\Lib;

use Pusaka\Utils\DateUtils;

class Schedule {

	private $id;

	private $timezone;

	private $time;
	private $frequency;

	private $command;

	private $runat;

	function __construct() {
		$this->minute 		= NULL;
		$this->frequency 	= NULL;
		$this->runat 		= NULL;  
		$this->id 			= uniqid();
	}

	function command( $command ) {
		$this->command 	= $command;
		return $this;
	}

	function timezone() {
		$this->timezone = '';
		return $this;
	}

	function everyMinute() {
		$this->frequency 	= 'MINUTELY';
		$this->time 		= 1;
		return $this;
	}

	function everyFiveMinutes() {
		$this->frequency 	= 'MINUTELY';
		$this->time 		= 5;
		return $this;
	}

	function everyTenMinutes() {
		$this->frequency 	= 'MINUTELY';
		$this->time 		= 10;
		return $this;
	}

	function everyFifteenMinutes() {
		$this->frequency 	= 'MINUTELY';
		$this->time 		= 15;
		return $this;
	}

	function everyThirtyMinutes() {
		$this->frequency 	= 'MINUTELY';
		$this->time 		= 30;
		return $this;
	}

	function hourly() {
		$this->frequency 	= 'MINUTELY';
		$this->time 		= 60;
		return $this;	
	}

	function daily() {
		$this->frequency 	= 'DAILY';
		$this->time 		= '00:00:00';
	}

	function dailyAt($time) {

		if(preg_match('/^\d\d:\d\d$/', $time) > 0) {
			$this->frequency 	= 'DAILY';
			$this->time 		= $time . ':00';
		}

	}

	function next() {

		if($this->frequency == 'MINUTELY') {

			$now 		= DateUtils::now();
			$next 		= DateUtils::now()->setSecond('00');

			$mod 		= $now->getMinute() % $this->time;

			$add 		= $this->time - $mod;

			$next->add($add, 'minute');

			$unix_now 	= $now->toSeconds();
			$unix_next 	= $next->toSeconds();

			$diff 		= $unix_next - $unix_now;

			$this->runat = $this->time;

			return $diff;
		
		}

		if($this->frequency == 'DAILY') {

			$now 		= DateUtils::now();
			$next 		= DateUtils::now()->setTime($this->time);

			$unix_now 	= $now->toSeconds();
			$unix_next 	= $next->toSeconds();

			$diff 		= $unix_next - $unix_now;

			if($diff < 0) {

				$next->add(1, 'day');

				$unix_now 	= $now->toSeconds();
				$unix_next 	= $next->toSeconds();

				$diff 		= $unix_next - $unix_now;

			}

			$this->runat = $next;

			return $diff;

		}

		$this->runat = NULL;
		
		return NULL;

	}

	function id() {
		return $this->id;
	}

	function execute() {

		if( $this->runat !== NULL ) {

			$command = $this->command;

			$bin = ROOTDIR . 'pusaka';

			$cmd = $bin . " $command ";

			$cmd = 'php ' . $cmd;

			echo shell_exec($cmd);

			return TRUE;

		}

		var_dump($this->runat);

		return FALSE;

	}

	function run() {

		while(true) {
			
			sleep($this->next());

			$log 		= path(ROOTDIR) . 'storage/schedules/logs/' . date('Y.m.d.H.i.s') . '_' . uniqid() . '.log';

			$message 	= json_encode([ 
				'time' 		=> DateUtils::now()->getFull(),
				'execute'	=> $this->command
			]);

			file_put_contents($log, $message);

			$this->execute();
			
		}

	}

}