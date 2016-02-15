<?php

echo json_encode(Array(
	 'token'   => $token->token,
	 'expires' => $token->expires
));