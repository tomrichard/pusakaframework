<?php 
namespace Pusaka\Utils;

use Exception;

class DateUtils {

	private $y = '0';
	private $m = '0';
	private $d = '0';
	private $h = '00';
	private $i = '00';
	private $s = '00';

	public function __construct() {

	}

	static function now() {

		if(function_exists('config')) {

			$timezone = config('date')['timezone'] ?? NULL;

			if($timezone !== NULL) {
				date_default_timezone_set($timezone);
			}

		}

		$f 		= explode(';', date('Y;m;d;H;i;s'));

		$Date 	= new DateUtils();

		$Date->create($f[0], $f[1], $f[2], $f[3], $f[4], $f[5]);

		return $Date;

	}

	public function create($y, $m, $d, $h = '00', $i = '00', $s = '00') {
		
		$this->y = $y;
		$this->m = $m;
		$this->d = $d;
		$this->h = $h;
		$this->i = $i;
		$this->s = $s;

	}

	public function createFromString( $format ) {

		if( preg_match('/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/', $format, $f) > 0 ) {
			
			$this->y = $f[1];
			$this->m = $f[2];
			$this->d = $f[3];
			$this->h = $f[4];
			$this->i = $f[5];
			$this->s = $f[6];
		
		}

	}

	public function setTime($time) {
		
		if(preg_match('/(\d{2}):(\d{2}):(\d{2})/', $time, $f) > 0) {
			$this->h = $f[1];
			$this->i = $f[2];
			$this->s = $f[3];
		}else {
			throw new Exception('time invalid format. ex. 00:00:00 | give ' . $time);
		}

		return $this;

	}

	public function setSecond($time) {
		
		if(preg_match('/(\d{2})/', $time, $f) > 0) {
			$this->s = $f[1];
		}else {
			throw new Exception('time invalid format. ex. 00 | give ' . $time);
		}

		return $this;

	}

	public function add($num, $format) {

		if( !is_int($num) ) {
			throw new Exception('$num must be an integer.');
		}

		if( strtolower($format) == 'day' ) {

			$f = date('Y-m-d H:i:s', strtotime("+$num day", strtotime($this->getFull())));

			$this->createFromString($f);
		
			return $this;

		}

		if( strtolower($format) == 'minute' ) {

			$f = date('Y-m-d H:i:s', strtotime("+$num minute", strtotime($this->getFull())));

			$this->createFromString($f);

			return $this;

		}

		return $this;

	}

	public function toSeconds() {

		return strtotime($this->getFull());

	}

	public function getFull() {

		return implode('-', [$this->y, $this->m, $this->d]) . ' ' . implode(':', [$this->h, $this->i, $this->s]);		

	}

	public function getMinute() {

		return (int) $this->i;

	}



}