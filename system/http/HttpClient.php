<?php 
namespace Pusaka\Http;

class HttpClient {
	
	static function post($options = []) {

		$url 	= $options['url'] ?? NULL;

		$data 	= $options['data'] ?? [];

		$body 	= json_encode($data);

		$ch 	= curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Content-Length: ' . strlen($body)
		]);
		$result = curl_exec($ch);
		
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($status == 200){
			
			$success 	= $options['success'] ?? function(){};

			$success($result);

		}else {
			
			$error 		= $options['error'] ?? function(){};

			$error($result);

		}

	}

	static function get($options = []) {

		$url 		= $options['url'] ?? NULL;

		$data 		= $options['data'] ?? [];

		$encoded 	= '';

		if(!empty($data)) {
			
			$data 	= http_build_query($data);
			$url  	= $url . '?' . $data; 

		}

		$ch 		= curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($ch, CURLOPT_HTTPHEADER, 
		// 	[
		// 		'Content-Type: application/json'
		// 	]
		// );

		$result = curl_exec($ch);
		
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($status == 200) {
			
			$success 	= $options['success'] ?? function(){};

			$success($result);

		} else {
			
			$error 		= $options['error'] ?? function(){};

			$error($result);

		}

	}

}