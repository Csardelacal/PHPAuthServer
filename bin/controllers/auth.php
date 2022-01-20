<?php

use app\AuthLock;
use connection\AuthModel;
use signature\Signature;
use spitfire\core\Environment;
use spitfire\core\http\URL;
use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;
use spitfire\io\XSSToken;

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
		if ($this->token) { $token = $this->token; }
		elseif ($tokenid) { $token = db()->table('token')->get('token', $tokenid)->where('expires', '>', time())->first(); }
		else              { $token = null; }

		#Check if the user has been either banned or suspended
		$suspension = $token? db()->table('user\suspension')->get('user', $token->user)->addRestriction('expires', time(), '>')->fetch() : null;

		#Check if the application grants generous TTLs
		$generous = Environment::get('phpAuth.token.extraTTL');

		#If the token does auto-extend, do so now.
		if ($token && $token->extends && $token->expires < (time() + $token->ttl) ) {
			$token->expires = time() + ($generous? $token->ttl * 1.15 : $token->ttl);
			$token->store();
		}

		#Check whether the app has signed the package, and whether it has been registered
		$usage = db()->table('token\usage')->get('token', $token)->where('app', $this->authapp)->first();

		if (!$usage) {
			$usage = db()->table('token\usage')->newRecord();
			$usage->token = $token;
			$usage->app   = $this->authapp;
			$usage->store();
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

		#Check whether the user was banned. If the account is disabled due to administrative
		#action, we inform the user that the account was disabled and why.
		$banned     = db()->table('user\suspension')->get('user', $this->user)->addRestriction('expires', time(), '>')->addRestriction('preventLogin', 1)->first();
		if ($banned) {
			$ex = new LoginException('Your account was banned, login was disabled.', 401);
			$ex->setUserID($this->user->_id);
			$ex->setReason($banned->reason);
			if ($banned->expires < (time() + (365 * 86400))) // only show expiry if less than 1 year!
				$ex->setExpiry($banned->expires);
			throw $ex;
        }

		#Check whether the user was disabled
		if (!$session->getUser()) { return $this->response->setBody('Redirect')->getHeaders()->redirect(url('user', 'login', Array('returnto' => (string) URL::current()))); }
		if ($this->user->disabled) {
			$ex = new LoginException('This account has been disabled permanently.', 401);
            $ex->setUserID($this->user->_id);
            throw $ex;
        }
		if (!$this->user->verified) { $this->response->setBody('Redirect')->getHeaders()->redirect(url('user', 'activate')); return; }

		#If the user already automatically grants the application in, then we continue
		if (db()->table('user\authorizedapp')->get('user', $this->user)->addRestriction('app', $token->app)->fetch())  { $grant = true; }

		#Only administrators are allowed to authorize tokens to system applications.
		#This imposes a restriction to encourage administrators to be open about the
		#applications accessing user data. System applications are not required to
		#disclose what data they have access to.
		if ($token->app->system && !$this->isAdmin) { $grant = false; }

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

		$remote  = isset($_GET['remote'])? $this->signature->verify($_GET['remote']) : null;
		$context = isset($_GET['context'])? $_GET->toArray('context') : [];

		if ($remote) {
			list($sig, $src, $tgt) = $remote;

			if (!$tgt || $tgt->appID != $this->authapp->appID) {
				throw new PublicException('Invalid remote signature. Target did not authorize itself properly', 401);
			}

			if ($sig->getContext()) {
				throw new PublicException('Invalid signature. Context should be provided via _GET', 400);
			}

			$contexts = [];
			$grant    = [];

			foreach ($context as $ctx) {
				$contexts[]  = $tgt->getContext($ctx);
				$grant[$ctx] = $tgt->canAccess($src, $this->token? $this->token->user : null, $ctx);
			}

			$this->view->set('context', $contexts);
			$this->view->set('grant', $grant);
		}
		else {
			$this->view->set('context', null);
			$this->view->set('grant', null);
		}

		$this->view->set('authenticated', !!$this->authapp);
		$this->view->set('src', $this->authapp);
		$this->view->set('remote', $src);
		$this->view->set('token', $this->token);
	}

	/**
	 *
	 * @validate GET#signatures (required)
	 * @param string $confirm
	 * @throws PublicException
	 * @layout minimal.php
	 */
	public function connect($confirm = null) {

		#Make the confirmation signature
		$xsrf = new XSSToken();

		/*
		 * First and foremost, this cannot be executed from the token context, this
		 * means that the user requesting this needs to be a user who is logged in
		 * via session instead of an application acting on behalf of a user.
		 */
		if ($this->token) {
			throw new PublicException('This method cannot be called from token context', 400);
		}

		/**
		 * If the user is not logged in, we need to send them to the log-in screen
		 * first to ensure that they can create the connection.
		 */
		if (!$this->user) {
			$this->response->setBody('Redirecting...')->getHeaders()->redirect(url('user', 'login', Array('returnto' => (string) URL::current())));
		}

		if (!isset($_GET['signatures'])) {
			throw new PublicException('Invalid signature', 400);
		}

		/*
		 * Extract all the signatures the system received. The idea is to provide
		 * one signature per context piece needed. While this requires the system
		 * to provide several signatures, it also makes it way more flexible for
		 * the receiving application to select which permissions it wishes to request.
		 */
		$signatures = collect($_GET->toArray('signatures'))->each(function ($e) {
			list($signature, $src, $tgt) = $this->signature->verify($e);

			/*
			 * Check if the application was already granted access This may report that
			 * the target was already blocked by the system from accessing data
			 * on the source application.
			 */
			$lock = new AuthLock($src, $this->user, $signature->getContext()[0]);
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
		})->filter();

		$src = db()->table('authapp')->get('appID', $signatures->rewind()->getSrc())->first(true);
		$tgt = db()->table('authapp')->get('appID', $signatures->rewind()->getTarget())->first(true);

		/*
		 * To prevent applications from sneaking in requests to permissions that
		 * do belong to third parties (by requesting seemingly innocuous requests
		 * mixed with requests from potentially malicious software), the system
		 * will verify that there is only a single source signing all the signatures.
		 */
		$singlesource = $signatures->reduce(function ($c, Signature$e) use($src, $tgt) {
			return $c && $e->getTarget() === $tgt->appID && $e->getSrc() === $src->appID;
		}, true);

		if (!$singlesource) {
			throw new PublicException('All signatures must belong to a single source', 401);
		}

		/*
		 * If the user is already confirming the application request, we check whether
		 * the signature they used to do so is valid. This is generally to protect
		 * the user from any illegitimate requests to provide data by XSRF.
		 */
		if ($confirm) {

			if (!$xsrf->verify($confirm)) {
				throw new PublicException('Invalid confirmation hash', 403);
			}

			foreach ($signatures as $c) {
				/*
				 * Create the authorizations. There's no need to check whether the
				 * connection already exists, since it would have been filtered from
				 * the signatures list at about line 242
				 */
				$connection = db()->table('connection\auth')->newRecord();
				$connection->target  = $tgt;
				$connection->source  = $src;
				$connection->user    = $this->user;
				$connection->context = $c->getContext();
				$connection->state   = AuthModel::STATE_AUTHORIZED;
				$connection->expires = isset($_POST['remember'])? null : time() + (86400 * 30);
				$connection->store();
			}

			return $this->response->setBody('Redirecting...')->getHeaders()->redirect($_GET['returnto']?: url(/*Grant success page or w/e*/));
		}

		$this->view->set('ctx', $signatures->each(function (Signature$e) {
			return $e->getContext();
		})->each(function ($e) use ($src) {
			return $e? $src->getContext($e) : null;
		})->filter());

		$this->view->set('src', $tgt);
		$this->view->set('tgt', $src);
		$this->view->set('signatures', $signatures);
		$this->view->set('confirm', $xsrf->getValue());

	}

}
