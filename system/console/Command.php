<?php 
namespace Pusaka\Console;

class Command {

	protected $args;
	protected $opts;
	protected $signature;
	protected $description;
	protected $output;

	public function __construct( $argv ) {

		$this->output = new Output();
		
		$tokens 	= [
			// argument
			'\{\w+\}',
			// argument - array
			'\{\w+\\*\}',
			// argument - default value
			'\{\w+=[^\s]+\}',
			// options
			'\{--\w+\}',
			// options - default
			'\{--\w+\=default\}',
			// options - array
			'\{--\w+\=\*}',
			// options - default value
			'\{--\w+\=[^\s]+}'
		];

		$token 		= implode('|', $tokens);

		//$this->argv = $argv;

		if(preg_match_all("/$token/", $this->signature, $match) > 0) {
			
			foreach ($match[0] as $pair) {
				
				// {param}
				//------------------------
				if( preg_match('/\{(\w+)\}/', $pair, $key) > 0 ) {
						
					if(isset($argv[0])) {

						if( preg_match('/--\w+/', $argv[0]) > 0 ) {
							continue;
						}

						$this->args[$key[1]] = $argv[0];
						array_shift($argv);

					}

					continue;
					
				}

				// {param*}
				//------------------------
				if( preg_match('/\{(\w+)\\*\}/', $pair, $key) > 0 ) {

					foreach ($argv as $val) {
						
						if( preg_match('/--\w+/', $val) > 0 ) {
							break;
						}

						$this->args[$key[1]][] = $val;
						
						array_shift($argv);
					
					}

					continue;

				}

				// {param=value}
				//------------------------
				if( preg_match('/\{(\w+)=([^\s]+)\}/', $pair, $key) > 0 ) {

					if(isset($argv[0])) {

						if( preg_match('/--\w+/', $argv[0]) > 0 ) {
							continue;
						}

						$this->args[$key[1]] = $argv[0];
						array_shift($argv);

					}else {
						$this->args[$key[1]] = trim($key[2]);
					}

					continue;

				}

				// {--option}
				//------------------------
				if( preg_match('/\{(--\w+)\}/', $pair, $key) > 0 ) {

					foreach ($argv as $i => $val) {
						
						if(trim($val) === $key[1]) {
							$this->opts[$key[1]] = true;	
							unset($argv[$i]);
						}

					}

					if(!isset($this->opts[$key[1]])) {
						$this->opts[$key[1]] = false;
					}

					$argv = array_values($argv);

					continue;

				}

				// {--option=default}
				//------------------------
				if( preg_match('/\{(--\w+)=default\}/', $pair, $key) > 0 ) {

					foreach ($argv as $i => $val) {
						
						if(trim($val) === $key[1]) {
							$this->opts[$key[1]] = true;	
							unset($argv[$i]);
						}

					}

					if(!isset($this->opts[$key[1]])) {
						$this->opts[$key[1]] = true;
					}

					$argv = array_values($argv);

					continue;

				}

				// {--option=value}
				//------------------------
				if( preg_match('/\{(--\w+)\=([^\s]+)}/', $pair, $key) > 0 ) {

					if($key[2] != '*') {

						foreach ($argv as $i => $val) {

							if( preg_match('/'.$key[1].'=(.+)/', $val, $kmatch) > 0 ) {
								$this->opts[$key[1]] = $kmatch[1];
								unset($argv[$i]);
							}

						}

						if(!isset($this->opts[$key[1]])) {
							$this->opts[$key[1]] = $key[2];
						}

						$argv = array_values($argv);

						continue;

					}

				}

				// {--option=*}
				//------------------------
				if( preg_match('/\{(--\w+)=\*\}/', $pair, $key) > 0 ) {

					foreach ($argv as $i => $val) {

						if( preg_match('/'.$key[1].'=([^\s]+)/', $val, $kmatch) > 0 ) {
							
							$this->opts[$key[1]][] = $kmatch[1];
							unset($argv[$i]);
							
						}

					}

					$argv = array_values($argv);

					continue;

				}
			
			}

		}

	}

	public function exec( $command, $out = NULL ) {

		exec($command, $output);

		if( $out !== NULL ) {

			foreach ($output as $line) {
				$out("\r\n" . $line);
			}

		}

	}

	public function argument($key) {

		return $this->args[$key] ?? NULL;

	}

	public function option($key) {

		return $this->opts[$key] ?? NULL;		

	}

	public function arguments() {

		return $this->args;

	}

	public function options() {

		return $this->opts;		

	}

	public function ask($text) {

		$answer = readline($text . ' ');

		return $answer;

	}

	public function secret($prompt) {

		echo $prompt . ' ';

		$secret = NULL;

		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {	

			// * nix system

			system('stty -echo');
			$secret = trim(fgets(STDIN));
			system('stty echo');

		}else {

			// * windows system

			$bin 	= ROOTDIR . "system/bin/input.exe";

			$secret = trim(shell_exec($bin));

		}

		echo "\r\n";

		return $secret;

	}

	public function line($text) {
		
		echo $text;
		echo "\r\n";

	}

	public function error($text) {

		$ctext 	= '';

		$color 	= '1;31';// light_red

		$ctext .= "\033[" . $color . "m" . $text;

		$ctext .= "\033[0m";

		echo $ctext;
		echo "\r\n";

	}

	public function info($text) {

		$ctext 	= '';

		$color = '1;32';// light_green
	
		$ctext .= "\033[" . $color . "m" . $text;

		$ctext .= "\033[0m";

		echo $ctext;
		echo "\r\n";

	}

}