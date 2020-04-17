<?php 
namespace Pusaka\Utils;

use Pusaka\Exceptions\IOExceptions;
use closure;

class DirectoryUtils {

	private $source;
	private $filter;

	public function __construct($source) {

		$this->source = $source;
		$this->filter = NULL; 

	}

	public function filter($filter) {
		$this->filter = $filter;
	}

	public function scan($deep, $closure, $parent = NULL) {

		if(!is_dir($this->source)) {
			throw new IOExceptions("Directory not found", 6713);
		}

		if($parent == NULL) {
			$parent = $this->source;
		}

		$scan = scandir($parent);

		foreach ($scan as $file) {

			if($file === '.' || $file === '..') {
				continue;
			}

			if($deep) {
				
				if(is_dir(path($parent) . $file)) {
					$this->scan(true, $closure, path($parent) . $file);
				}

				if($this->filter !== NULL) {
					if(preg_match($this->filter, path($parent) . $file) > 0) {
						$closure(path($parent) . $file);	
					}else {
						continue;
					}
				}else {
					$closure(path($parent) . $file);
				}

			}else {
			
				if($this->filter !== NULL) {
					if(preg_match($this->filter, path($parent) . $file) > 0) {
						$closure(path($parent) . $file);	
					}else {
						continue;
					}
				}else {
					$closure(path($parent) . $file);
				}
			
			}

		}

	}

	private function __recurse_copy($src, $dst) {
		
		$dir = opendir($src);
		@mkdir($dst);
		
		while(false !== ( $file = readdir($dir)) ) {
			
			if (( $file != '.' ) && ( $file != '..' )) {
				
				if ( is_dir($src . '/' . $file) ) {
					$this->__recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}

			}

		}

		closedir($dir);

	} 

	public function copy( $dest ) {

		if( is_dir($this->src) ) {

			$this->__recurse_copy( $this->src, $dest );

		}

	}

	public function make() {

		if(is_dir($this->source)) {
			return true;
		}

		return mkdir($this->source, 0777, true);

	}

	// public function __set($property, $value) {

	// 	if(in_array($property, 'error')){
	// 		$this->{$property} = $value;
	// 	}
	
	// }

	// public function __get($property) {

	// 	return $this->{$property};

	// }

	// private function __recursiveDelete($path) {

	// 	$del = $files = glob($path . '/*');
		
	// 	foreach ($files as $file) {
	// 		is_dir($file) ? $this->__recursiveDelete($file) : unlink($file);
	// 	}

	// 	rmdir($path);

	// 	return !is_dir($path);

	// }

	// public function make() {

	// 	if(is_dir($this->src)) {
	// 		return true;
	// 	}

	// 	return mkdir($this->src, true);

	// }

	// public function delete() {

	// 	return $this->__recursiveDelete($this->src);

	// }

}