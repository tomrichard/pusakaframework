<?php 
namespace Pusaka\Utils;

use Pusaka\Exceptions\IOExceptions;
use closure;

class FileUtils {

	const FORM 	= 'FORM';
	const PATH 	= 'PATH';

	private $source;
	private $type;
	private $config;
	private $location;
	private $overwrite;
	private $contents;

	public function __construct($source) {

		$this->overwrite = false;

		if(is_array($source) && isset($source['tmp_name'])) {
			$this->type = self::FORM;
		}else {
			$this->type = self::PATH;
		}

		$this->source = $source;

	}

	public function __get($key) {

		if(in_array($key, ['location', 'link'])) {
			return $this->{$key};
		}

		throw new ClassExceptions("Property cannot be access.", 4001);

	}

	public function src() {
		return $this->source;
	}

	public function write($text) {

		return file_put_contents($this->source, $text);

	}

	public function overwrite() {
		$this->overwrite = true;
	}

	public function config($key) {

		$config = config('upload');

		if(!isset($config[$key])) {
			throw new IOExceptions("Config [file:$key] not found", 6701);
		}

		$this->config = $config[$key];

	}

	public function read() {

		$this->contents = file_get_contents($this->source);

		return $this;

	}

	public function replace($replace) {


		$this->contents = strtr($this->contents, $replace);

		return $this;

	}

	public function save($save_as = NULL) {

		// source is NULL
		if($this->source === NULL) {
			throw new IOExceptions("File is NULL", 6702);
		}

		$config = $this->config;

		// if type is Absolute PATH | Url
		if($this->type === self::PATH) {
			
			$is_url = false;

			if(preg_match('/https?:\/\//', $this->source) > 0) {
				$is_url = true;
			}

			if(!file_exists($this->source) && !$is_url) {
				throw new IOExceptions("File not found or selected.", 6705);	
			}

			if(is_string($save_as)) {
				
				// copy to $save_as
				
				$save_dir 	= dirname($save_as);
				
				if(!is_dir($save_dir)) {
					throw new IOExceptions("Destination directory [$save_dir] not found.", 6707);
				}

				if(file_exists($save_as) && !$this->overwrite) {
		 			throw new IOExceptions("File already exist, cannot overwrite the file.", 6709);
		 		}

				/*
				| Error when upload
				|-------------------------------------- */
		 		$content = $this->contents;

		 		if(!file_put_contents($save_as, $content)) {
		 			throw new IOExceptions("Copy or upload failed.", 6710);
		 		}

		 		if(!file_exists($save_as)) {
		 			throw new IOExceptions("Copy or upload failed.", 6710);
		 		}

		 		$this->source 	= $save_as;

				return true;
			}

			if($save_as === NULL) {

				/*
				| Destination in config not found
				|-------------------------------------- */
				if(!isset($config['save'])) {
					throw new IOExceptions("Destination config[save] not found.", 6703);
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
					throw new IOExceptions("Destination directory [$save_dir] not found.", 6707);	
				}

				if(file_exists($save_as) && !$this->overwrite) {
		 			throw new IOExceptions("File already exist, cannot overwrite the file.", 6709);
		 		}

				/*
				| Error when upload
				|-------------------------------------- */
		 		if(!copy($this->source, $save_as)) {
		 			throw new IOExceptions("Copy or upload failed.", 6710);
		 		}

		 		if(!file_exists($save_as)) {
		 			throw new IOExceptions("Copy or upload failed.", 6710);
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
				throw new IOExceptions("Destination config[save] not found.", 6703);
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
				throw new IOExceptions("Form error [$errcode].", 6704);
			}
			
			/*
			| File not selected
			|-------------------------------------- */
			if($file['name'] === '') {
				throw new IOExceptions("File not found or selected.", 6705);	
			}

			$ext 			= strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

			/*
			| Extension not allowed
			|-------------------------------------- */
			if(!in_array($ext, $allowed)) {
				throw new IOExceptions("Extension [.$ext] not allowed.", 6706);	
			}

			$file['name'] 	= strtr($file['name'], $remove); 

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
				throw new IOExceptions("Destination directory [$save_dir] not found.", 6707);	
			}

			/*
			| Oversize
			|-------------------------------------- */
	 		if($file['size'] > ByteUtils::value($config['max']) ) {
	 			throw new IOExceptions("Oversize maximum size is ".$config['max'].'.', 6708);
	 		}

	 		$tmp 		= $file['tmp_name'];

	 		if(file_exists($save_as) && !$this->overwrite) {
	 			throw new IOExceptions("File already exist, cannot overwrite the file.", 6709);
	 		}

	 		/*
			| Error when upload
			|-------------------------------------- */
	 		if(!move_uploaded_file($tmp, $save_as)) {
	 			throw new IOExceptions("Copy or upload failed.", 6710);
	 		}

	 		if(!file_exists($save_as)) {
	 			throw new IOExceptions("Copy or upload failed.", 6710);
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
			throw new IOExceptions("Source must be string path.", 6711);
		}

		if(!file_exists($this->source)) {
			throw new IOExceptions("File not found or selected.", 6705);	
		}

		if(!unlink($this->source)) {
			throw new IOExceptions("Delete failed.", 6712);	
		}

		return true;

	}

}