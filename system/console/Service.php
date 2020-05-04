<?php 
namespace Pusaka\Console;

class Service {
	
	private $id;
	private $path;
	private $script;
	private $saveas;

	function __construct($module) {

		$this->id 	= $module->id ?? '';
		$this->path = $module->path ?? '';
	}

	function javascript($callback) {
		
		$this->saveas = ROOTDIR . 'app/service/javascript/' . $this->id . '.javascript.service.php';

		$callback($this);

		$this->script = strtr($this->script, [
			'@module.path' => $this->path
		]);

		$this->create();

	}

	function load($file) {

		if(!file_exists($file)) {
			return $this;
		}

		$this->script = file_get_contents($file);

		return $this;

	}

	function create() {

		if(empty($this->id)) {
			throw new \Exception('Id cannot be NULL');
		}

		try {
			file_put_contents($this->saveas, $this->script);
		}catch(\Exception $e) {
			echo $e->getMessage();
		}

	}

}