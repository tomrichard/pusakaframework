<?php 
namespace Pusaka\Schedule\Lib;

use Thread;

class AsyncTask extends Thread {

	private $schedule;

	public function __construct( $schedule ) {
		$this->schedule = $schedule;
	}

	public function run() {

		while(true) {
			
			sleep($this->schedule->next());

			$this->schedule->execute();
			
		}

	}

}

class TaskScheduler {

	private $name;
	private $schedules;

	function __construct($name) {
		$this->name = $name;
	}

	function add( $schedule ) {
		$this->schedules[] = $schedule;
	}

	function run() {

		$task = [];

		foreach ($this->schedules as $schedule) {
			$task[] = new AsyncTask($schedule);
		}

		foreach ($task as $t) {
			$t->start();
		}

	}

}