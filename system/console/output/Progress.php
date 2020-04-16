<?php 
namespace Pusaka\Console\Output;

class Progress {

	private $total;
	private $count;

	function __construct($count) {
		
		$this->count = $count;

	}

	function draw($done, $total, $info="", $width=50) {
		$perc 	= round(($done * 100) / $total);
		$bar 	= round(($width * $perc) / 100);
		return sprintf("%s%%[%s>%s]%s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width-$bar), $info);
	}

	function start() {

		$this->total = 0;
	
	}

	function next() {

		$this->total++;
		echo $this->draw($this->total, $this->count);

	}

	function finish() {

		$this->total = 0;

	}

}