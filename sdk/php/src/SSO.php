<?php namespace magic3w\phpauth\sdk;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use magic3w\http\url\reflection\URLReflection;
use magic3w\phpauth\sdk\signature\Hash;
use magic3w\phpauth\sdk\signature\Signature;

class SSO
{
	
	/**
	 * This client will be in charge of sending our requests to the server.
	 *
	 * The change to guzzle is requiring us to reconsider the way we handle HTTP requests,
	 * previously we would have used this class to create a request and have the applications
	 * relying on it change the request as needed.
	 *
	 * This is currently not available, since Guzzle requires the client object to be
	 * used in tandem with the request objects.
	 *
	 * @var Client
	 */
	private $client;
	
	/**
	 *
	 * @var string
	 */
	private $endpoint;
	
	/**
	 *
	 * @var int
	 */
	private $appId;
	
	/**
	 *
	 * @var non-empty-string
	 */
	private string $appSecret;
	
	/**
	 *
	 * @var non-empty-string|null
	 */
	private ?string $publicKey;
	
	/**
	 *
	 * @param string $credentials
	 * @param null|non-empty-string $publicKey
	 */
	public function __construct(string $credentials, ?string $publicKey = null)
	{
		$reflection = URLReflection::fromURL($credentials);
		$path = $reflection->getPath();
		$host = $reflection->getProtocol() . '://' . $reflection->getHostname() . ':' . $reflection->getPort();
		
		$secret = $reflection->getPassword();
		assert(!empty($secret));
		
		$this->endpoint  = rtrim($host . $path, '/');
		$this->appId     = (int)$reflection->getUser();
		$this->appSecret = $secret;
		
		$this->client = new Client(['base_uri' => $this->endpoint]);
		$this->publicKey = $publicKey;
	}
	
	/**
	 * Generates a URL to direct the user agent to in order to initiate
	 * the authentication. Please note that the oAuth 2.0 protocol uses the user
	 * agent for this 'request', therefore the server itself is not performing
	 * the request at all.
	 *
	 * I would like to avoid using the word request for whatever is happening
	 * at this stage of the connection.
	 *
	 * @param string $state
	 * @param string $verifier_challenge
	 * @param string $returnto
	 * @param int|null $audience The app ID of the application this client wishes to access data from. This may
	 *        be null if the application wishes to access the client account on PHPAS.
	 * @return string
	 */
	public function makeAccessCodeRedirect(
		string $state,
		string $verifier_challenge,
		string $returnto,
		int $audience = null
	) : string {
		
		$query = [
			'response_type' => 'code',
			'client_id' => $this->appId,
			'audience' => (string)$audience,
			'state' => $state,
			'redirect_uri' => $returnto,
			'code_challenge' => hash('sha256', $verifier_challenge),
			'code_challenge_method' => 'S256'
		];
		
		$request = URLReflection::fromURL(sprintf('%s/auth/oauth2', $this->endpoint));
		$request->setQueryString($query);
		
		return strval($request);
	}
	
	/**
	 * Tokens can be retrieved using three different mechanisms.
	 *
	 * 1. Provide an access code that a user generated. This is used during the oAuth flow
	 * 2. Provide application specific credentials, yields a client token
	 * 3. Provide a refresh token.
	 *
	 * This mechanism intends to make it simple for the applications to generate new tokens
	 * for the first scenario, by providing a code and a verifier to the table.
	 *
	 * @param string $code
	 * @param string $verifier
	 * @param int|null $audience
	 * @return array{'access': Token, 'refresh': RefreshToken}
	 */
	public function token(string $code, string $verifier, int $audience = null) : array
	{
		$post = [
			['name' => 'type', 'contents' => 'code'],
			['name' => 'code', 'contents' => $code],
			['name' => 'client', 'contents' => (string)$this->getAppId()],
			['name' => 'audience', 'contents' => (string)$audience],
			['name' => 'secret', 'contents' => $this->appSecret],
			['name' => 'verifier', 'contents' => $verifier]
		];
		
		$response = $this->request('/token/access.json', $post);
		
		/**
		 * These assertions are only executed in a development environment, allowing servers running
		 * in production to ignore these and assume that the response they received from the other
		 * party is safe.
		 */
		assert(is_object($response) && isset($response->tokens));
		assert($response->tokens->access && $response->tokens->access->token);
		assert($response->tokens->refresh && $response->tokens->refresh->token);
		
		$access = $response->tokens->access;
		
		return [
			'access'  => new Token($this->parse($access->token), $access->expires),
			'refresh' => new RefreshToken($response->tokens->refresh->token, $response->tokens->refresh->expires)
		];
	}
	
	public function parse(string $jwt) : UnencryptedToken
	{
		$parser = new Parser(new JoseEncoder);
		$validator = new Validator();
		
		$parsed = $parser->parse($jwt);
		
		if ($this->publicKey !== null) {
			assert(!empty($this->publicKey));
			$validator->validate($parsed, new SignedWith(new RsaSha256, InMemory::plainText($this->publicKey)));
		}
		else {
			assert(!empty($this->appSecret));
			$validator->validate($parsed, new SignedWith(new Sha256, InMemory::plainText($this->appSecret)));
		}
		
		assert($parsed instanceof UnencryptedToken);
		return $parsed;
	}
	
	/**
	 * Refreshes an access and refresh token by passing a refresh token to the
	 * system as grant.
	 *
	 * The token must be a string, if you held onto the `Token` object you received
	 * from the API, you can extract the code by calling getToken.
	 *
	 * @param string $token
	 * @return array{0: Token, 1: RefreshToken}
	 */
	public function refresh(string $token) : array
	{
		return $this->renew(new RefreshToken($token, null));
	}
	
	/**
	 * Returns an access token that allows the application to access it's own credentials
	 * on the server.
	 *
	 * @param int|null $audience
	 * @return Token
	 */
	public function credentials(int $audience = null)
	{
		
		$post = [
			['name' => 'type', 'contents' => 'client_credentials'],
			['name' => 'client', 'contents' => (string)$this->getAppId()],
			['name' => 'audience', 'contents' => (string)$audience],
			['name' => 'secret', 'contents' => $this->appSecret],
		];
		
		$response = $this->request('/token/access.json', $post);
		
		/**
		 * These assertions are only executed in a development environment, allowing servers running
		 * in production to ignore these and assume that the response they received from the other
		 * party is safe.
		 */
		assert(is_object($response) && isset($response->tokens));
		assert($response->tokens->access && $response->tokens->access->token);
		
		return new Token($response->tokens->access->token, $response->tokens->access->expires);
	}
	
	/**
	 * The base URL of the PHPAuth Server
	 *
	 * @return string
	 */
	public function getEndpoint() : string
	{
		return $this->endpoint;
	}
	
	/**
	 * The app id of the application that is authenticating this client.
	 *
	 * @return int
	 */
	public function getAppId() : int
	{
		return $this->appId;
	}
	
	/**
	 * Returns the secret the system is using to communicate with the server. This can be
	 * used by refresh tokens to renew their lease.
	 *
	 * @return string
	 */
	public function getSecret()
	{
		return $this->appSecret;
	}
	
	/**
	 *
	 * @return object
	 */
	public function getGroupList() : object
	{
		$resp = $this->request('/group/index.json');
		assert(isset($resp->payload));
		return $resp->payload;
	}
	
	/**
	 *
	 * @param string $id
	 * @return object
	 */
	public function getGroup(string $id) : object
	{
		$resp = $this->request('/group/detail/' . $id . '.json');
		assert(isset($resp->payload));
		return $resp->payload;
	}
	
	
	/**
	 * This method allows your client to push a custom scope onto the server. This scope
	 * can then be used by third party applications to request access to parts of the user's
	 * data that you requested be fenced off.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $description
	 * @param string $icon
	 * @return void
	 */
	public function putScope($id, $name, $description, $icon = null) : void
	{
		$post = [
			['name' => 'token', 'contents' => (string)$this->credentials()->getId()],
			['name' => 'name', 'contents' => $name],
			['name' => 'description', 'contents' => $description],
			['name' => 'icon', 'contents' => Utils::tryFopen($icon, 'r'), 'filename' => basename($icon)]
		];
		
		$this->request(sprintf('/scope/create/%s.json', $id), $post);
	}
	
	/**
	 * Generate a logout link. The logout flow is best described as follows:
	 *
	 * 1. Client generates a logout link, which contains a return URL to direct the user to on successful logout
	 * 2. Resource owner is directed to the logout location
	 * 3. Authentication server terminates the session, and all authenticated tokens that depend on it
	 * 4. Authentication directs the resource owner to the return URL
	 * 5. Client destroys the session on their end.
	 *
	 * Alternatively, the client can execute the 5fth point first.
	 *
	 * Asynchronously, the Authentication server will start notifying all applications
	 * using tokens in the current session to destroy them.
	 *
	 * Invoking this endpoint is optional, it only ends the session on the authentication
	 * server, allowing your application to display a "log out" from all
	 *
	 * @param Token $token
	 * @param string $returnto
	 * @return string
	 */
	public function getLogoutLink(Token $token, string $returnto) : string
	{
		$query = http_build_query(['returnto' => $returnto, 'token' => $token->getId()]);
		return $this->endpoint . '/user/logout?' . $query;
	}
	
	
	/**
	 * The renew method
	 *
	 * @param RefreshToken $token
	 * @return array{0: Token, 1: RefreshToken}
	 */
	public function renew(RefreshToken $token) : array
	{
		$post = [
			['name' => 'grant_type', 'contents' => 'refresh_token'],
			['name' => 'refresh_token', 'contents' => $token->getId()],
			['name' => 'client', 'contents' => (string)$this->getAppId()],
			['name' => 'secret', 'contents' => $this->getSecret()]
		];
		
		$response = $this->request('/token/access.json', $post);
		
		/**
		 * These assertions are only executed in a development environment, allowing servers running
		 * in production to ignore these and assume that the response they received from the other
		 * party is safe.
		 */
		assert(is_object($response) && isset($response->tokens));
		assert($response->tokens->access && $response->tokens->access->token);
		assert($response->tokens->refresh && $response->tokens->refresh->token);
		
		$parser = new Parser(new JoseEncoder);
		$validator = new Validator();
		
		$access = $response->tokens->access;
		$parsed = $parser->parse($access->token);
		
		$validator->validate($parsed, new SignedWith(new RsaSha256, InMemory::plainText($this->publicKey)));
		
		assert($parsed instanceof UnencryptedToken);
		
		return [
			new Token($parsed, $access->expires),
			new RefreshToken($response->tokens->refresh->token, $response->tokens->refresh->expires)
		];
	}
	
	/**
	 * Prepares a authenticated request that all objects can use to interact with
	 * the API.
	 *
	 * @throws GuzzleException
	 * @param string $url
	 * @param mixed[][] $payload
	 * @param string[] $query
	 * @param string[] $headers
	 * @return object The raw response from the server (JSON decoded).
	 */
	private function request($url, array $payload = [], array $query = [], array $headers = []) : object
	{
		/**
		 * Send a request to the server and harvest the response.
		 */
		$response = $this->client->post(
			$url,
			[
				'headers' => $headers,
				'multipart' => $payload,
				'query' => $query
			]
		);
		
		/**
		 * Parse the server's response. Please note that the server will ALWAYS reply to API requests with
		 * valid json. If this is not the case, the request went wrong or the server is misconfigured.
		 *
		 * This means that we cannot continue with the execution.
		 */
		$responsePayload = json_decode((string)$response->getBody(), false, 512, JSON_THROW_ON_ERROR);
		
		return $responsePayload;
	}
	
	public function getBaseURL() : string
	{
		return $this->endpoint;
	}
	
	/**
	 *
	 * @param string|int $username
	 * @return User
	 */
	public function getUser(string|int $username) : User
	{
		
		if (!$username) {
			throw new Exception('Valid user id needed');
		}
		
		/**
		 *
		 * @var object
		 */
		$request = $this->request(
			$this->endpoint . '/user/detail/' . $username . '.json',
			[],
			['signature' => (string)$this->makeSignature()]
		);
		
		/*
		 * Fetch the JSON message from the endpoint. This should tell us whether
		 * the request was a success.
		 */
		$data = $request->payload;
		
		return new User(
			$data->id,
			$data->username,
			$data->aliases,
			$data->groups,
			$data->verified,
			$data->registered_unix,
			$data->attributes,
			$data->avatar
		);
	}
	
	/**
	 *
	 * @param int $target
	 * @param string[] $contexts
	 * @todo Remove
	 * @deprecated
	 */
	public function makeSignature($target = null, $contexts = []) : Signature
	{
		$signature = new Signature(Hash::ALGO_DEFAULT, $this->appId, $this->appSecret, $target, $contexts, time() + 3600);
		return $signature;
	}
}
