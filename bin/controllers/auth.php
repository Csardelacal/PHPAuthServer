<?php

use connection\AuthModel;
use spitfire\core\Environment;
use spitfire\core\http\URL;
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
		
		#Check if the user has been either banned or suspended
		$suspension = db()->table('user\suspension')->get('user', $token->user)->addRestriction('expires', time(), '>')->fetch();
		
		#Check if the application grants generous TTLs
		$generous = Environment::get('phpAuth.token.extraTTL');
		
		#If the token does auto-extend, do so now.
		if ($token && $token->extends && $token->expires < (time() + $token->ttl) ) {
			$token->expires = time() + ($generous? $token->ttl * 1.15 : $token->ttl);
			$token->store();
		}
		
		$this->view->set('token', $token);
		$this->view->set('suspension', $suspension);
	}
	
	/**
	 * 
	 * @param type $tokenid
	 * @return type
	 * @layout minimal.php
	 * @throws PublicException
	 */
	public function oauth($tokenid) {
		
		$successURL = isset($_GET['returnurl'])? $_GET['returnurl'] : url('auth', 'invalidReturn');
		$failureURL = isset($_GET['cancelurl'])? $_GET['cancelurl'] : $successURL;
		
		$token      = db()->table('token')->get('token', $tokenid)->fetch();
		$grant      = isset($_GET['grant'])  ? ((int)$_GET['grant']) === 1 : null;
		$session    = Session::getInstance();
		
		#Check whether the user was banned
		$banned     = db()->table('user\suspension')->get('user', $this->user)->addRestriction('expires', time(), '>')->addRestriction('preventLogin', 1)->fetch();
		if ($banned) { throw new PublicException('Your account was banned, login was disabled', 401); }
		
		#Check whether the user was disabled
		if ($this->user->disabled) { throw new PublicException('Your account was disabled', 401); }
		
		#If the user already automatically grants the application in, then we continue
		if (db()->table('user\authorizedapp')->get('user', $this->user)->addRestriction('app', $token->app)->fetch())  { $grant = true; }
		
		#No token, no access
		if (!$token) { throw new PublicException('No token', 404); }
		
		$this->view->set('token',     $token);
		$this->view->set('cancelURL', $failureURL);
		$this->view->set('continue',  (string) url('auth', 'oauth', $tokenid, array_merge($_GET->getRaw(), Array('grant' => 1))));
		
		if (!$session->getUser()) { return $this->response->getHeaders()->redirect(url('user', 'login', Array('returnto' => (string) URL::current()))); }
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
	 * Allows third party applications to test whether a certain application 
	 * exists within PHPAS. It expects the application to provide a series of 
	 * _GET parameters that need to be properly provided for it to return a 
	 * positive match.
	 * 
	 * * Application id
	 * * A signature that authorizes the application.
	 * 
	 * The signature is composed of the application's id, the target application's
	 * id, a random salt and a hash composed of these and the application's secret.
	 * 
	 * The signature should therefore prevent the application from forging requests
	 * on behalf of third parties.
	 * 
	 * @todo For legacy purposes, this will accept an app id and secret combo 
	 * which is no longer supported and will be removed in future versions.
	 */
	public function app() {
		
		if (isset($_GET['token'])) {
			$token = db()->table('token')->get('token', $_GET['token'])->fetch();
			if ($token->expires < time()) { throw new PublicException('Invalid token: ' . __($_GET['token']), 400); }
		}
		else {
			$token = null;
		}
		
		if (isset($_GET['appSec'])) {
			$appId  = isset($_GET['appId']) ? $_GET['appId']  : null;
			$appSec = isset($_GET['appSec'])? $_GET['appSec'] : null;
		
			$app = db()->table('authapp')->get('appID', $appId)->addRestriction('appSecret', $appSec)->fetch();
			$this->view->set('authenticated', !!$app);
		}
		else {
			$signature = explode(':', isset($_GET['signature'])? $_GET['signature'] : '');
			$context   = isset($_GET['context'])? $_GET['context'] : null;
			
			switch(count($signature)) {
				case 4:
					list($algo, $src, $salt, $hash) = $signature;
					$remote = null;
					break;
				case 5:
					list($algo, $src, $target, $salt, $hash) = $signature;
					$remote = db()->table('authapp')->get('appID', $target)->fetch();
					
					if(!$remote) { throw new PublicException('No remote found', 404); }
					break;
				default:
					throw new PublicException('Invalid signature', 400);
			}
			
			$app = db()->table('authapp')->get('appID', $src)->fetch();
			
			/*
			 * Reconstruct the original signature with the data we have about the 
			 * source application to verify whether the apps are the same, and
			 * should therefore be granted access.
			 */
			switch(strtolower($algo)) {
				case 'sha512':
					$calculated = hash('sha512', implode('.', array_filter([$app->appID, $remote? $remote->appID : null, $app->appSecret, $salt])));
					break;
				default:
					throw new PublicException('Invalid algorithm', 400);
			}
			
			if ($hash !== $calculated) {
				throw new PublicException('Invalid signature', 403);
			}
			
			$this->view->set('authenticated', !!$app);
			$this->view->set('src', $app);
			$this->view->set('remote', $remote);
			$this->view->set('token', $token);
			$this->view->set('grant', $remote? $app->canAccess($remote, $token? $token->user : null, $context) : null);
			$this->view->set('context', $remote && $context? $remote->getContext($context) : null);
		}
		
	}
	
	/**
	 * 
	 * @param boolean $confirm
	 * @return type
	 * @throws PublicException
	 * @layout minimal.php
	 */
	public function connect($confirm = null) {
		if (!isset($_GET['signature'])) { throw new PublicException('Invalid signature', 400); }
		
		$signature = explode(':', $_GET['signature']);
		if (count($signature) != 6) { throw new PublicException('Malformed signature', 400); }
		
		list($algo, $srcId, $targetId, $contextstr, $salt, $hash) = $signature;
		$context = explode(',', $contextstr);
		
		/**
		 * @var AuthAppModel The source application (the application requesting data)
		 */
		$src = db()->table('authapp')->get('appID', $srcId)->fetch();
		$tgt = db()->table('authapp')->get('appID', $targetId)->fetch();
		$ctx = $src->getContext($context);
		
		switch(strtolower($algo)) {
			case 'sha512':
				$calculated = hash('sha512', implode('.', [$src->appID, $tgt->appID, $src->appSecret, $contextstr, $salt]));
				break;
			
			default:
				throw new PublicException('Invalid algorithm', 400);
		}
		
		if ($calculated !== $hash) {
			throw new PublicException('Hash failure', 403);
		}
		
		$granted = $tgt->canAccess($tgt, $this->user, $ctx->each(function ($e) { return $e->ctx; })->toArray());
		
		if ($confirm) {
			$pieces = explode(':', $confirm);
			list($expires, $csalt, $csum) = $pieces;
			
			if ($csum !== hash('sha512', implode('.', [$src->appID, $tgt->appID, $src->appSecret, $csalt, $expires])) || $expires < time()) {
				throw new PublicException('Invalid confirmation hash', 403);
			}
			
			$confirm = true;
		}
		
		if ($granted === AuthModel::STATE_DENIED) {
			throw new PublicException('Application was already denied access', 400);
		}
		
		if ($granted === AuthModel::STATE_AUTHORIZED || $confirm ) {
			
			if ($ctx->isEmpty()) {
				$ctx = [null];
			}
			
			foreach ($ctx as $c) {
				$connection = db()->table('connection\auth')
					->get('source', $tgt)
					->addRestriction('target', $src)
					->addRestriction('user', $this->user)
					->addRestriction('context', $c? $c->getId() : null, $c? '=' : 'IS')
					->addRestriction('expires', time(), '>')->fetch();
				
				if (!$connection) {
					$connection = db()->table('connection\auth')->newRecord();
					$connection->target  = $src; //Source and target are swapped in this request.
					$connection->source  = $tgt; //This is rather confusing, but the request is issued by the target - making it the source
					$connection->user    = $this->user;
					$connection->context = $c? $c->getId() : null;
					$connection->state   = AuthModel::STATE_AUTHORIZED;
					$connection->expires = isset($_POST['remember'])? null : time() + (86400 * 30);
					$connection->store();
				}
			}
			
			if (isset($_GET['returnto'])) {
				return $this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']);
			}
		}
		
		#Make the confirmation signature
		$confirmSalt = trim(str_replace(['/', '+', '='], '-', base64_encode(random_bytes(25))), '-');
		$confirmExpires = time() + 300;
		$confirmHash = hash('sha512', implode('.', [$src->appID, $tgt->appID, $src->appSecret, $confirmSalt, $confirmExpires]));
		$confirmSignature = implode(':', [$confirmExpires, $confirmSalt, $confirmHash]);
		
		$this->view->set('src', $src);
		$this->view->set('tgt', $tgt);
		$this->view->set('ctx', $ctx);
		$this->view->set('ctxstr', $contextstr);
		$this->view->set('signature', $_GET['signature']);
		$this->view->set('confirm', $confirmSignature);
		
	}
	
}
