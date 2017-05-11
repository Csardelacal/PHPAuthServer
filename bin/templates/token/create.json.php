<?php

echo json_encode(Array(
	 'location'=> (string)new AbsoluteURL('auth', 'oauth', $token->token),
	 'token'   => $token->token,
	 'expires' => $token->expires
));