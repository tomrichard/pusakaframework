<?php 
namespace Pusaka\Core;

class Benchmark {
	
	static function load() {

		return ( microtime(true) - BENCHMARK_START );

	}

	static  function memory() {

		return ( memory_get_peak_usage() - BENCHMARK_MEMORY );

	}

	static function files() {

		return ( get_included_files() );

	}

}