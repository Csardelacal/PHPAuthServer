<?php


$errors = ob_get_clean();
ob_start();

$payload = Array(
	 'location'=> (string)url('auth', 'oauth', $token->token)->absolute(),
	 'token'   => $token->token,
	 'expires' => $token->expires
);

if (!empty($errors) && \spitfire\core\Environment::get('debug_mode')) {
	$payload['errors'] = $errors;
}

echo json_encode($payload);