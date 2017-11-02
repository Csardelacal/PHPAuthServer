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
		'name'        => $context->name,
		'description' => $context->description,
		'expires'     => $context->expires
	];
} 
elseif ($remote) {
	$payload['context'] = [
		'undefined' => true
	];
}

echo json_encode($payload);