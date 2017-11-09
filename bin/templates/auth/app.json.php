<?php

$payload = Array(
	 'authenticated' => $authenticated,
	 'grant'         => $grant
);

if ($remote) {
	$payload['remote'] = [
		'id'   => $remote->appID,
		'name' => $remote->name
	];
}

if ($context) {
	$payload['context'] = [
		'undefined'   => false,
		'name'        => $context->title,
		'description' => $context->descr,
		'expires'     => $context->expires
	];
} 
elseif ($remote) {
	$payload['context'] = [
		'undefined' => true
	];
}

/**
 * @todo This needs some prettying up, the code looks rather messy.
 */
if ((int)$grant === 0 && $src && $remote) {
	$salt     = str_replace(['+', '/'], '', base64_encode(random_bytes(30)));
	$hash     = hash('sha512', implode('.', [$src->appID, $remote->appID, $remote->appSecret, $context->ctx, $salt]));
	$returnto = isset($_GET['returnto']) && filter_var($_GET['returnto'], FILTER_VALIDATE_URL)? $_GET['returnto'] : null;
	$redirect = url('auth', 'connect', ['signature' => implode(':', ['sha512', $src->appID, $remote->appID, $context->ctx, $salt, $hash]), 'returnto' => $returnto])->absolute();
	$payload['redirect'] = strval($redirect);
}

echo json_encode($payload);