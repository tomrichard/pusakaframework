<?php 
use Pusaka\Hmvc\Controller;

use Pusaka\Http\Request;

class <<$Name>>CS extends Controller {
	
	function index() {

		$file = "<<$param>>";

		$page = "<<$Name>>";

		$this->load->view(NULL, compact('page', 'file'));

	}

}