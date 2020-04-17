<?php 
namespace Pusaka\Core;

class Benchmark {
	
	static function load() {

		$load = ( microtime(true) - BENCHMARK_START );

		$load = number_format((float) $load, 4, '.', ''); 

		return $load;

	}

	static  function memory() {

		return ( memory_get_peak_usage() - BENCHMARK_MEMORY );

	}

	static function files() {

		return ( get_included_files() );

	}

}