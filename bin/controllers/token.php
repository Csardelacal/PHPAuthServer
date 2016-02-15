<?php

use spitfire\exceptions\PublicException;

class TokenController extends Controller
{
	
	public function create() {
		$appid  = isset($_POST['appID'])    ? $_POST['appID']     : $_GET['appID'];
		$secret = isset($_POST['appSecret'])? $_POST['appSecret'] : $_GET['appSecret'];
		
		$app = db()->table('authapp')->get('appID', $appid)
				  ->addRestriction('appSecret', $secret)->fetch();
		
		if (!$app) { throw new PublicException('No application found', 403); }
		
		$token = db()->table('token')->newRecord();
		$token->token   = md5(uniqid(mt_rand(), true));
		$token->user    = null;
		$token->app     = $app;
		$token->expires = time() + 14400;
		$token->extends = true;
		$token->store();
		
		//Send the token to the view so it can render it
		$this->view->set('token', $token);
	}
	
}
