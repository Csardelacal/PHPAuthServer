<?php

define('APPID', '465662825');
define('APPSec', 'rfTjJz9HTjDWIgpoy0bu7NTIZfSVkAjYJrf3Bv4EB3VMLmU=');

$token = isset($_GET['token'])? $_GET['token'] : null;

if (!$token) {
	$ch = curl_init('http://localhost/PHPAuthServer/token/create.json?appID=' . APPID . '&appSecret=' . urlencode(APPSec));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$json = json_decode($msg = curl_exec($ch));
	
	die ('<a href="' . $json->location . '?returnurl=' . urlencode('http://localhost/PHPAuthServer/test.php?token=' . $json->token) .'">Connect</a>');
}


$ch = curl_init('http://localhost/PHPAuthServer/user/detail/CSharp.json?token=' . urlencode($_GET['token']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$user = json_decode($msg = curl_exec($ch))->payload;

echo sprintf('User (%s) %s, website %s', $user->id, $user->username, $user->attributes->website);