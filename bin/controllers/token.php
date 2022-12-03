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
		
		$pages = new \spitfire\storage\database\pagination\Paginator($query);
		
		$this->view->set('pagination', $pages);
		$this->view->set('records',    $pages->records());
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
		if (!$app) { throw new PublicException('No application found', 403); }
		
		/*
		 * In order to search for the application, we need to make sure that we're
		 * querying the secrets to find whether the application has an appropriate
		 * secret available.
		 * 
		 * While I originally had a much leaner version that would just run a search
		 * for this:
		 * 
		 * $app = db()->table('authapp')->get('appID', $appid)
		 *   ->addRestriction('credentials', db()->table('client\credential')->get('secret', $secret)->group()->where('expires', null)->where('expires', '<', time()))->fetch();
		 * 
		 * Which would run in a single query, the security of it was severely compromised
		 * by the fact that database searches are rather lenient. While this only meant a 
		 * cost in enthropy, it still makes more sense to separate the queries and 
		 * test the result in PHP.
		 */
		$credentials = db()->table('client\credential')
			->get('secret', $secret)
			->where('client', $app)
			->group()->where('expires', null)->where('expires', '>', time())->endGroup()
			->all();
		
		if ($app->appSecret !== null && $app->appSecret === $secret) {
			/*
			 * Legacy applications will have a set of credentials baked right into them. This
			 * also means that the applications are implied to be running on a server, since
			 * this has been the way we used to identify applications when they have all been
			 * server-side.
			 */
		}
		elseif ($credentials && !$credentials->extract('secret')->contains($secret)) {
			/*
			 * If the application was issued credentials, it MUST provide a valid credential.
			 */
		}
		elseif($type === 'code' || $type === 'refresh_token') {
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
		
		if ($type === 'code')
		{
			/*
			 * Read the code the client sent
			 */
			$code = db()->table('access\code')->get('code', $_POST['code']?? null)->where('expires', '>', time())->first(true);

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

			#TODO: This code could be extracted into an helper that could be pulled 
			#in via service providers to reduce the amount of code duplication.
			/*
			 * Instance a token that can be sent to the client to provide them access
			 * to the resources of the owner.
			 */
			$token = db()->table('access\token')->newRecord();
			$token->session = $code->session;
			$token->owner   = $code->user;
			$token->audience = $code->audience;
			$token->client  = $app;
			$token->store();
			
			$refresh = db()->table('access\refresh')->newRecord();
			$refresh->session = $code->session;
			$refresh->owner   = $code->user;
			$refresh->audience = $code->audience;
			$refresh->client  = $app;
			$refresh->store();
			
			/**
			 * 
			 * @todo Remove this code once the legacy systems are finally gone
			 * @deprecated
			 */
			$legacy = db()->table('token')->newRecord();
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
			
			$token = db()->table('access\token')->newRecord();
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
			throw new PublicException('Nah');
			/**
			 * The provided refresh token. The application MUST use this to validate
			 * the client's claims.
			 * 
			 * @var RefreshModel
			 */
			$_provided = $_POST['refresh_token']?? null;
			$provided = db()->table('access\refresh')->get('token', $_provided)->first(true);
			
			if ($provided->client->appID != $app->appID) {
				throw new PublicException('Tried refreshing a token owned by a different client', 403);
			}
			
			#TODO: This code could be extracted into an helper that could be pulled 
			#in via service providers to reduce the amount of code duplication.
			/*
			 * Instance a token that can be sent to the client to provide them access
			 * to the resources of the owner.
			 */
			$token = db()->table('access\token')->newRecord();
			$token->session = $provided->session;
			$token->owner   = $provided->owner;
			$token->audience = $provided->audience;
			$token->client  = $provided->client;
			$token->store();
			
			$refresh = db()->table('access\refresh')->newRecord();
			$refresh->session = $provided->session;
			$refresh->owner   = $provided->owner;
			$refresh->audience = $provided->audience;
			$refresh->client  = $provided->client;
			$refresh->store();
			
			/**
			 * 
			 * @todo Remove this code once the legacy systems are finally gone
			 * @deprecated
			 */
			$legacy = db()->table('token')->newRecord();
			$legacy->token   = $token->token;
			$legacy->user    = $token->owner;
			$legacy->app     = $token->client;
			$legacy->expires = $token->expires;
			$legacy->extends = false;
			$legacy->ttl     = $token->expires - time();
			$legacy->store();
		}
		else {
			throw new PublicException('Invalid grant_type selected', 400);
		}
		
		//Send the token to the view so it can render it
		$this->view->set('token', $token);
		$this->view->set('refresh', $refresh);
		$this->view->set('session', $provided->session);
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
