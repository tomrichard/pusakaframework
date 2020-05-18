<?php 
use Pusaka\Hmvc\Controller;

use Pusaka\Http\Request;
use Pusaka\Http\Middleware;

use Pusaka\Library\Datatable;

use App\Model\User;

use Pusaka\Database\Manager;

class WelcomeCS extends Controller {

	function index() {

		$benchmark = Pusaka\Core\Benchmark::class;

		$this->load->view('layout', compact('benchmark'));
	
	}

}