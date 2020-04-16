<?php 
use Pusaka\Core\Exceptions;

ob_start();

set_exception_handler('exception_handler');

register_shutdown_function( "shutdown_handler" );

function exception_handler($e) {

	ob_end_clean();

	$type   	= $e->getCode();
	$file 		= $e->getFile();
	$line 		= $e->getLine();
	$message 	= $e->getMessage();
	$stacktrace = $e->getTraceAsString();
	$hint 		= '';

	$file 		= strtr($file, '\\', '/');

	$protocol 	= 'subl:';


	if(EDITOR === 'vscode') {
		$protocol 	= 'vscode://file/';			
	}

	if(ENVIRONMENT === 'PRODUCTION') {
		
		if($type != 0) {
			$fileload = ROOTDIR . 'storage/error/404.php';
			file_exists($fileload) ? require_once($fileload) : die('Error found.');				
		}else { 
			$fileload = ROOTDIR . 'storage/error/500.php';
			file_exists($fileload) ? require_once($fileload) : die('Error found.');
		}

		exit();

	}

	if($type === 0) {
		// uncaught exception
		$hint 	 = \Pusaka\Wapps\FixingError::external($message);

		$message = \Pusaka\Wapps\FixingError::link(0, $message);
	}

	$stacktrace = strtr($stacktrace, ["\n" => "\n\n"] );

	$stacktrace = preg_replace('/#0.*/', '<b class="code-error">$0</b>', $stacktrace);

	$scripts 	= '';

	$_now_line 	= 1;

	$fn = fopen($file,"r");
	while(!feof($fn))  {
		$result 	= fgets($fn);
		if($_now_line == $line) {
			$scripts .= '<b class="code-error">';
		}
		$scripts   .= str_pad($_now_line, 4, '0', STR_PAD_LEFT) . ' => ' . $result;
		if($_now_line == $line) {
			$scripts .= '</b>';
		}

		$_now_line++;
	}
	fclose($fn);

	if(file_exists($exception = ROOTDIR . 'storage/error/Exceptions.php')) {
		require_once($exception);
	}else {
		echo '<pre>';
		print_r($e->__toString());
		echo '</pre>';
	}

	exit();

}

function shutdown_handler() {

	$errfile = "unknown file";
	$errstr  = "shutdown";
	$errno   = E_CORE_ERROR;
	$errline = 0;

	$hint 	 = '';

	$error 	 = error_get_last();

	if( $error !== NULL) {

		$etype = [
			E_ERROR 			=> 'ERROR',
			E_WARNING 			=> 'WARNING',
			E_PARSE 			=> 'PARSE',
			E_NOTICE 			=> 'NOTICE',
			E_CORE_ERROR 		=> 'CORE_ERROR',
			E_CORE_WARNING 		=> 'CORE_WARNING',
			E_COMPILE_ERROR 	=> 'COMPILE_ERROR',
			E_COMPILE_WARNING 	=> 'COMPILE_WARNING',
			E_USER_ERROR 		=> 'USER_ERROR',
			E_USER_WARNING 		=> 'USER_WARNING',
			E_USER_NOTICE 		=> 'USER_NOTICE',
			E_STRICT 			=> 'STRICT',
			E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
			E_DEPRECATED 		=> 'DEPRECATED',
			E_USER_DEPRECATED 	=> 'USER_DEPRECATED'
		];

		$type   	= $etype[$error["type"]];
		$file 		= $error["file"];
		$line 		= $error["line"];
		$message 	= $error["message"];
		$message  	= preg_replace('/\sin\s/', "\r\n\tin ", $message);
		$message  	= preg_replace('/\s(#\d+)/', "\r\n\t$1", $message);
		$message  	= preg_replace('/\s(#\d+)/', "\r\n\t$1", $message);
		$protocol 	= 'subl:';

		if(EDITOR === 'vscode') {
			$protocol 	= 'vscode:';			
		}

		ob_end_clean();

		if(ENVIRONMENT === 'PRODUCTION') {

			$contents = "File : ".$file."\r\n"."Line : ".$line."\r\n".$message;

			if(file_exists($exception = ROOTDIR . 'storage/error/Exceptions.php')) {
				ob_start();
				require_once($exception);
				$contents = ob_get_contents();
				ob_end_clean();
			}

			if(is_dir(LOG)) {
				$savefile = rtrim('/', LOG) . '/error_log_' . date('ymdHi') . uniqid() . '.html';
				$fh 	  = fopen($savefile, 'w');
				fwrite($fh, $contents);
				fclose($fh);
			} 
			
			switch($type) {
				case 'ERROR' : 
					$fileload = ROOTDIR . 'storage/error/404.php';
					file_exists($fileload) ? require_once($fileload) : '';				
					break;
				case 'PARSE' : 
					$fileload = ROOTDIR . 'storage/error/500.php';
					file_exists($fileload) ? require_once($fileload) : '';
					break;
				default :
					$fileload = ROOTDIR . 'storage/error/500.php';
					file_exists($fileload) ? require_once($fileload) : '';
					break;
			}

			exit();

		}

		if(file_exists($exception = ROOTDIR . 'storage/error/Exceptions.php')) {
			require_once($exception);
		}else {
			echo '<pre>';
			print_r($error);
			echo '</pre>';
		}

	}

}