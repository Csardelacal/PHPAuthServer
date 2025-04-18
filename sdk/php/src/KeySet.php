<?php namespace magic3w\phpauth\sdk;

use GuzzleHttp\Client;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use magic3w\http\url\reflection\URLReflection;

/**
 * 
 * @todo Document
 * @todo Add an overarching client that allows to fetch the
 * keys with a cache and whatnot. Maybe even a service provider
 * or similar.
 */
class KeySet
{
	
	/**
	 * 
	 * @var Key[]
	 */
	private array $keys;
	
	/**
	 * 
	 * @return Key[]
	 */
	public function all() : array
	{
		return $this->keys;
	}
	
	public static function fromServer(string $credentials) : KeySet
	{
		
		$reflection = URLReflection::fromURL($credentials);
		$base = $reflection->stripCredentials();
		
		$client = new Client(['base_uri' => $base]);
		
		$openid = json_decode(
			$client->get('/.well-known/openid-configuration')->getBody(),
			false,
			512,
			JSON_THROW_ON_ERROR
		);
		
		$keys = json_decode(
			$client->get($openid->jwks_uri)->getBody(),
			false,
			512,
			JSON_THROW_ON_ERROR
		);
		
		$_return = new KeySet;
		$_return->keys = array_map(
			fn($raw) => InMemory::plainText($raw->pem),
			$keys->keys
		);
		
		return $_return;
	}
	
	public static function fromArray(array $keys) : KeySet
	{
		$_return = new KeySet;
		$_return->keys = array_map(
			fn($raw) => InMemory::plainText($raw),
			$keys
		);
		
		return $_return;
	}
}
