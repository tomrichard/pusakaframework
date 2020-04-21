<?php 
namespace Pusaka\Microservices;

use closure;

use Pusaka\Utils\IOUtils;
use Pusaka\Core\Loader;

use Pusaka\Http\Response;

class Application {

	public $route = [];

	function get($url, $funct) {

		$this->route[$url] = [

			'method' => ['GET'],
			'action' => $funct

		];

	}

	function post($url, $funct) {

		$this->route[$url] = [

			'method' => ['POST'],
			'action' => $funct

		];

	}

	function put($url, $funct) {

		$this->route[$url] = [

			'method' => ['PUT'],
			'action' => $funct

		];

	}

	function delete($url, $funct) {

		$this->route[$url] = [

			'method' => ['DELETE'],
			'action' => $funct

		];

	}

	function options($url, $funct) {
		
		$this->route[$url] = [

			'method' => ['OPTIONS'],
			'action' => $funct

		];

	}

	function serve() {

		Router::microservice( $this );

	}

}