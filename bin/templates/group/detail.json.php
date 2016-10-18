<?php 

$members = Array();

foreach ($group->members as $member) {
	$members[] = Array(
		'id'   => $member->user->_id,
		'role' => $member->role
	);
}

$payload = Array(
	'id'      => $group->_id,
	'name'    => $group->name,
	'public'  => $group->public,
	'members' => $members
);

echo json_encode(Array(
	'status'  => 200,
	'payload' => $payload
));
