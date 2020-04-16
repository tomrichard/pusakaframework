<?php 
namespace Pusaka\Utils;

use Pusaka\Utils\DirectoryUtils;
use Pusaka\Utils\FileUtils;

class IOUtils {

	static function directory($path = NULL) {
		return new DirectoryUtils($path);
	}

	static function file($path = NULL) {
		return new FileUtils($path);
	}

}