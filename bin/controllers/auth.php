<?php

use app\AuthLock;
use client\LocationModel;
use client\ScopeModel;
use connection\AuthModel;
use exceptions\suspension\LoginDisabledException;
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
	public function index($tokenid = null) {
		if ($this->token) { $token = $this->token; }
		elseif ($tokenid) { $token = db()->table('access\token')->get('token', $tokenid)->where('expires', '>', time())->first(); }
		else              { $token = null; }
		
		#Check if the user has been either banned or suspended
		$suspension = $token? db()->table('user\suspension')->get('user', $token->user)->addRestriction('expires', time(), '>')->fetch() : null;
		
		$this->view->set('token', $token);
		$this->view->set('suspension', $suspension);
	}
	
	/**
	 * 
	 * @validate GET#response_type (string required)
	 * @validate GET#client (string required)
	 * @validate GET#state (string required)
	 * @validate GET#redirect (string required)
	 * @validate GET#code_challenge (string required)
	 * @validate GET#code_challenge_method (string required)
	 * 
	 * @param type $tokenid
	 * @return void
	 * @layout minimal.php
	 * @throws PublicException
	 */
	public function oauth() {
		
		$code_challenge = $_GET['code_challenge'];
		$code_challenge_method = $_GET['code_challenge_method']?? 'plain';
		$audience = $_GET['audience']? 
			db()->table(AuthAppModel::class)->get('appID', $_GET['audience'])->first(true) : 
			db()->table(AuthAppModel::class)->get('_id', SysSettingModel::getValue('app.self'))->first(true);
		
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
		 */
		$scopes = collect(explode(' ', $_GET['scope']))
			->filter()
			->each(function ($e) use ($audience) {
				return db()->table(ScopeModel::class)->get('identifier', sprintf('%s.%s', $audience->appID, $e))->first(true);
			});
		
		/*
		 * The response type used to be code or token for applications implementing
		 * oAuth2 whenever the server and/or client does not support PKCE. Since our
		 * server is implemented right from the start with PKCE in mind, we can 
		 * enforce the use of the response_type of code and deny any requests with
		 * token.
		 */
		if ($_GET['response_type'] !== 'code') {
			throw new PublicException('This server does only accept a response_type of code. Please refer to the manual', 400);
		}
		
		/*
		 * When generating an oAuth session we do require the user to be fully 
		 * authenticated.
		 */
		if (!$this->user) { 
			$this->response->setBody('Redirecting...');
			return $this->response->getHeaders()->redirect(url('user', 'login', Array('returnto' => (string) URL::current()))); 
		}
		
		
		/*
		 * Check whether the user was banned. If the account is disabled due to administrative
		 * action, we inform the user that the account was disabled and why.
		 */
		$banned = db()->table('user\suspension')->get('user', $this->user)->addRestriction('expires', time(), '>')->addRestriction('preventLogin', 1)->first();
		
		if ($banned) { 
			throw new LoginDisabledException($banned);
		}
		
		#Check whether the user was disabled
		if ($this->user->disabled) { throw new PublicException('Your account was disabled', 401); }
		
		/*
		 * Find the application intending to authenticate this request.
		 */
		$client = db()->table('authapp')->get('appID', $_GET['client'])->first(true);
		
		/*
		 * Check if the user needs to be strongly authenticated for this app
		 */	
		if ($client->twofactor && $this->level->count() < 2) {
			$this->response->setBody('Redirect...')->getHeaders()->redirect(url('auth', 'add', 2, ['returnto' => strval(URL::current())]));
			return;
		}
		
		/*
		 * Start of by assuming that the client is not intended to be given the application's
		 * data. We will later check whether the application was granted access and
		 * will then flip this flag.
		 */
		$grant = false;
		
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
		$redirect = URLReflection::fromURL($_GET['redirect']);
		
		/*
		 * In order to validate the redirect we make sure that the protocol, hostname
		 * and paths for the redirect match.
		 */
		$valid = db()->table(LocationModel::class)->get('client', $client)->all()->reduce(function ($valid, LocationModel $e) use ($redirect) {
			if ($e->protocol !== $redirect->getProtocol()) { return $valid; }
			if ($e->hostname !== $redirect->getServer()) { return $valid; }
			if (!Strings::startsWith($redirect->getPath(), $e->path)) { return $valid; }
			
			return true;
		}, false);
		
		if (!$valid) {
			throw new PublicException(sprintf('Redirect to %s is invalid', __($redirect)), 401);
		}
		
		
		if (false) {
			#TODO: Check whether policy or permission disables the login for this application
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
			$challenge = db()->table('access\code')->newRecord();
			$challenge->code = str_replace(['-', '/', '='], '', base64_encode(random_bytes(64)));
			$challenge->audience = $audience;
			$challenge->client = $client;
			$challenge->user = $this->user;
			$challenge->state = $_GET['state'];
			$challenge->challenge = sprintf('%s:%s', $code_challenge_method, $code_challenge);
			$challenge->scope = $scopes->extract('identifier')->join(' ');
			$challenge->redirect = (string)$redirect;
			$challenge->created = time();
			$challenge->expires = time() + 180;
			$challenge->session = $this->session;
			$challenge->store();
			
			return $this->response->setBody('Redirect')
				->getHeaders()->redirect((clone $redirect)->setQueryString(['code' => $challenge->code, 'state' => $challenge->state]));
		}
		
		/*
		 * If the request was posted, the user selected to deny the application access
		 */
		elseif ($this->request->isPost()) {
			$this->response->setBody('Redirect')->getHeaders()->redirect($redirect . '?' . http_build_query(['error' => 'denied', 'description' => 'Authentication request was denied']));
		}
		
		/**
		 * If the application requested a silent authentication, we do not continue to seek permission
		 * from the resource owner, since the application is explicitly asking us not to do so.
		 * 
		 * While the application has the option to ask us to not prompt the user, this will not change the
		 * server's decision and will just result in a denied error being issued immediately.
		 */
		elseif (($_GET['prompt']?? false) === 'none') {
			$this->response->setBody('Redirect')->getHeaders()->redirect($redirect . '?' . http_build_query(['error' => 'denied', 'description' => 'Authentication request was denied']));
		}
		
		/*
		 * If the user has not been able to allow or deny the request, the server
		 * should request their permission.
		 */
		$this->view->set('client', $client);
		$this->view->set('audience', $audience);
		$this->view->set('redirect', (string)$redirect);
		$this->view->set('cancel', (string)(clone $redirect)->setQueryString(['error' => 'denied', 'description' => 'Authentication request was denied']));
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
	
	public function threshold($expect = 0) 
	{
		
		
		if (!$this->session) {
			$this->response->setBody('Redirecting')->getHeaders()->redirect(url('user', 'login', ['returnto' => strval(URL::current())]));
			return;
		}
		
		/*
		 * Create a list of the available authentication providers for each level.
		 * Depending on the expected threshold, the application will require a different
		 * combination of providers:
		 * 
		 * For level 1, the application will require the user to either confirm their
		 * password, if the password wasn't confirmed in a given amount of time, 
		 * or use any other primary provider.
		 * 
		 * For level 2, the application will require a secondary provider to be 
		 * available.
		 * 
		 * Subsequent levels will only ask for additional providers to be given.
		 */
		$primary = Environment::get('phpauth.mfa.providers.primary')? explode(',', Environment::get('phpauth.mfa.providers.primary')) : ['email', 'password'];
		$secondary = Environment::get('phpauth.mfa.providers.secondary')? explode(',', Environment::get('phpauth.mfa.providers.secondary')) : ['phone', 'rfc6238', 'backup-code', 'webauthn'];
		
		$levels = [
			1 => $primary,
			2 => $secondary,
			3 => array_merge($primary, $secondary),
			4 => array_merge($primary, $secondary),
			5 => array_merge($primary, $secondary)
		];
		
		/*
		 * Create list of challenges that the user has already passed.
		 */
		$passed = db()->table('authentication\challenge')->get('session', $this->session)->where('cleared', '!=', null)->where('expires', '>', time())->all();
		
		/*
		 * Fetch a list of all the authentication providers this user has available,
		 * these can then be used to challenge the user
		 */
		$providers = db()->table('authentication\provider')
			->get('expires', null)
			->where('user', $this->session->candidate)
			->setOrder('preferred', 'DESC')
			->all();
		
		foreach ($levels as $level => $required) 
		{
			/*
			 * We do not need to perform verification that is stronger than the app
			 * requested us to do.
			 */
			if ($level > $expect) { continue; }
			
			/*
			 * Create a list of accepted providers for this level.
			 */
			$accepted = collect($providers)->filter(function ($e) use ($required) {
				return array_search($e->type, $required);
			});
			
			if ($accepted->isEmpty()) {
				throw new PrivateException('Authentication provider for level ' . $level . ' is unavailable');
			}
			
			/*
			 * Check if any of the providers the user has passed recently was a 
			 * primary provider
			 */
			$success = $passed->filter(function ($challenge) use ($accepted) {
				return ($accepted->extract('_id')->contains($challenge->provider->_id));
			})->rewind();
			
			/*
			 * When successfully authenticating using a certain provider, the provider 
			 * cannot be used again. Otherwise a properly typed password could let 
			 * the user authenticate themselves to level 5 without any issue.
			 */
			if ($success) {
				$providers = $providers->filter(function ($e) use ($success) { return $success->_id != $e->_id; });
			}
			else {
				$provider = $accepted->rewind();
				return $this->response->setBody('Redirect')->getHeaders()->redirect(url('mfa', $provider->type, 'challenge', $provider->_id, ['returnto' => (string)URL::current()]));
			}
		}
		
		/*
		 * Redirect to where the user was headed since they passed all the trials 
		 * in order to access this resource.
		 * 
		 * TODO: Check the user is trying to get redirected to a URL within this
		 * application and not somewhere else.
		 */
		$this->response->setBody('Redirecting')->getHeaders()->redirect($_GET['returnto']?? url());
	}
	
}
