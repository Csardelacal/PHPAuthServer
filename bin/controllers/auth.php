<?php

use app\AuthLock;
use client\LocationModel;
use connection\AuthModel;
use magic3w\http\url\reflection\QueryString;
use magic3w\http\url\reflection\URLReflection;
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
	public function index($tokenid = null)
	{
		if ($this->token) {
			$token = $this->token;
		}
		elseif ($tokenid) {
			$token = db()->table('token')->get('token', $tokenid)->where('expires', '>', time())->first();
		}
		else {
			$token = null;
		}
		
		#Check if the user has been either banned or suspended
		$suspension = $token && $token->user? $token->user->isSuspended() : null;
		
		#Check if the application grants generous TTLs
		$generous = Environment::get('phpAuth.token.extraTTL');
		
		#If the token does auto-extend, do so now.
		if ($token && $token->extends && $token->expires < (time() + $token->ttl)) {
			$token->expires = time() + ($generous? $token->ttl * 1.15 : $token->ttl);
			$token->store();
		}
		
		#Check whether the app has signed the package, and whether it has been registered
		$usage = db()->table(token\UsageModel::class)->get('token', $token)->where('app', $this->authapp)->first();
		
		if (!$usage) {
			$usage = db()->table(token\UsageModel::class)->newRecord();
			$usage->token = $token;
			$usage->app   = $this->authapp;
			$usage->store();
		}
		
		$this->view->set('token', $token);
		$this->view->set('suspension', $suspension);
	}
	
	/**
	 * oAuth here should actually stand for oldAuth - it's not technically oAuth we're
	 * doing here and it's a very cumbersome implementation.
	 *
	 * @param type $tokenid
	 * @return type
	 * @layout minimal.php
	 * @throws PublicException
	 */
	public function oauth($tokenid)
	{
		
		$successURL = isset($_GET['returnurl'])? $_GET['returnurl'] : url('auth', 'invalidReturn');
		$failureURL = isset($_GET['cancelurl'])? $_GET['cancelurl'] : $successURL;
		
		$token      = db()->table('token')->get('token', $tokenid)->fetch();
		$grant      = isset($_GET['grant'])  ? ((int)$_GET['grant']) === 1 : null;
		$session    = Session::getInstance();
		
		#Check whether the user is authenticated. If this is not the case, redirect them
		#to the login page.
		if (!$this->user) {
			$loginURL = url('user', 'login', array('returnto' => (string) URL::current()));
			return $this->response->setBody('Redirect')->getHeaders()->redirect($loginURL);
		}
		
		#Check whether the user was banned. If the account is disabled due to administrative
		#action, we inform the user that the account was disabled and why.
		$banned = $this->user->isSuspended();
		
		if ($banned) {
			$ex = new LoginException('Your account was banned, login was disabled.', 401);
			$ex->setUserID($this->user->_id);
			$ex->setReason($banned->reason);
			$ex->setExpiry($banned->expires);
			throw $ex;
		}
		
		if ($this->user->disabled) {
			$ex = new LoginException('This account has been disabled permanently.', 401);
			$ex->setUserID($this->user->_id);
			throw $ex;
		}
		if (!$this->user->verified) {
			$this->response->setBody('Redirect')->getHeaders()->redirect(url('user', 'activate'));
			return;
		}
		
		#If the user already automatically grants the application in, then we continue
		if (db()->table(user\AuthorizedappModel::class)->get('user', $this->user)->addRestriction('app', $token->app)->fetch()) {
			$grant = true;
		}
		
		#Only administrators are allowed to authorize tokens to system applications.
		#This imposes a restriction to encourage administrators to be open about the
		#applications accessing user data. System applications are not required to
		#disclose what data they have access to.
		if ($token->app->system && !$this->isAdmin) {
			$grant = false;
		}
		
		#No token, no access
		if (!$token) {
			throw new PublicException('No token', 404);
		}
		
		$continueURL = url('auth', 'oauth', $tokenid, array_merge($_GET->getRaw(), array('grant' => 1)));
		
		$this->view->set('token', $token);
		$this->view->set('cancelURL', $failureURL);
		$this->view->set('continue', (string) $continueURL);
		
		if (!$session->getUser()) {
			$loginURL = url('user', 'login', array('returnto' => (string) URL::current()));
			return $this->response->getHeaders()->redirect($loginURL);
		}
		if ($grant === false) {
			return $this->response->getHeaders()->redirect($failureURL);
		}
		
		/*
		 * If the user allowed the token to exist and granted the application access,
		 * then we record the user's setting whether he wishes to be automatically
		 * logged in next time.
		 */
		if ($grant === true) {
			$preauthorized = db()->table(user\AuthorizedappModel::class)
				->get('user', $token->user)
				->where('app', $token->app)
				->fetch();
			
			if (isset($_POST['authorize']) && !$preauthorized) {
				$authorization = db()->table(user\AuthorizedappModel::class)->newRecord();
				$authorization->user = $this->user;
				$authorization->app  = $token->app;
				$authorization->store();
			}
			
			/*
			 * Retrieve the IP information from the client. This should allow the
			 * application to provide the user with data where they connected from.
			 *
			 * @todo While Cloudflare is very convenient. It's definitely not a generic
			 * protocol and produces vendor lock-in. This should be replaced with an
			 * interface that allows using a different vendor for location detection.
			 */
			$token->country = $_SERVER["HTTP_CF_IPCOUNTRY"];
			$token->city    = substr($_SERVER["HTTP_CF_IPCITY"], 0, 20);
			
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
	public function app()
	{
		
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
	 * @todo Remove deprecated mechanism
	 *
	 * @validate GET#signatures (required)
	 * @param string $confirm
	 * @throws PublicException
	 * @layout minimal.php
	 */
	public function connect($confirm = null)
	{
		
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
			$loginURL = url('user', 'login', array('returnto' => (string) URL::current()));
			$this->response->setBody('Redirecting...')->getHeaders()->redirect($loginURL);
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
		$singlesource = $signatures->reduce(function ($c, Signature$e) use ($src, $tgt) {
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
				$connection = db()->table(connection\AuthModel::class)->newRecord();
				$connection->target  = $tgt;
				$connection->source  = $src;
				$connection->user    = $this->user;
				$connection->context = $c->getContext();
				$connection->state   = AuthModel::STATE_AUTHORIZED;
				$connection->expires = isset($_POST['remember'])? null : time() + (86400 * 30);
				$connection->store();
			}
			
			return $this->response->setBody('Redirecting...')
				->getHeaders()->redirect($_GET['returnto']?: url(/*Grant success page or w/e*/));
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
	
	
	
	/**
	 *
	 * @validate GET#response_type (string required)
	 * @validate GET#client_id (string required)
	 * @validate GET#state (string required)
	 * @validate GET#redirect_uri (string required)
	 * @validate GET#code_challenge (string required)
	 * @validate GET#code_challenge_method (string required)
	 *
	 * @todo This endpoint should verify that a user actually is authorized to issue this token
	 * by using ABAC or a similar mechanism. This could allow operators of the Authentication
	 * server to block certain users from accessing certain applications (or parts of them).
	 *
	 * @param type $tokenid
	 * @return void
	 * @layout minimal.php
	 * @throws PublicException
	 */
	public function oauth2()
	{
		
		$code_challenge = $_GET['code_challenge'];
		$code_challenge_method = $_GET['code_challenge_method']?? 'plain';
		$audience = $_GET['audience']?
		db()->table(AuthAppModel::class)->get('appID', $_GET['audience'])->first(true) :
		null;
		
		/*
		 * Find the application intending to authenticate this request.
		 */
		$client = db()->table('authapp')->get('appID', $_GET['client_id'])->first(true);
		
		/*
		 * In order to ensure that the client can be given appropriate access, the
		 * server needs to make sure that the application requests the appropriate
		 * scopes for this application.
		 *
		 * An application must never request access to a scope that doesn't exist,
		 * granting access to undefined scopes may lead to dangerous behavior.
		 *
		 * Scopes are defined by the audience that receives the token, to make sure
		 * you request the right scopes, refer to the documentation of the application
		 * you wish to read data from.
		 *
		 * While we do this, we also put the 'basic' scope on the stack. This allows
		 * us to check that the user is granting permission to access the application
		 * with the minimum viable data.
		 *
		 * @todo Implement scope checking. Right now PHPAS does not validate the list of
		 * scopes the client has passed. This means that PHPAS is unable to inform the
		 * user what the scope does and will just regurgitate the list of scopes it received.
		 *
		 * This used to have a database table attached which would provide icons and captions
		 * that users should understand when interacting with the authentication dialog.
		 */
		$scopes = collect(explode(' ', $_GET['scope']))
			->filter()
			->add(collect(['basic']))
			->unique();
		
		/*
		 * We now check which scopes we have already received consent for, this means that the
		 * user has either already given their consent or the server implies that they are
		 * consenting due to policy.
		 *
		 * Policy based consent is generally used when handling internal applications where the
		 * developer can assume that the consent is already given.
		 */
		$consent_implied = db()->table(user\ConsentModel::class)
			->get('client', $client)
			->where('scope', $scopes->toArray())
			->where('user', null)
			->where('revoked', null)
			->all()
			->add(
				db()->table(user\ConsentModel::class)
					->get('client', $client)
					->where('scope', $scopes->toArray())
					->where('user', $this->user)
					->where('revoked', null)
					->all()
			)
			->extract('scope')
			->unique();
		
		$consent_needed = array_diff(
			$scopes->toArray(),
			$consent_implied->toArray()
		);
		
		/*
		 * The response type used to be code or token for applications implementing
		 * oAuth2 whenever the server and/or client does not support PKCE. Since our
		 * server is implemented right from the start with PKCE in mind, we can
		 * enforce the use of the response_type of code and deny any requests with
		 * token.
		 *
		 * Since the result of this request is handled by the user agent, it should
		 * never return a token directly. Instead, the user agent should be handed an
		 * access_code that the application (potentially running inside the UA) can
		 * exchange for a token.
		 */
		if ($_GET['response_type'] !== 'code') {
			throw new PublicException(
				'This server does only accept a response_type of code. Please refer to the manual',
				400
			);
		}
		
		/*
		 * When generating an oAuth session we do require the user to be fully
		 * authenticated.
		 */
		if (!$this->user) {
			$this->response->setBody('Redirecting...');
			$loginURL = url('user', 'login', array('returnto' => (string) URL::current()));
			return $this->response->getHeaders()->redirect($loginURL);
		}
		
		
		/*
		 * Check whether the user was banned. If the account is disabled due to administrative
		 * action, we inform the user that the account was disabled and why.
		 */
		$banned = $this->user->isSuspended();
		
		if ($banned) {
			$ex = new LoginException('Your account was suspended, login is disabled.', 401);
			$ex->setUserID($this->user->_id);
			$ex->setReason($banned->reason);
			$ex->setExpiry($banned->expires);
			throw $ex;
		}
		
		#Check whether the user was disabled
		if ($this->user->disabled) {
			throw new PublicException('Your account was disabled', 401);
		}
		
		/**
		 * Check if the user needs to be strongly authenticated for this app
		 *
		 * @todo Perform MFA check here
		 */
		
		/*
		 * Start of by assuming that the client is not intended to be given the application's
		 * data. We will later check whether the application was granted access and
		 * will then flip this flag.
		 */
		$grant = empty($consent_needed);
		
		/*
		 * Our implementation does not accept anything but S256, plain code_challenges
		 * are going to be rejected. These could be intercepted easily.
		 */
		if ($code_challenge_method != 'S256') {
			throw new PublicException('Invalid code_challenge_method', 400);
		}
		
		/*
		 * Extract the redirect, and make sure that it points to a URL that the client
		 * is authorized to send the user to.
		 */
		$redirect = URLReflection::fromURL($_GET['redirect_uri']);
		
		/**
		 * Create a cancel URL in case the user chaanges their mind.
		 */
		$cancelURL = (string)$redirect->withQuery(QueryString::encode([
			'error' => 'denied',
			'description' => 'Authentication request was denied'
		]));
		
		/**
		 * In order to validate the redirect we make sure that the protocol, hostname
		 * and paths for the redirect match.
		 *
		 * @todo Actually check the redirect
		 */
		$valid = true || db()->table(LocationModel::class)->get('client', $client)->all()
			->reduce(function ($valid, LocationModel $e) use ($redirect) {
				if ($e->hostname !== $redirect->getHost()) {
					return $valid;
				}
				if (!Strings::startsWith($redirect->getPath(), $e->path)) {
					return $valid;
				}
				
				return true;
			}, false);
		
		if (!$valid) {
			throw new PublicException(sprintf('Redirect to %s is invalid', __($redirect)), 401);
		}
		
		/*
		 * Check if the user is approving the request to provide the application access
		 * to their account, given the information they have.
		 */
		elseif ($this->request->isPost()) {
			#TODO: Check if permission allows this user to authenticate codes for this
			# application. Important for elevated privileges apps
			
			$grant = $_POST['grant'] === 'grant';
		}
		
		/*
		 * Check if the client is granted access to the application by policy, this
		 * would allow the application to bypass the authentication flow.
		 */
		elseif (false) {
			#TODO: Check whether the application is granted access by policy
		}
		
		if ($grant) {
			/*
			 * Record the code challenge, and the user approving this challenge, together
			 * with the state the application passed.
			 */
			$challenge = db()->table(access\CodeModel::class)->newRecord();
			$challenge->code = str_replace(['-', '/', '='], '', base64_encode(random_bytes(64)));
			$challenge->audience = $audience;
			$challenge->client = $client;
			$challenge->user = $this->user;
			$challenge->state = $_GET['state'];
			$challenge->challenge = sprintf('%s:%s', $code_challenge_method, $code_challenge);
			$challenge->scopes = $scopes->join(' ');
			$challenge->redirect = (string)$redirect;
			$challenge->created = time();
			$challenge->expires = time() + 180;
			$challenge->session = $this->session;
			$challenge->store();
			
			foreach ($scopes as $scope) {
				/**
				 * Fetch the user's consent, so if we didn't have that on record before, we can record it
				 * now. This is automatically done, because the user has consented to granting the application
				 * access to the scopes.
				 *
				 * Please note that the user should be able to revoke the consent to the use of their data
				 * at any time, but we also acknowledge that asking for consent for the same actions over and
				 * over does not lead to better security and causes user exhaustion.
				 *
				 * Also, most malicious apps, will have the ability to just cache the data they did receive
				 * from the user, and they still need the user to be present in order to refresh it.
				 */
				$consent = db()->table('user\consent')
					->get('client', $client)
					->where('scope', $scope)
					->where('user', $this->user)
					->where('revoked', null)->first();
				
				if (!$consent) {
					$consent = db()->table('user\consent')->newRecord();
					$consent->client = $client;
					$consent->scope = $scope;
					$consent->user = $this->user;
					$consent->store();
				}
			}
			
			return $this->response->setBody('Redirect')
				->getHeaders()->redirect($redirect->withQuery(QueryString::encode([
					'code' => $challenge->code,
					'state' => $challenge->state
				])));
		}
		
		/*
		 * If the request was posted, the user selected to deny the application access
		 */
		elseif ($this->request->isPost()) {
			$this->response->setBody('Redirect')->getHeaders()->redirect($cancelURL);
		}
		
		/**
		 * If the application requested a silent authentication, we do not continue to seek permission
		 * from the resource owner, since the application is explicitly asking us not to do so.
		 *
		 * While the application has the option to ask us to not prompt the user, this will not change the
		 * server's decision and will just result in a denied error being issued immediately.
		 */
		elseif (($_GET['prompt']?? false) === 'none') {
			$this->response->setBody('Redirect')->getHeaders()->redirect($cancelURL);
		}
		
		/*
		 * If the user has not been able to allow or deny the request, the server
		 * should request their permission.
		 */
		$this->view->set('user', $this->user);
		$this->view->set('client', $client);
		$this->view->set('audience', $audience);
		$this->view->set('redirect', (string)$redirect);
		$this->view->set('cancel', $cancelURL);
	}
}
