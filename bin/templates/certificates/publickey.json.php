<?php

use jwt\Base64URL;

current_context()->response->getHeaders()->contentType('json');

$_out = [];

foreach($keys as $key) {
	$keyInfo = \openssl_pkey_get_details(\openssl_pkey_get_public($key->public));
	$_out[] = [
		'kty' => 'RSA',
		'use' => 'sig',
		'alg' => 'RS256',
		'n'   => Base64URL::fromString($keyInfo['rsa']['n']),
		'e'   => Base64URL::fromString($keyInfo['rsa']['e']),
		'pem' => $key->public
	];
	
}

echo json_encode([
	'keys' => $_out
]);
