<?php 
use Pusaka\Http\Middleware;
use Pusaka\Http\Request;
use Pusaka\Http\Response;

use Pusaka\Core\Loader;
use Pusaka\Core\Benchmark;

class RuntimeMiddleware extends Middleware {

	public function begin(Loader $load) {

		if(is_development()) {
			$load->console->execute('pusaka/easyui:compile');
		}
		
	}

	public function end() {
		
		// echo Benchmark::load();
		// echo '<br>';
		// echo Benchmark::memory();
		// echo '<br>';
		// echo var_dump(Benchmark::files());
		// echo '<br>';	

	}

}