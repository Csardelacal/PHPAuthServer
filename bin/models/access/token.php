<?php namespace access;

use AuthAppModel;
use DateTimeImmutable;
use IntegerField;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Lcobucci\JWT\Token\Builder;
use Reference;
use SessionModel;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use SysSettingModel;
use UserModel;
use function db;

/**
 * An access token connects up to three parties in a relationship that authenticates
 * the following:
 *
 * * A resource owner, who owns the resources on the server, and wishes to grant access
 *   to the client. This is generally a human.
 * * A client, an application that wishes to retrieve data or issue commands to the server.
 * * A server. An application that holds the owner's information and wishes to authenticate
 *   the client's requests.
 *
 * These tokens can be of two kinds, access tokens or refresh tokens. When a "public"
 * application issues an access token, no refresh token is generated. A private
 * application may request a refresh token to be issued.
 *
 * Depending on which fields are populated, the token may be used for different
 * scenarios.
 *
 * * A token may have no owner, which means that the owner is implied to be the server.
 * * A token may have no client nor owner, making it a client-credential so that an
 *   application can rate limit clients
 * * If the client and server are the same app, the token is a session token and used to
 *   log the user into the application.
 *
 *
 *
 * @property string $type Either access or refresh
 * @property string $token The token identifier
 *
 * @property UserModel $owner The resource owner
 * @property AuthAppModel $client The application requesting access to the owner's information
 * @property AuthAppModel $server The application containing the application owner's information
 * @property string $scopes A comma separated list of contexts the client wishes to have access to
 *
 * @property int $created The time the token was created
 * @property int $expires The time the token is no longer valid
 * @property int $ttl The amount of seconds this token was set to be valid
 *
 * @property SessionModel $session The session that spawned this token
 *
 * @todo Make an array adapter for contexts so they get automatically separated
 */
class TokenModel extends Model
{
	
	const TOKEN_PREFIX = 't_';
	const TOKEN_LENGTH = 50;
	const TOKEN_TTL = 1800;
	
	public function definitions(Schema $schema)
	{
		$schema->token   = new StringField(self::TOKEN_LENGTH);
		
		$schema->owner   = new Reference('user');
		$schema->client  = new Reference('authapp');
		$schema->audience = new Reference('authapp');
		
		$schema->scopes  = new StringField(255);
		
		$schema->created = new IntegerField(true);
		$schema->expires = new IntegerField(true);
		$schema->ttl     = new IntegerField(true);
		
		$schema->session = new Reference(SessionModel::class);
		
		$schema->token->setUnique(true);
	}
	
	public function onbeforesave(): void
	{
		parent::onbeforesave();
		
		/*
		 * If the token happened to be new, and therefore had no token-id assigned,
		 * we generate a new, unique token identifier for this one.
		 */
		if (!$this->token) {
			do {
				$this->token = substr(self::TOKEN_PREFIX . bin2hex(random_bytes(25)), 0, self::TOKEN_LENGTH);
			}
			while (db()->table('access\token')->get('token', $this->token)->first());
		}
		
		/*
		 * If the token has no creation date we assume that it has never been stored
		 * before and record the creation time.
		 */
		if (!$this->created) {
			$this->created = time();
		}
		
		/*
		 * Set the expiration time to a timestamp in the future (by default 30 minutes)
		 * if the expiration was not explicitly set before.
		 */
		if (!$this->expires) {
			$this->expires = time() + self::TOKEN_TTL;
		}
	}
	
	public function __toString()
	{
		
		try {
			return $this->jwtrs256();
		}
		catch (\Exception $e) {
			throw new \spitfire\exceptions\PublicException($e->getMessage());
		}
		
		
		/*@var $jwt Configuration*/
		$credential = db()->table('client\credential')
			->get('expires', null)
			->where('client', $this->client)
			->first(true);
		
		$time = new DateTimeImmutable();
		$jwt = Configuration::forSymmetricSigner(
			new Sha256(),
			InMemory::base64Encoded($credential->secret)
		);
		
		return $jwt->builder()
			->withClaim('for', $this->client->appID)
			->permittedFor($this->audience->appID)
			->issuedAt($time->setTimestamp($this->created))
			->identifiedBy($this->token)
			->expiresAt($time->setTimestamp($this->expires))
			->withClaim('uid', $this->owner->_id)
			->getToken($jwt->signer(), $jwt->signingKey())
			->toString();
	}
	
	public function jwtrs256() : string
	{
		
		$credential = db()->table('key')->get('expires', null)->first(true);
		#TODO
		# $issuer     = db()->table('authapp')->get('_id', SysSettingModel::getValue('app.self'))->first(true);
		
		$time = new DateTimeImmutable();
		$jwt = new Builder(new JoseEncoder, ChainedFormatter::default());
		$jwt = Configuration::forAsymmetricSigner(
			new RsaSha256(),
			InMemory::plainText($credential->private),
			/**
			 * @todo Replace with proper mechanism
			 */
			InMemory::base64Encoded('mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw=')
		);
		
		$suspension = db()
			->table('user\suspension')
			->get('user', $this->owner)
			->where('expires', '>', time())
			->first();
		
		return $jwt->builder()
			#->issuedBy($issuer->appID)
			->withClaim('for', $this->client->appID)
			#->permittedFor($this->audience->appID)
			->issuedAt($time->setTimestamp($this->created))
			->identifiedBy($this->token)
			->expiresAt($time->setTimestamp($this->expires))
			->withClaim('uid', $this->owner->_id)
			->withClaim('restricted', !$this->owner->verified || $suspension)
			->getToken($jwt->signer(), $jwt->signingKey())
			->toString();
	}
}
