<?php 
namespace Pusaka\Library;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;

use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\JWSBuilder;

use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\Serializer\CompactSerializer;

use stdClass;

class Auth {

	private $key 		= NULL;
	
	private $payload 	= [];

	public function __construct() {
	
		$this->key = config('security')['key'];
	
	}

	public function explode( $token, $format ) {

		$tokens = explode(' ', $token);

		$match 	= $tokens[0] ?? NULL;
		$token 	= $tokens[1] ?? NULL;

		if($format == $match) {
			return $token;
		}

		return NULL;

	}

	public function create($payload = []) {
		
		if($payload instanceof stdClass) {
			$payload = json_decode(json_encode($payload), true);
		}

		if(!is_array($payload)) {
			throw new Exceptions('Payload must be an array.');
		}


		// Create Token
		// --------------------------------------------------------------
		$algorithmManager = new AlgorithmManager([
		    new HS256()
		]);

		// Our key.
		$jwk = new JWK([
			'kty' 	=> 'oct',
			'k' 	=> $this->key,
		]);

		// We instantiate our JWS Builder.
		$jwsBuilder = new JWSBuilder($algorithmManager);

		// The payload we want to sign. The payload MUST be a string hence we use our JSON Converter.
		$payload = json_encode(array_merge([
			'iat' => time(),
			'nbf' => time(),
			'exp' => time() + 3600,
			'iss' => 'Pusaka_Auth',
			'aud' => 'PusakaJwtSignatureAuth',
		], $payload));

		$jws 		= 	
			$jwsBuilder
				->create()
				->withPayload($payload)
				->addSignature($jwk, ['alg' => 'HS256'])
				->build();	

		$serializer = new CompactSerializer(); // The serializer

		$token 		= $serializer->serialize($jws, 0); // We serialize the signature at index 0 (we only have one signature).
		// --------------------------------------------------------------

		return $token;

	}

	public function isVerified($token) {

		// Our key.
		$jwk = new JWK([
			'kty' 	=> 'oct',
			'k' 	=> $this->key,
		]);

		// Verify Token
		// --------------------------------------------------------------
		$algorithmManager = new AlgorithmManager([
		    new HS256()
		]);

		$jwsVerifier = new JWSVerifier($algorithmManager);

		// The serializer manager. We only use the JWS Compact Serialization Mode.
		$serializerManager = new JWSSerializerManager([
			new CompactSerializer(),
		]);

		// We try to load the token.
		$jws 		 = $serializerManager->unserialize($token);

		$isVerified  = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

		return $isVerified;

	}

	public function payload($token) {

		// Create Token
		// --------------------------------------------------------------
		$algorithmManager = new AlgorithmManager([
		    new HS256()
		]);

		$jwsVerifier = new JWSVerifier($algorithmManager);

		// The serializer manager. We only use the JWS Compact Serialization Mode.
		$serializerManager = new JWSSerializerManager([
			new CompactSerializer(),
		]);

		// We try to load the token.
		$jws 		 = $serializerManager->unserialize($token);

		return $jws->getPayload();

	}

}