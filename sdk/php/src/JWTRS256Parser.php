<?php

declare(strict_types=1);
namespace magic3w\phpauth\sdk;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Validator;
use magic3w\phpauth\sdk\constraints\SignedWithAnyOf;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Lcobucci\JWT\Validation\Constraint\HasClaim;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

class JWTRS256Parser
{
	
	/**
	 * @var KeySet
	 */
	private KeySet $keySet;
	
	
	public function __construct(KeySet $keySet)
	{
		$this->keySet = $keySet;
	}
	
	public function parse(string $jwt) : UnencryptedToken
	{
		$parser = new Parser(new JoseEncoder);
		$validator = new Validator();
		
		$parsed = $parser->parse($jwt);
		$validator->validate(
			$parsed,
			new SignedWithAnyOf(new RsaSha256, $this->keySet),
			new StrictValidAt(new SystemClock(new \DateTimeZone('UTC')), new \DateInterval('PT0S')),
			new HasClaim('for')
		);
		
		assert($parsed instanceof UnencryptedToken);
		return $parsed;
	}
}
