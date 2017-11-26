<?php

$payload = Array(
	 'authenticated' => $authenticated,
	 'grant'         => $grant,
	 'src'           => ['id' => $src->appID, 'name' => $src->name]
);

if ($remote) {
	$payload['remote'] = [
		'id'   => $remote->appID,
		'name' => $remote->name
	];
}

if ($context) {
	$collect = $context instanceof spitfire\core\Collection? $context : collect($context);
	$payload['context'] = $collect->each(function (\auth\Context$c) use ($src, $remote, $token) { 
		return [
			'undefined'   => !$c->getDefined(),
			'granted'     => $src->canAccess($remote, $token->user, $c->getId()),
			'id'          => $c->getId(),
			'name'        => $c->getName(),
			'description' => $c->getDescription(),
			'expires'     => $c->getExpires()
		];
	})->toArray();
} 
elseif ($remote) {
	$payload['context'] = [
		'undefined' => true,
		'id'        => isset($_GET['context'])? $_GET['context'] : null
	];
}

/**
 * @todo This needs some prettying up, the code looks rather messy.
 */
if ((int)$grant === 0 && $src && $remote) {
	$salt     = str_replace(['+', '/'], '', base64_encode(random_bytes(30)));
	$hash     = hash('sha512', implode('.', [$src->appID, $remote->appID, $remote->appSecret, $salt]));
	$returnto = isset($_GET['returnto']) && filter_var($_GET['returnto'], FILTER_VALIDATE_URL)? $_GET['returnto'] : null;
	$redirect = url('auth', 'connect', ['signature' => implode(':', ['sha512', $src->appID, $remote->appID, $salt, $hash]), 'returnto' => $returnto])->absolute();
	$payload['redirect'] = strval($redirect);
}

$payload['token']    = $token->token;
$payload['messages'] = spitfire()->getMessages();
echo json_encode($payload);