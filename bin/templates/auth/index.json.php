<?php

$data = Array();

if ($token && $token->user) {
	$data['authenticated'] = true;
	$data['token']         = $token->token;
	$data['expires']       = $token->expires;
	
	$data['user']             = Array();
	$data['user']['id']       = $token->user->_id;
	$data['user']['username'] = $token->user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name;
	$data['user']['verified'] = $token->user->verified;
	$data['user']['disabled'] = $token->user->disabled;
	$data['user']['avatar']   = (string)new AbsoluteURL('image', 'user', $token->user->_id);
} else {
	$data['authenticated'] = false;
}

echo json_encode($data);
