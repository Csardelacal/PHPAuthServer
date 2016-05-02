<?php

echo json_encode(Array(
	 'location'=> (string)new absoluteURL('auth', 'oauth', $token->token),
	 'token'   => $token->token,
	 'expires' => $token->expires
));