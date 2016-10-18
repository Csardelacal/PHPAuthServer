<?php 

$groups = Array();

foreach ($records as $record) {
	
	$group = Array(
		'id'     => $record->_id,
		'name'   => $record->name,
		'public' => $record->public
	);
	
	$groups[] = $group;
	
} 

echo json_encode(Array(
	'status'  => 200,
	'payload' => $groups
));
