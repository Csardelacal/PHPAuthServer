<?php


$errors = ob_get_clean();
ob_start();

$payload = Array(
	'tokens'   => [
		'access' => [
			'type'  => 'Bearer',
			'token' => (string)$token,
			'expires' => $token->expires
		],
		'refresh' => [
			'type'  => 'Bearer',
			'token' => $refresh->token,
			'expires' => $refresh->expires
		]
	]
);

if (!empty($errors) && \spitfire\core\Environment::get('debug_mode')) {
	$payload['errors'] = $errors;
}

echo json_encode($payload);