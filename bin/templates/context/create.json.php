<?php

echo json_encode([
	'result'      => $result? 'success' : 'failure',
	'id'          => $result->ctx,
	'app'         => $result->app->appID,
	'name'        => $result->title,
	'description' => $result->description,
	'expires'     => $result->expires
]);