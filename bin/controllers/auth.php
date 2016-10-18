<?php

use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;

class AuthController extends BaseController
{
	
	/**
	 * The auth/index endpoint provides an application with the means to retrieve
	 * information about a token they authorized.
	 * 
	 * If the token is expired it will act as if there was no token and return an
	 * unathorized as result.
	 * 
	 * @param string $tokenid
	 */
	public function index($tokenid = null) {
		if ($tokenid) { $token = db()->table('token')->get('token', $tokenid)->addRestriction('expires', time(), '>')->fetch(); }
		else          { $token = null; }
		
		#If the token does auto-extend, do so now.
		if ($token && $token->extends) {
			$token->expires = time() + $token->ttl;
			$token->store();
		}
		
		$this->view->set('token', $token);
	}
	
	public function oauth($tokenid) {
		
		$successURL = isset($_GET['returnurl'])? $_GET['returnurl'] : new URL('auth', 'invalidReturn');
		$failureURL = isset($_GET['cancelurl'])? $_GET['cancelurl'] : $successURL;
		
		$grant      = isset($_GET['grant'])  ? ((int)$_GET['grant']) === 1 : null;
		$session    = Session::getInstance();
		
		$token      = db()->table('token')->get('token', $tokenid)->fetch();
		
		#No token, no access
		if (!$token) { throw new PublicException('No token', 404); }
		
		$this->view->set('token',     $token);
		$this->view->set('cancelURL', $failureURL);
		$this->view->set('continue',  (string) new URL('auth', 'oauth', $tokenid, array_merge($_GET->getRaw(), Array('grant' => 1))));
		
		if (!$session->getUser()) { return $this->response->getHeaders()->redirect(new URL('user', 'login', Array('returnto' => (string)URL::current()))); }
		if ($grant === false)     { return $this->response->getHeaders()->redirect($failureURL); }
		
		if ($grant === true)      { 
			$token->user = $this->user;
			$token->store();
			
			return $this->response->getHeaders()->redirect($successURL); 
		}
		
	}
	
	/**
	 * As opposed to the OAuth endpoint, the xauth endpoint allows a machine to
	 * log in a user by using their credentials.
	 * 
	 * The use of tokens is still enforced. Some installations may have this 
	 * endpoint disabled to force OAuth.
	 * 
	 * @param string $token
	 */
	public function xauth($token) {
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			
		}
	}
	
}
