<?php 
namespace Pusaka\Http;

class Header {

	function compile( $comments ) {

		$headers = [];

		$comments = explode("\n", implode("\n", $comments) );

		foreach( $comments as $comment ) {

			preg_match('/@header\s\["([^"]+)",\s+"([^"].+)"\]/', $comment, $match);

			if( count($match) === 3 ) {
				$headers[trim($match[1])] = trim($match[2]);
			}

			preg_match('/@method\s(.*)/', $comment, $match);

			if( count($match) > 0 ) {
				$headers['Access-Control-Allow-Methods'] 	= trim($match[1]);	
			}

			preg_match('/@origin\s(.*)/', $comment, $match);

			if( count($match) > 0 ) {
				$headers['Access-Control-Allow-Origin'] 	= trim($match[1]);	
			}

		}

		foreach ($headers as $key => $val) {
			
			header($key . ': ' . $val);

		}

	}

}