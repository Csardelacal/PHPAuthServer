<?php namespace magic3w\phpauth\sdk\constraints;

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Lcobucci\JWT\Validation\SignedWith as SignedWithInterface;
use magic3w\phpauth\sdk\KeySet;

class SignedWithAnyOf implements SignedWithInterface
{
	
	/**
	 * 
	 * @var SignedWith[]
	 */
	private array $parents;

	public function __construct(Signer $signer, KeySet $keys)
	{
		$this->parents = array_map(
			fn($key) => new SignedWith($signer, $key), 
			$keys->all()
		);
	}
	
	/**
	 * 
	 * @param Token $token
	 * @throws ConstraintViolation
	 * @return void
	 */
	public function assert(Token $token): void
	{
		foreach ($this->parents as $constraint) {
			try { $constraint->assert($token); }
			catch (ConstraintViolation $e) {}
		}
		throw ConstraintViolation::error('No public key could be matched', $this);
	}
}
