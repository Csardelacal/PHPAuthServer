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
		
		#Check if the application grants generous TTLs
		$generous = \spitfire\core\Environment::get('phpAuth.token.extraTTL');
		
		#If the token does auto-extend, do so now.
		if ($token && $token->extends && $token->expires < (time() + $token->ttl) ) {
			$token->expires = time() + ($generous? $token->ttl * 1.15 : $token->ttl);
			$token->store();
		}
		
		$this->view->set('token', $token);
	}
	
	/**
	 * 
	 * @param type $tokenid
	 * @return type
	 * @layout minimal.php
	 * @throws PublicException
	 */
	public function oauth($tokenid) {
		
		$successURL = isset($_GET['returnurl'])? $_GET['returnurl'] : new URL('auth', 'invalidReturn');
		$failureURL = isset($_GET['cancelurl'])? $_GET['cancelurl'] : $successURL;
		
		$token      = db()->table('token')->get('token', $tokenid)->fetch();
		$grant      = isset($_GET['grant'])  ? ((int)$_GET['grant']) === 1 : null;
		$session    = Session::getInstance();
		
		#If the user already automatically grants the application in, then we continue
		if (db()->table('user\authorizedapp')->get('user', $this->user)->addRestriction('app', $token->app)->fetch())  { $grant = true; }
		
		#No token, no access
		if (!$token) { throw new PublicException('No token', 404); }
		
		$this->view->set('token',     $token);
		$this->view->set('cancelURL', $failureURL);
		$this->view->set('continue',  (string) url('auth', 'oauth', $tokenid, array_merge($_GET->getRaw(), Array('grant' => 1))));
		
		if (!$session->getUser()) { return $this->response->getHeaders()->redirect(url('user', 'login', Array('returnto' => (string) spitfire\core\http\URL::current()))); }
		if ($grant === false)     { return $this->response->getHeaders()->redirect($failureURL); }
		
		/*
		 * If the user allowed the token to exist and granted the application access,
		 * then we record the user's setting whether he wishes to be automatically
		 * logged in next time.
		 */
		if ($grant === true) { 
			
			if (isset($_POST['authorize']) && !db()->table('user\authorizedapp')->get('user', $token->user)->addRestriction('app', $token->app)->fetch()) {
				$authorization = db()->table('user\authorizedapp')->newRecord();
				$authorization->user = $this->user;
				$authorization->app  = $token->app;
				$authorization->store();
			}
			
			/*
			 * Retrieve the IP information from the client. This should allow the 
			 * application to provide the user with data where they connected from.
			 */
			$ip = IP::makeLocation();
			if ($ip) {
				$token->country = $ip->country_code;
				$token->city    = substr($ip->city, 0, 20);
			}
			
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
	
	/**
	 * This endpoint allows an application to test whether a certain app exists 
	 * with an appID and appSec combination. Please note that the endpoint will 
	 * not reveal whether an application exists and the appSec is wrong.
	 */
	public function app() {
		
		$appId  = isset($_GET['appId']) ? $_GET['appId']  : null;
		$appSec = isset($_GET['appSec'])? $_GET['appSec'] : null;
		
		$app = db()->table('authapp')->get('appID', $appId)->addRestriction('appSecret', $appSec)->fetch();
		
		$this->view->set('app', $app);
	}
	
}
