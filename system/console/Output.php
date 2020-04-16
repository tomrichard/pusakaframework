<?php 
namespace Pusaka\Console;

use Pusaka\Console\Output\Progress;

class Output {
	
	function createProgress($count) {

		return new Progress($count);

	}

}