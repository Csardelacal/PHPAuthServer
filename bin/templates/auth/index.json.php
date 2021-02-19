<?php

$data = Array();

if ($token && $token->owner) {
	$data['authenticated'] = true;
	$data['token']         = $token->token;
	$data['expires']       = $token->expires;
	
	$data['user']              = Array();
	$data['user']['id']        = $token->owner->_id;
	$data['user']['username']  = $token->owner->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name;
	$data['user']['verified']  = $token->owner->verified;
	$data['user']['disabled']  = $token->owner->disabled || ($suspension && $suspension->preventLogin);
	$data['user']['suspended'] = !!$suspension;
	$data['user']['avatar']    = (string)url('image', 'user', $token->owner->_id)->absolute();
	
	$data['app']               = Array();
	$data['app']['id']         = $token->client->appID;
	
	$data['groups']            = Array();
	
	foreach ($token->owner->memberof as $group) {
		$data['groups'][$group->group->_id] = $group->group->name;
	}
} else {
	$data['authenticated'] = false;
}

echo json_encode($data);