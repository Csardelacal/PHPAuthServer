<?php

$data = Array();

#Set the user ID
$data['id']       = $profile->_id;

#Set the username
$data['username'] = $profile->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name;

#Set the aliases
$data['aliases'] = Array();
$aliases = $profile->usernames->getQuery()->addRestriction('expires', null, 'IS NOT')->fetchAll();
foreach ($aliases as $alias) { $data['aliases'][] = $alias->name; }

#Define since when the user is a member
$data['registered']      = date('r', $profile->created);
$data['registered_unix'] = $profile->created;

#Get the properties
$data['attributes'] = Array();
foreach ($attributes as $attribute) {
	$attrValue = db()->table('user\attribute')->get('user', $profile)->addRestriction('attr', $attribute)->fetch();
	$data['attributes'][$attribute->_id] = $attrValue? $attrValue->value : null;
}

echo json_encode(Array('payload' => $data));

var_dump($permissions);