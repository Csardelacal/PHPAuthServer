<?php

$data = Array();

#Set the user ID
$data['id']       = $user->_id;

#Set the username
$data['username'] = $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name;

#Set the aliases
$data['aliases'] = Array();
$aliases = $user->usernames->getQuery()->addRestriction('expires', null, 'IS NOT')->addRestriction('expires', time(), '>')->fetchAll();
foreach ($aliases as $alias) { $data['aliases'][] = $alias->name; }

#Find the groups the user belongs to
#We only list public groups here. To prevent private group information from leaking
#The groups that are restricted will require the app to probe an adequate endpoint
$data['groups']  = Array();
$groups  = $user->memberof;

foreach ($groups as $group) { 
	if($group->group->public) { 
		$data['groups'][] = Array(
			'id'   => (int)$group->group->_id,
			'name' => $group->group->name,
			'role' => $group->role
		); 
	} 
}

#Let the application know whether the profile was verified
$data['verified'] = !!$user->verified;

#Define since when the user is a member
$data['registered']      = date('r', $user->created);
$data['registered_unix'] = $user->created;

#If the account was disabled we do add the disabled flag
$data['disabled']  = $user->disabled || ($suspension && $suspension->preventLogin);
$data['suspended'] = !!$suspension;

if ($email) {
	/*
	 * TODO: This needs to use the email contact instead of the field inside the
	 * user table.
	 */
	$data['email'] = $user->email;
}

#Avatar
$data['avatar']          = Array();
$data['avatar']['32']    = (string)url('image', 'user', $user->_id,  32, ['t' => $user->modified])->absolute();
$data['avatar']['64']    = (string)url('image', 'user', $user->_id,  64, ['t' => $user->modified])->absolute();
$data['avatar']['128']   = (string)url('image', 'user', $user->_id, 128, ['t' => $user->modified])->absolute();
$data['avatar']['256']   = (string)url('image', 'user', $user->_id, 256, ['t' => $user->modified])->absolute();

#Get the properties
$data['profile'] = Array();
$data['attributes'] = Array();

foreach ($attributes as $attribute) {
	
	$data['attributes'][$attribute->_id] = [
		'id' => $attribute->_id,
		'name' => $attribute->name,
		'type' => $attribute->datatype,
		'readable' => isset($profile[$attribute->_id])
	];
	
	if(!isset($profile[$attribute->_id])) { continue; } 
	
	$attrValue = $profile[$attribute->_id];
	
	#In the event of a file being provided it requires special treatment.
	if ($attribute->datatype === 'file') { 
		$file = new attribute\io\FileToJson($attrValue); 
		$data['attributes'][$attribute->_id]['value'] = $file->getRaw();
	} 
	#Otherwise we just output it as usual
	else {
		$data['attributes'][$attribute->_id]['value'] = $attrValue? $attrValue->value : null;
	}
}

echo json_encode(Array('payload' => $data));