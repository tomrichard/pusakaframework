<?php 
namespace Pusaka\Easyui;

use Pusaka\Easyui\Lib\Compiler;
use Pusaka\Console\Command;
use Pusaka\Utils\IOUtils;

//include(ROOTDIR . 'app/console/pusaka/easyui/lib/Compiler.php');

class Add extends Command {

	protected $signature 	= 'pusaka\easyui:add {page}';

	protected $description 	= 'Add EasyUI Pages - Pusaka Template Framework';

	public function handle() {

		$tpl 	= ROOTDIR . 'app/console/pusaka/easyui/data/templates/';

		$dir  	= ROOTDIR . 'app/web/www/'; 

		$page 	= $this->argument('page');

		$page 	= strtr($page, ['\\' => '/']);

		$page 	= trim($page, '/');

		$create = $dir . $page;

		// vars 
		//--------------------------

		$base 	= basename($page);

		//--------------------------

		// check directory
		if( is_dir($create) ) {
			
			$this->error("Cannot create page folder {$page} already created.");			
			return;

		}

		// create directory
		IOUtils::directory($create . '/res')->make();

		if( !is_dir($create) ) {

			$this->error("Cannot create folder.");			
			return;

		}

		// create controller
		IOUtils::file($tpl . 'example.cs.tpl')
			->read()
			->replace([
				'<<$Name>>' 	=> ucfirst($base),
				'<<$name>>' 	=> $base,
				'<<$param>>' 	=> $page
			])
			->save($create . '/' . $base . '.cs.php');

		// create view
		IOUtils::file($tpl . 'example.easyui.tpl')
			->read()
			->replace([
				'<<$Name>>' 	=> ucfirst($base),
				'<<$name>>' 	=> $base,
				'<<$param>>' 	=> $page
			])
			->save($create . '/' . $base . '.easyui.php');

		// create javascript
		IOUtils::file($tpl . 'script.tpl')
			->read()
			->replace([
				'<<$Name>>' 	=> ucfirst($base),
				'<<$name>>' 	=> $base,
				'<<$param>>' 	=> $page
			])
			->save($create . '/' . 'res/script.js');


		$this->line($page);

	}

}