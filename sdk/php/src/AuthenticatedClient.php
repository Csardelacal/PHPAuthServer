<?php namespace magic3w\phpauth\sdk;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use magic3w\http\url\reflection\URLReflection;
use magic3w\phpauth\sdk\SSO;
use magic3w\phpauth\sdk\Token;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use spitfire\exceptions\ApplicationException;

/*
 * The MIT License
 *
 * Copyright 2021 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The client provides a factory for requests that are contextualized to an authenticated
 * endpoint. This allows applications making use of these clients to abstract their behavior.
 *
 * This class should be extended, since all applications implementing them will be unable to share
 * their APIs, but will have common behavior within this class.
 *
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
abstract class AuthenticatedClient
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
	 * A client credential token allows the application to authenticate itself
	 * against the app if needed. Some endpoints require the application to be
	 * authenticated, some require the user to be authenticated.
	 *
	 * The application should construct the client with the appropriate credential
	 * for the user it wishes to "impersonate".
	 *
	 * This also means that a client created for a user cannot be used to access
	 * the application's own accounts.
	 *
	 * @var Token
	 */
	private $credentials;
	
	/**
	 * The URL providing access to the app. This endpoint should point to the base url
	 * of the app installation.
	 *
	 * @var string
	 */
	private $endpoint;
	
	/**
	 * Instances a new client. The client allows to construct a the app instance that
	 * is bound to a certain user or client.
	 *
	 * A URL with query parameters will have those stripped. The query parameters are
	 * not relayed to the server.
	 *
	 * @param SSO|Token $credentials
	 * @param string $endpoint
	 */
	public function __construct($credentials, $endpoint)
	{
		if ($credentials instanceof SSO) {
			$reflection = URLReflection::fromURL($endpoint);
			$appid = $reflection->getUser();
			
			$this->endpoint  = (string)$reflection->stripCredentials();
			$this->credentials = $credentials->credentials((int)$appid);
		}
		else {
			$this->credentials = $credentials;
			$this->endpoint = $endpoint;
		}
		
		$this->client = new Client([
			'base_uri' => $this->endpoint,
			'headers' => [
				'Authorization' => sprintf('Bearer %s', $this->credentials->getId())
			]
		]);
	}
	
	/**
	 *
	 * @return ClientInterface
	 */
	public function getClient() : ClientInterface
	{
		return $this->client;
	}
	
	public function send(RequestInterface $request) : ResponseInterface
	{
		return $this->client->sendRequest($request);
	}
}
