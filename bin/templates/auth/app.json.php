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
		'id'        => null
	];
}

$payload['token']    = $token->token;
echo json_encode($payload);