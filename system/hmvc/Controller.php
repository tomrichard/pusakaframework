<?php 
namespace Pusaka\Hmvc;

use Pusaka\Core\Loader;

class Controller {
	
	protected $fragment;
	protected $load;
	protected $auth;

	public function __construct($env) {

		if(isset($env['loader'])) {
			$this->load = $env['loader'];
		}else {
			$this->load = new Loader($env);
		}

		if(isset($env['auth'])) {
			$this->auth = $env['auth'];
		}

		if(isset($env['fragment'])) {
			$this->fragment = $env['fragment'];
		}
		
	}

}