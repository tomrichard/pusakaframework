<?php 
namespace Pusaka\Utils;

use closure;

class ByteUtils {

	const B 	= ['B', 	'Byte'];
	const KB 	= ['KB', 	'Kilo Byte'];
	const MB 	= ['MB', 	'Mega Byte'];
	const GB 	= ['GB', 	'Giga Byte'];
	const TB 	= ['TB', 	'Tera Byte'];

	static function value($byte) {

		if(is_numeric($byte)) {
			return $byte;
		}

		if(!is_string($byte)) {
			return 0;
		}

		preg_match('/^\s*(\d+)\s*(B|KB|MB|TB)\s*$/i', $byte, $match);

		if( isset($match[1]) AND isset($match[2]) ) {
			$base = $match[2];
			$size = $match[1];
		}

		$base  = strtoupper($base);
		
		if(!is_numeric($size)){
			return 0;
		}
		
		if($base == ByteUtils::B[0]){
			$bytes = $size;
			return $bytes;
		}else if($base == ByteUtils::KB[0]){
			$bytes = $size * (1024);
			return $bytes;
		}else if($base == ByteUtils::MB[0]){
			$bytes = $size * (1024*1024);
			return $bytes;
		}else if($base == ByteUtils::GB[0]){
			$bytes = $size * (1024*1024*1024);
			return $bytes;
		}else if($base == ByteUtils::TB[0]){
			$bytes = $size * (1024*1024*1024*1024);
			return $bytes;
		}

		return 0;

	}

	static function string($byte) {

		$string = '';
		$size 	= 0;

		if(is_string($byte)) {
			$size = ByteUtils::value($byte);
		}

		if(is_numeric($byte)) {
			$size = $byte;
		}

		if(!is_numeric($size)) {
			return $string;
		}

		$units = array('B', 'KB', 'MB', 'GB', 'TB');
 
		$bytes 		= $size;
		$precision  = 2;
		$bytes 		= max($bytes, 0);
		$pow 		= floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow 		= min($pow, count($units) - 1);
 
		$bytes 	   /= pow(1024, $pow);
 
		return round($bytes, $precision) . ' ' . $units[$pow];

	}

}