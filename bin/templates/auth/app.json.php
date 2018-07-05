<?php

$errors = ob_get_clean();
ob_start();

$payload = Array(
	 'authenticated' => $authenticated,
	 'grant'         => $grant,
	 'src'           => ['id' => $src->appID, 'name' => $src->name, 'deprecated' => true],
	 'local'         => ['id' => $src->appID, 'name' => $src->name]
);

if ($remote) {
	$payload['remote'] = [
		'id'   => $remote->appID,
		'name' => $remote->name
	];
}

if (!empty($errors) && \spitfire\core\Environment::get('debug_mode')) {
	$payload['errors'] = $errors;
}

if ($context) {
	$collect = $context instanceof spitfire\core\Collection? $context : collect($context);
	$payload['context'] = $collect->each(function (\auth\Context$c) use ($grant) { 
		return [
			'undefined'   => !$c->getDefined(),
			'granted'     => $grant[$c->getId()],
			'id'          => $c->getId(),
			'name'        => $c->getName(),
			'description' => $c->getDescription(),
			'expires'     => $c->getExpires()
		];
	})->toArray();
} 
elseif ($remote) {
	$payload['context'] = [];
}

$payload['token']    = $token? $token->token : null;
echo json_encode($payload);