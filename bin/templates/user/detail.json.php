<?php

$data = Array();

#Set the user ID
$data['id']       = $profile->_id;

#Set the username
$data['username'] = $profile->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name;

#Set the aliases
$data['aliases'] = Array();
$aliases = $profile->usernames->getQuery()->addRestriction('expires', null, 'IS NOT')->addRestriction('expires', time(), '>')->fetchAll();
foreach ($aliases as $alias) { $data['aliases'][] = $alias->name; }

#Find the groups the user belongs to
#We only list public groups here. To prevent private group information from leaking
#The groups that are restricted will require the app to probe an adequate endpoint
$data['groups']  = Array();
$groups  = $profile->memberof;

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
$data['verified'] = !!$profile->verified;

#Define since when the user is a member
$data['registered']      = date('r', $profile->created);
$data['registered_unix'] = $profile->created;

#If the account was disabled we do add the disabled flag
$data['disabled']         = !!$profile->disabled;

#Avatar
$data['avatar']          = Array();
$data['avatar']['32']    = (string)new absoluteURL('image', 'user', $profile->_id, 32);
$data['avatar']['64']    = (string)new absoluteURL('image', 'user', $profile->_id, 64);
$data['avatar']['128']   = (string)new absoluteURL('image', 'user', $profile->_id, 128);
$data['avatar']['256']   = (string)new absoluteURL('image', 'user', $profile->_id, 256);

#Get the properties
$data['attributes'] = Array();
foreach ($attributes as $attribute) {
	$attrValue = db()->table('user\attribute')->get('user', $profile)->addRestriction('attr', $attribute)->fetch();
	
	#In the event of a file being provided it requires special treatment.
	if ($attribute->datatype === 'file') { 
		$file = new attribute\io\FileToJson($attrValue); 
		$data['attributes'][$attribute->_id] = $file->getRaw();
	} 
	#Otherwise we just output it as usual
	else {
		$data['attributes'][$attribute->_id] = $attrValue? $attrValue->value : null;
	}
}

echo json_encode(Array('payload' => $data));