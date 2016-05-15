<?php

use spitfire\exceptions\PublicException;

class TokenController extends BaseController
{
	
	public function index() {
		
		$query = db()->table('token')->getAll();
		if (!$this->isAdmin) { $query->addRestriction('user', $this->user); }
		
		$query->group()
				->addRestriction('expires', null, 'IS')
				->addRestriction('expires', time(), '>');
		
		$this->view->set('pagination', new Pagination($query));
		$this->view->set('records',    $query->fetchAll());;
	}
	
	public function create() {
		$appid   = isset($_POST['appID'])    ? $_POST['appID']     : $_GET['appID'];
		$secret  = isset($_POST['appSecret'])? $_POST['appSecret'] : $_GET['appSecret'];
		$expires = (int) isset($_GET['expires'])? $_GET['expires'] : 14400;
		
		$app = db()->table('authapp')->get('appID', $appid)
				  ->addRestriction('appSecret', $secret)->fetch();
		
		if (!$app) { throw new PublicException('No application found', 403); }
		
		$token = TokenModel::create($app, $expires);
		
		//Send the token to the view so it can render it
		$this->view->set('token', $token);
	}
	
	/**
	 * 
	 * @template none
	 * @param string $tokenid
	 */
	public function end($tokenid) {
		$token = db()->table('token')->get('token', $tokenid)->fetch();
		
		if (!$token) { throw new PublicException('No token found', 404); }
		if ($token->expires && $token->expires < time()) { throw new PublicException('Token already expired', 403); }
		
		$token->expires = time();
		$token->store();
		
		$this->response->getHeaders()->redirect(new URL('token', Array('message' => 'ended')));
	} 
	
}
