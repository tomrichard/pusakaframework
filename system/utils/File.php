<?php 
namespace Pusaka\Utils;

use Pusaka\Exceptions\IOException;
use closure;

class FileUtils {

	const FORM 	= 'FORM';
	const PATH 	= 'PATH';

	private $source;
	private $name;
	private $size;
	private $extension;
	private $mime;

	private $type;
	private $config;
	private $location;
	private $overwrite;
	private $contents;
	private $auto;

	public function __construct($source) {

		$this->overwrite = false;
		$this->auto 	 = false;

		if(is_array($source) && isset($source['tmp_name'])) {
			
			$this->type = self::FORM;

			$this->name 		= $source['name'] ?? '';
			$this->size 		= $source['size'] ?? 0;
			$this->extension 	= pathinfo($source['name'], PATHINFO_EXTENSION);
			$this->mime 		= '';

		}else {

			$this->type = self::PATH;

			$this->name 		= basename($source);
			$this->size 		= @filesize($source);
			$this->extension 	= pathinfo($source, PATHINFO_EXTENSION);
			$this->mime 		= @mime_content_type($source);

		}

		$this->source = $source;

	}

	public function __get($key) {

		if(in_array($key, ['location', 'link', 'type', 'name'])) {
			return $this->{$key};
		}

		var_dump($key);

		throw new ClassExceptions("Property cannot be access.", 4001);

	}

	public function seo() {

		$string = strtolower(basename($this->name, '.' . $this->extension));

		$string = trim($string);

		$string = strtr($string, [' ' => '_']);

		$string = preg_replace('/\W/', '', $string);

		$string = strtr($string, ['_' => '-']);

		$string = preg_replace('/\s+/', '-', $string);

		return date('Y-m-d-H-i-s-') . $string;
	
	}

	public function src() {
		return $this->source;
	}

	public function auto() {
		return $this->auto = true;
	}

	public function write($text) {

		return file_put_contents($this->source, $text);

	}

	public function overwrite() {
		$this->overwrite = true;
	}

	public function config($key, $overwrite = NULL) {

		$config = config('upload');

		if(!isset($config[$key])) {
			throw new IOException("Config [file:$key] not found", 6701);
		}

		$use 	= $config[$key];

		if( $overwrite instanceof closure) {
			
			$overwrite( $use );

		}

		$this->config = $use;

	}

	public function read() {

		$this->contents = file_get_contents($this->source);

		return $this;

	}

	public function replace($replace) {


		$this->contents = strtr($this->contents, $replace);

		return $this;

	}

	public function attributes() {

		return [
			'mime' 	=> $this->mime,
			'size'	=> $this->size,
			'name' 	=> $this->name,
			'file' 	=> $this->source,
			'ext'  	=> $this->extension
		];

	}

	public function json($farray = FALSE) {

		if(!is_string($this->source)) {
			throw new IOException("File not found or selected.", 404);
		}

		if(!file_exists($this->source) && !$is_url) {
			throw new IOException("File not found or selected.", 404);	
		}

		$content = file_get_contents($this->source);

		return json_decode( $content, $farray );

	}

	public function save($save_as = NULL) {

		// source is NULL
		if($this->source === NULL) {
			throw new IOException("File is NULL", 6702);
		}

		$config = $this->config;

		// if type is Absolute PATH | Url
		if($this->type === self::PATH) {

			$is_url = false;

			if(preg_match('/https?:\/\//', $this->source) > 0) {
				$is_url = true;
			}

			if(!file_exists($this->source) && !$is_url) {
				throw new IOException("File not found or selected.", 6705);	
			}

			if(is_string($save_as)) {
				
				// copy to $save_as
				
				$save_dir 	= dirname($save_as);
				
				if(!is_dir($save_dir)) {
					throw new IOException("Destination directory [$save_dir] not found.", 6707);
				}

				if(file_exists($save_as) && !$this->overwrite) {
		 			throw new IOException("File already exist, cannot overwrite the file.", 6709);
		 		}

				/*
				| Error when upload
				|-------------------------------------- */
		 		$content = $this->contents;

		 		if(!file_put_contents($save_as, $content)) {
		 			throw new IOException("Copy or upload failed.", 6710);
		 		}

		 		if(!file_exists($save_as)) {
		 			throw new IOException("Copy or upload failed.", 6710);
		 		}

		 		$this->source 	= $save_as;

				return true;
			}

			if($save_as === NULL) {

				/*
				| Destination in config not found
				|-------------------------------------- */
				if(!isset($config['save'])) {
					throw new IOException("Destination config[save] not found.", 6703);
				}

				$save_as 	= strtr($config['save'], [
					'@root' 	=> ROOTDIR,
					'@filename' => basename($this->source)
				]);

				$save_as 	= strtr($save_as, ['//' => '/']);

				$save_dir 	= dirname($save_as);

				/*
				| Destination folder not found
				|-------------------------------------- */
				if(!is_dir($save_dir)) {
					throw new IOException("Destination directory [$save_dir] not found.", 6707);	
				}

				if(file_exists($save_as) && !$this->overwrite) {
		 			throw new IOException("File already exist, cannot overwrite the file.", 6709);
		 		}

				/*
				| Error when upload
				|-------------------------------------- */
		 		if(!copy($this->source, $save_as)) {
		 			throw new IOException("Copy or upload failed.", 6710);
		 		}

		 		if(!file_exists($save_as)) {
		 			throw new IOException("Copy or upload failed.", 6710);
		 		}

		 		$this->source 	= $save_as;

				return true;

			}

			// cannot copy
			return false;

		}

		if($this->type === self::FORM) {


			$remove 	= ['#' => ''];

			$allowed 	= explode('|', strtr($config['allowed'], [' '=>'']));

			/*
			| Destination in config not found
			|-------------------------------------- */
			if($save_as === NULL && !isset($config['save'])) {
				throw new IOException("Destination config[save] not found.", 6703);
			}


			$file = [
				'name' 		=> $this->source['name'] ?? '',
				'type' 		=> $this->source['type'] ?? '',
				'tmp_name' 	=> $this->source['tmp_name'] ?? '',
				'error' 	=> $this->source['error'] ?? '',
				'size' 		=> $this->source['size'] ?? ''
			];

			/*
			| Form error
			|-------------------------------------- */
			if($file['error'] !== 0) {
				$errcode = $this->source['error'];
				throw new IOException("Form error [$errcode].", 6704);
			}

			/*
			| File not selected
			|-------------------------------------- */
			if($file['name'] === '') {
				throw new IOException("File not found or selected.", 6705);	
			}

			$ext 			= strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

			/*
			| Extension not allowed
			|-------------------------------------- */
			if(!in_array($ext, $allowed)) {
				throw new IOException("Extension [.$ext] not allowed.", 6706);	
			}

			$file['name'] 	= strtr($file['name'], $remove); 

			if($this->auto) {
				$file['name'] = date('ymdhis').uniqid() . '.' . $ext;
			}

			if($save_as === NULL) {
				$save_as 	= strtr($config['save'], [
					'@root' 	=> ROOTDIR,
					'@filename' => $file['name']
				]);
			}

			$save_as 	= strtr($save_as, ['//' => '/']);

			$save_dir 	= dirname($save_as);

			/*
			| Destination folder not found
			|-------------------------------------- */
			if(!is_dir($save_dir)) {
				throw new IOException("Destination directory [$save_dir] not found.", 6707);	
			die('error');
			}

			/*
			| Oversize
			|-------------------------------------- */
	 		if($file['size'] > ByteUtils::value($config['max']) ) {
	 			throw new IOException("Oversize maximum size is ".$config['max'].'.', 6708);
	 		}

	 		$tmp 		= $file['tmp_name'];

	 		if(file_exists($save_as) && !$this->overwrite) {
	 			throw new IOException("File already exist, cannot overwrite the file.", 6709);
	 		}

	 		/*
			| Error when upload
			|-------------------------------------- */
	 		if(!move_uploaded_file($tmp, $save_as)) {
	 			throw new IOException("Copy or upload failed.", 6710);
	 		}

	 		if(!file_exists($save_as)) {
	 			throw new IOException("Copy or upload failed.", 6710);
	 		}

	 		$this->link 	= strtr($config['link'], ['@filename' => ($file['name'])]);
	 		$this->location = $save_as;

	 		$this->source 	= $save_as;

	 		$this->type 	= self::PATH; 

	 		return true;

		}

		return false;

	}

	public function delete() {

		if(!is_string($this->source)) {
			throw new IOException("Source must be string path.", 6711);
		}

		if(!file_exists($this->source)) {
			throw new IOException("File not found or selected.", 6705);	
		}

		if(!unlink($this->source)) {
			throw new IOException("Delete failed.", 6712);	
		}

		return true;

	}

}