<?php 
namespace Pusaka\Microservices;

use closure;

use Pusaka\Utils\IOUtils;
use Pusaka\Core\Loader;

use Pusaka\Http\Response;

class Application {

	public $route = [];

	function add($url, $funct, $method = []) {

		$url = '/' . strtr($url, ['/' => '\/']) . '/';

		$url = preg_replace('/\{\w+\}/', '(\w+)', $url);

		$this->route[$url] = [

			'method' => $method,
			'action' => $funct

		];

		unset($url);

	}

	function get($url, $funct) {

		$this->add($url, $funct, $method = ['GET']);

	}

	function post($url, $funct) {

		$this->add($url, $funct, $method = ['POST']);

	}

	function put($url, $funct) {

		$this->add($url, $funct, $method = ['PUT']);

	}

	function delete($url, $funct) {

		$this->add($url, $funct, $method = ['DELETE']);

	}

	function options($url, $funct) {
		
		$this->add($url, $funct, $method = ['OPTIONS']);

	}

	function serve() {

		Router::handle( $this );

	}

}