<?php 
namespace Pusaka\Http;

use Pusaka\Core\Injection;
use Pusaka\Utils\FileUtils;

use stdClass;

class Response {

	static function json($array) {
		header('Content-Type: application/json');
		echo json_encode($array);
	}

	static function xml($array) {
	}

	static function html($string) {
		header('Content-Type: text/html');
		echo $string;
	}

	static function text($string) {
		header('Content-Type: text/plain');
		if(is_string($string)) {
			echo $string;
		}else if(is_array($string)) {
			echo json_encode($string);
		}else {
			echo $string;
		}
	}

	static function redirect($url) {
		header("Location: $url");
	}

	static function image($file) {

		header('Content-Type: '.$file['mime']);
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header("Cache-Control: private", false);
		header('Pragma: public');
		header('Content-Length: ' . filesize($file['file']));
		readfile($file['file']);

	}

	static function download($file) {

		header('Content-Description: File Transfer');
		header('Content-Type: '.$file['mime']);
		header('Content-Disposition: attachment; filename="'.$file['name'].'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header("Cache-Control: private", false);
		header('Pragma: public');
		header('Content-Length: ' . filesize($file['file']));
		readfile($file['file']);

	}

	static function file($File) {

		$image = array('gif','jpg','jpeg','png');
		
		if($File instanceof FileUtils) {

			if($File->category() == FileUtils::FORM) {

				$src = $File->src();

				if(isset($src['tmp_name'])) {
					if(is_string($src['tmp_name'])) {
						
						if($src['tmp_name'] === '') {
							return;
						}

						$file = [
							'mime' => $src['type'],
							'name' => $src['name'],
							'file' => $src['tmp_name'],
							'ext'  => strtolower(pathinfo($src['name'], PATHINFO_EXTENSION))
						];
						
						if(in_array($file['ext'], $image)) {
							Response::image($file);
							return;
						}

						Response::download($file);

						unset($file);
						unset($File);

						return;	
					}
				}

				unset($File);
				return;
			}

			if($File->category() == FileUtils::FILE) {

				$file = [
							'mime' => $File->mime(),
							'name' => $File->name(),
							'file' => $File->src(),
							'ext'  => strtolower(pathinfo($File->name(), PATHINFO_EXTENSION))
						];

				if(!file_exists($file['file'])) {
					echo 'file not found';
					unset($file);
					unset($File);
					return;
				}

				if(in_array($file['ext'], $image)) {
					Response::image($file);
					unset($file);
					unset($File);
					return;
				}

				Response::download($file);

				unset($File);
				return;

			}

		}

		unset($File);
		echo 'Object is not file.';

	}

	static function out($obj) {

		if(is_array($obj)) {
			return Response::json($obj);
		}

		if($obj instanceof stdClass) {
			return Response::json($obj);	
		}

		if($obj instanceof FileUtils) {
			return Response::file($obj);
		}

		if(is_string($obj)) {
			return Response::html($obj);
		}

	}

	static function http($code, $message = '') {

		http_response_code($code);
		die($message);

	}

	static function header() {

		return new Header();

	}

}