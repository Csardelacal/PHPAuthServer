<?php

$data = Array();

if ($token && $token->user) {
	$data['authenticated'] = true;
	$data['token']         = $token->token;
	$data['expires']       = $token->expires;
	
	$data['user']              = Array();
	$data['user']['id']        = $token->user->_id;
	$data['user']['username']  = $token->user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name;
	$data['user']['verified']  = $token->user->verified;
	$data['user']['disabled']  = $token->user->disabled || ($suspension && $suspension->preventsLogin);
	$data['user']['suspended'] = ($suspension && !$suspension->preventsLogin);
	$data['user']['avatar']    = (string)url('image', 'user', $token->user->_id)->absolute();
	
	$data['app']               = Array();
	$data['app']['id']         = $token->app->appID;
	
	$data['groups']            = Array();
	
	foreach ($token->user->memberof as $group) {
		$data['groups'][$group->group->_id] = $group->group->name;
	}
} else {
	$data['authenticated'] = false;
}

echo json_encode($data);