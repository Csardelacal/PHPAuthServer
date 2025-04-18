<?php

use access\CodeModel;
use access\RefreshModel;
use access\TokenModel;
use client\CredentialModel;
use spitfire\exceptions\PublicException;
use TokenModel as GlobalTokenModel;

class TokenController extends BaseController
{
	
	public function index()
	{
		
		$query = db()->table('token')->getAll();
		if (!$this->isAdmin) {
			$query->addRestriction('user', $this->user);
		}
		
		$query->group()
				->addRestriction('expires', null, 'IS')
				->addRestriction('expires', time(), '>');
		
		$pages = new \spitfire\storage\database\pagination\Paginator($query);
		
		$this->view->set('pagination', $pages);
		$this->view->set('records', $pages->records());
	}
	
	public function create()
	{
		$appid   = isset($_POST['appID'])    ? $_POST['appID']     : $_GET['appID'];
		$secret  = isset($_POST['appSecret'])? $_POST['appSecret'] : $_GET['appSecret'];
		$expires = (int) isset($_GET['expires'])? $_GET['expires'] : 14400;
		
		$app = db()->table('authapp')->get('appID', $appid)
				  ->addRestriction('appSecret', $secret)->fetch();
		
		if (!$app) {
			throw new PublicException('No application found', 403);
		}
		
		$token = GlobalTokenModel::create($app, $expires);
		
		//Send the token to the view so it can render it
		$this->view->set('token', $token);
	}
	
	/**
	 * @todo Allow trading refresh tokens for fresh tokens
	 *
	 * @throws PublicException
	 */
	public function access()
	{
		$type    = $_POST['grant_type']?? 'code';
		$appid   = isset($_POST['client'])? $_POST['client'] : $_GET['client'];
		$secret  = $_POST['secret']?? null;
		
		/*
		 * Check if an app with the provided ID does indeed exist.
		 */
		$app = db()->table('authapp')->get('appID', $appid)->first();
		if (!$app) {
			throw new PublicException('No application found', 403);
		}
		
		/**
		 * We need a flag to determine whether the client itself authenticated itself
		 * securely. Authenticated clients get perks, like extended refresh tokens.
		 */
		$client_authenticated = false;
		
		/*
		 * In order to search for the application, we need to make sure that we're
		 * querying the secrets to find whether the application has an appropriate
		 * secret available.
		 *
		 * While I originally had a much leaner version that would just run a search
		 * for this.
		 *
		 * Which would run in a single query, the security of it was severely compromised
		 * by the fact that database searches are rather lenient. While this only meant a
		 * cost in enthropy, it still makes more sense to separate the queries and
		 * test the result in PHP.
		 */
		$credentials = db()->table(CredentialModel::class)
			->get('secret', $secret)
			->where('client', $app)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all();
		
		/**
		 * @todo Once legacy applications are gone, this can be removed.
		 */
		if ($app->appSecret !== null && $app->appSecret === $secret) {
			/*
			 * Legacy applications will have a set of credentials baked right into them. This
			 * also means that the applications are implied to be running on a server, since
			 * this has been the way we used to identify applications when they have all been
			 * server-side.
			 */
			$client_authenticated = true;
		}
		elseif ($credentials && !$credentials->extract('secret')->contains($secret)) {
			/*
			 * If the application was issued credentials, it MUST provide a valid credential.
			 */
			$client_authenticated = true;
		}
		elseif ($type === 'code' || $type === 'refresh_token') {
			/*
			 * In case the application is not issued credentials, because it runs on a
			 * user controlled device only, we can accept an "unauthenticated" request.
			 *
			 * This obviously means that we cannot issue client credentials to the application
			 * since the application itself has no 'willpower' and is tethered to the user.
			 */
		}
		else {
			throw new PublicException('Invalid credentials', 403);
		}
		
		if ($type === 'code') {
			/*
			 * Read the code the client sent
			 */
			$code = db()->table(CodeModel::class)
				->get('code', $_POST['code']?? null)
				->where('expires', '>', time())
				->first(true);
			
			/*
			 * Verify that the code the client sent, is actually the client's code
			 */
			if ($code->client->_id !== $app->_id) {
				throw new PublicException('Code is for another client', 403);
			}
			
			/*
			 * Check the code verifier
			 */
			list($algo, $hash) = explode(':', $code->challenge);
			$known_algos = [
				'S256' => 'sha256'
			];
			
			if (hash($known_algos[$algo], $_POST['verifier']) !== $hash) {
				throw new PublicException('Hash failed', 403);
			}
			
			/*
			 *
			 */
			$code->expires = time();
			$code->store();
			
			/*
			 * Instance a token that can be sent to the client to provide them access
			 * to the resources of the owner.
			 */
			$token = db()->table(TokenModel::class)->newRecord();
			$token->session = $code->session;
			$token->owner   = $code->user;
			$token->audience = $code->audience;
			$token->client  = $app;
			$token->store();
			
			/**
			 * Calculate the TTL for the token. If the client is authenticated we will trust
			 * it for longer.
			 */
			$ttl = ($client_authenticated? RefreshModel::TOKEN_TTL : RefreshModel::TOKEN_TTL_PUBLIC);
			
			$refresh = db()->table(RefreshModel::class)->newRecord();
			$refresh->session = $code->session;
			$refresh->owner   = $code->user;
			$refresh->audience = $code->audience;
			$refresh->client  = $app;
			$refresh->expires = time() + $ttl;
			$refresh->store();
			
			/**
			 *
			 * @todo Remove this code once the legacy systems are finally gone
			 * @deprecated
			 */
			$legacy = db()->table(GlobalTokenModel::class)->newRecord();
			$legacy->token   = $token->token;
			$legacy->user    = $token->owner;
			$legacy->app     = $token->client;
			$legacy->expires = $token->expires;
			$legacy->extends = false;
			$legacy->ttl     = $token->expires - time();
			$legacy->store();
		}
		/**
		 * Applications can request a token for themselves. When they do so, we call this token client
		 * credentials. This means the token will have no user claim. When the application uses this token,
		 * servers will know that the application is acting on it's own behalf.
		 */
		elseif ($type === 'client_credentials') {
			$audience = $_GET['audience']?
			db()->table(AuthAppModel::class)->get('appID', $_GET['audience'])->first(true) : null;
			
			$token = db()->table(TokenModel::class)->newRecord();
			$token->session  = null;
			$token->owner    = null;
			$token->client   = $app;
			$token->audience = $audience;
			$token->store();
			
			/**
			 * Client credentials do not provide refresh tokens, since applications are not
			 * inconvenienced by the need to reauthenticate the token.
			 */
			$refresh = null;
		}
		elseif ($type === 'refresh_token') {
			
			/**
			 * The refresh token must be sent with the request and be a string.
			 * @todo When SF 2020 is introduced, this needs to be replaced with assume()
			 */
			assert(is_string($_POST['refresh_token']));
			
			/**
			 * @var string
			 */
			$_provided = $_POST['refresh_token'];
			
			/**
			 * The provided refresh token. The application MUST use this to validate
			 * the client's claims.
			 *
			 * @var RefreshModel
			 */
			$provided = db()->table(RefreshModel::class)->get('token', $_provided)->first(true);
			
			if ($provided->client->appID != $app->appID) {
				throw new PublicException('Tried refreshing a token owned by a different client', 403);
			}
			
			/**
			 * If the refresh token has expired, or has been used to refresh another token, the renewal
			 * should fail. This is only the case in the event of the application not being authenticated.
			 *
			 * @todo Flag the session as potentially compromised if there was an attempt to use a
			 * token that was expired (this indicates a potential attack).
			 */
			if ($provided->expires < time()) {
				throw new PublicException('Refresh token has already expired', 401);
			}
			
			/**
			 * We cannot issue new tokens to suspended users. These tokens should be already destroyed by the
			 * suspension mechanism.
			 * 
			 * @todo Add logging to record this impossible condition
			 */
			if ($provided->owner->isSuspended()) {
				throw new PublicException('User is suspended. Token cannot be refreshed', 401);
			}
			
			#TODO: This code could be extracted into an helper that could be pulled
			#in via service providers to reduce the amount of code duplication.
			/*
			 * Instance a token that can be sent to the client to provide them access
			 * to the resources of the owner.
			 */
			$token = db()->table(TokenModel::class)->newRecord();
			$token->session = $provided->session;
			$token->owner   = $provided->owner;
			$token->audience = $provided->audience;
			$token->client  = $provided->client;
			$token->store();
			
			$refresh = db()->table(RefreshModel::class)->newRecord();
			$refresh->session = $provided->session;
			$refresh->owner   = $provided->owner;
			$refresh->audience = $provided->audience;
			$refresh->client  = $provided->client;
			$refresh->expires = $client_authenticated? time() + RefreshModel::TOKEN_TTL : $provided->expires;
			$refresh->store();
			
			/**
			 *
			 * @todo Remove this code once the legacy systems are finally gone
			 * @deprecated
			 */
			$legacy = db()->table(GlobalTokenModel::class)->newRecord();
			$legacy->token   = $token->token;
			$legacy->user    = $token->owner;
			$legacy->app     = $token->client;
			$legacy->expires = $token->expires;
			$legacy->extends = false;
			$legacy->ttl     = $token->expires - time();
			$legacy->store();
			
			/**
			 * The old token can now be expired, allowing the application to perform token rotation.
			 * This measure increases the chances of preventing a user's credential being stolen by an
			 * attacker.
			 */
			if (!$client_authenticated) {
				$provided->expires = time();
				$provided->store();
			}
		}
		else {
			throw new PublicException('Invalid grant_type selected', 400);
		}
		
		//Send the token to the view so it can render it
		$this->view->set('token', $token);
		$this->view->set('refresh', $refresh);
		$this->view->set('session', $this->session);
	}
	
	/**
	 *
	 * @param string $tokenid
	 */
	public function end($tokenid)
	{
		$token = db()->table('token')->get('token', $tokenid)->fetch();
		
		if (!$token) {
			throw new PublicException('No token found', 404);
		}
		if ($token->expires && $token->expires < time()) {
			throw new PublicException('Token already expired', 403);
		}
		
		$token->expires = time();
		$token->store();
		
		$this->response->setBody('Redirect');
		$this->response->getHeaders()->redirect(new URL('token', array('message' => 'ended')));
	}
}
