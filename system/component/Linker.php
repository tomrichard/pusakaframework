<?php 
namespace Pusaka\Component;

// update

class Linker {

	function extract( $url ) {

		$ext 	= strtolower(pathinfo($url, PATHINFO_EXTENSION));

		if($ext == 'js') {
			echo PHP_EOL;
			echo '<script type="text/javascript" src="'.$url.'"></script>';
			echo PHP_EOL;
			return;
		} 

		if($ext == 'css') {
			echo PHP_EOL;
			echo '<link media="screen" rel="stylesheet" type="text/css" href="'.$url.'"/>';
			echo PHP_EOL;
			return;
		}

	}

	function favicon( $url ) {

		$ext 	= strtolower(pathinfo($url, PATHINFO_EXTENSION));

		if(in_array($ext, ['png', 'jpg', 'jpeg', 'ico'])) {

			$mime = [
				'png' 	=> 'image/png',
				'jpg'	=> 'image/jpeg',
				'jpeg'	=> 'image/jpeg',
				'ico'	=> 'image/x-icon'
			];

			$type = $mime[$ext];

			echo PHP_EOL;
			echo '<link rel="icon" type="'.$type.'" href="'.$url.'" />';
			echo PHP_EOL;

			unset($mime);

		}

	}

	function top( $asset ) {

		$url 	= URL . 'static/vendors/';

		$dir 	= ROOTDIR . 'static/';

		$vnd 	= $dir . 'vendors/';

		$links 	= $dir . $asset . '.linker.json';

		if(!file_exists($links)) {
			return;
		}

		$json = json_decode( file_get_contents($links) );

		if(!is_array($json)) {
			return;
		}

		$include = [];

		foreach ($json as $i => $link) {
			
			$vendor 	= $link[0] ?? NULL;
			$version 	= $link[1] ?? NULL;

			$file 		= $vnd . $vendor . '/' . $version . '/' . 'assets.schema.json';
			
			if( !file_exists( $file ) ) {
				continue;
			}
			
			$assets 	= json_decode( file_get_contents($file) );

			foreach ( $assets->top as $asset ) {

				$this->extract( $url . $vendor . '/' . $version . '/' . $asset );

			}


		}

	}

	function bot( $asset ) {

		$url 	= URL . 'static/vendors/';

		$dir 	= ROOTDIR . 'static/';

		$vnd 	= $dir . 'vendors/';

		$links 	= $dir . $asset . '.linker.json';

		if(!file_exists($links)) {
			return;
		}

		$json = json_decode( file_get_contents($links) );

		if(!is_array($json)) {
			return;
		}

		$include = [];

		foreach ($json as $i => $link) {
			
			$vendor 	= $link[0] ?? NULL;
			$version 	= $link[1] ?? NULL;

			$file 		= $vnd . $vendor . '/' . $version . '/' . 'assets.schema.json';
			
			if( !file_exists( $file ) ) {
				continue;
			}
			
			$assets 	= json_decode( file_get_contents($file) );

			foreach ( $assets->bot as $asset ) {

				$this->extract( $url . $vendor . '/' . $version . '/' . $asset );

			}


		}

	}

}