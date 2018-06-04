<?php

use app\AuthLock;
use connection\AuthModel;
use mail\spam\domain\IP;
use signature\Signature;
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
		$suspension = $token? db()->table('user\suspension')->get('user', $token->user)->addRestriction('expires', time(), '>')->fetch() : null;
		
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
			$ip = \IP::makeLocation();
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
		
		if ($this->token && $this->token->expires < time()) { 
			throw new PublicException('Invalid token: ' . __($_GET['token']), 400); 
		}
		
		
		if (isset($_GET['appSec'])) { //TODO: Remove
			/*
			 * This section is soon gonna get phased out to prevent the Application
			 * secret from being sent around between servers.
			 */
			$appId  = $_GET['appId'];
			$appSec = $_GET['appSec'];
		
			$app = db()->table('authapp')->get('appID', $appId)->addRestriction('appSecret', $appSec)->fetch();
			$this->view->set('authenticated', !!$app);
		}
		else {
			$signature = isset($_GET['signature'])? $_GET['signature'] : '';
			$context   = isset($_GET['context'])?   $_GET['context']   : null;
			
			$extracted = $this->signature->extract($signature);
			
			if ($extracted->getTarget()) {
				$remote = db()->table('authapp')->get('appID', $extracted->getTarget())->fetch();
				if(!$remote) { throw new PublicException('No remote found', 404); }
			}
			
			$app = db()->table('authapp')->get('appID', $extracted->getSrc())->fetch();
			
			/*
			 * Reconstruct the original signature with the data we have about the 
			 * source application to verify whether the apps are the same, and
			 * should therefore be granted access.
			 */
			$check = new Signature($extracted->getAlgo(), $app->appID, $app->appSecret, $extracted->getTarget(), null, $extracted->getSalt());
			
			if (!$check->checksum()->verify($extracted->checksum())) {
				throw new PublicException('Invalid signature', 403);
			}
			
			/*
			 * This endpoint requires signatures to be unexpired. The server issuing
			 * the signature can freely decide how long they want the signature to
			 * be valid.
			 * 
			 * It is unlikely that the system could be man-in-the-middle attacked,
			 * but it is possible that a signature may leak during a server error
			 * or due to human error. In this case, an expiry of 5 minutes gives 
			 * most servers ample time to process the request but an attacker will
			 * have a hard time forging an attack that will be effective.
			 */
			if ($extracted->isExpired()) {
				throw new PublicException('Signature is expired. Please renew.', 403);
			}
			
			$this->view->set('authenticated', !!$app);
			$this->view->set('src', $app);
			$this->view->set('remote', $remote);
			$this->view->set('token', $this->token);
			$this->view->set('grant', $remote? $app->canAccess($remote, $this->token? $this->token->user : null, $context) : null);
			$this->view->set('context', $remote && $context? $remote->getContext($context) : null);
		}
		
	}
	
	/**
	 * 
	 * @validate GET#signature (required)
	 * @param boolean $confirm
	 * @throws PublicException
	 * @layout minimal.php
	 */
	public function connect($confirm = null) {
		
		#Make the confirmation signature
		$xsrf = new spitfire\io\XSSToken();
		
		/*
		 * First and foremost, this cannot be executed from the token context, this
		 * means that the user requesting this needs to be a user who is logged in
		 * via session instead of an application acting on behalf of a user.
		 */
		if ($this->token) {
			throw new PublicException('This method cannot be called from token context', 400);
		}
		
		if (!isset($_GET['signature'])) {
			throw new PublicException('Invalid signature', 400); 
		}
		
		/*
		 * Extract all the signatures the system received. The idea is to provide 
		 * one signature per context piece needed. While this requires the system
		 * to provide several signatures, it also makes it way more flexible for 
		 * the receiving application to select which permissions it wishes to request.
		 */
		$signatures = collect($_GET['signature'])->each(function ($e) { 
			list($signature, $src, $tgt) = $this->signature->verify($e);
			
			/*
			 * Check if the application was already granted access This may report that
			 * the target was already blocked by the system from accessing data
			 * on the source application.
			 */
			$lock = new AuthLock($src, $this->user, $e->getContext()[0]);
			$granted = $lock->unlock($tgt);
			
			/*
			 * If the target was already denied access, either by the user or by policy,
			 * then we throw an exception and prevent the user from continuing.
			 */
			if ($granted === AuthModel::STATE_DENIED) {
				throw new PublicException('Application was already denied access', 400);
			}
			
			/*
			 * If the target was already approved access, either by the user or by 
			 * policy, then we skip asking for permission to this context.
			 */
			if ($granted === AuthModel::STATE_AUTHORIZED) {
				return null;
			}
			
			return $signature;
		});
		
		$src = db()->table('authapp')->get('appID', $signatures->rewind()->getSrc())->first(true);
		$tgt = db()->table('authapp')->get('appID', $signatures->rewind()->getTarget())->first(true);
		
		$singlesource = $signatures->reduce(function ($c, Signature$e) use($src, $tgt) { 
			return $c && $e->getTarget() === $tgt && $e->getSrc() === $src; 
		}, true);
		
		if (!$singlesource) {
			throw new PublicException('All signatures must belong to a single source', 401);
		}
		
		/*
		 * If the user is already confirming the application request, we check whether
		 * the signature they used to do so is valid. This is generally to protect
		 * the user from any illegitimate requests to provide data by XSFS.
		 */
		if ($confirm) {
			
			if (!$xsrf->verify($confirm)) {
				throw new PublicException('Invalid confirmation hash', 403);
			}
			
			foreach ($signatures as $c) {
				$connection = db()->table('connection\auth')
					->get('source', $src)
					->where('target', $tgt)
					->where('user', $this->user)
					->where('context', $c->getContext()[0])
					->where('expires', '>', time())->first();
				
				if (!$connection) {
					$connection = db()->table('connection\auth')->newRecord();
					$connection->target  = $tgt;
					$connection->source  = $src;
					$connection->user    = $this->user;
					$connection->context = $c->getContext()[0];
					$connection->state   = AuthModel::STATE_AUTHORIZED;
					$connection->expires = isset($_POST['remember'])? null : time() + (86400 * 30);
					$connection->store();
				}
			}
			
			if (isset($_GET['returnto'])) {
				return $this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']);
			}
		}
		
		$this->view->set('src', $src);
		$this->view->set('tgt', $tgt);
		$this->view->set('signatures', $signatures);
		$this->view->set('confirm', $xsrf->getValue());
		
	}
	
}
