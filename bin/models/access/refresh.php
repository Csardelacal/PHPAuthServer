<?php namespace access;

use AuthAppModel;
use IntegerField;
use Reference;
use SessionModel;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use UserModel;
use function db;

/**
 * While structurally extremely similar to regular access tokens, the refresh token
 * has no ability to provide direct access to a resource, instead it needs to be
 * traded for an access token.
 *
 * Since a refresh token must never be used in a context where the regular access
 * token is accepted, and vice-versa, the system is way more stable whenever we
 * use two separate models, making it easier for the DBMS and providing powerful
 * isolation between the access and refresh tokens.
 *
 * @property string $type Either access or refresh
 * @property string $token The token identifier
 *
 * @property UserModel $owner The resource owner
 * @property AuthAppModel $client The application requesting access to the owner's information
 * @property AuthAppModel $audience The application containing the application owner's information
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
class RefreshModel extends Model
{
	
	const TOKEN_PREFIX = 'r_';
	const TOKEN_LENGTH = 50;
	const TOKEN_TTL = 63072000;
	
	/**
	 * The public TTL affects tokens issued to clients running inside the user agent. This
	 * includes SPA or native applications that run on the user's device. Sessions on these
	 * devices may be limited in order to prevent refresh tokens from being hijacked.
	 *
	 * Unlike private tokens, the lifetime of these is not extended beyond their initial
	 * expiration.
	 */
	const TOKEN_TTL_PUBLIC = 86400;
	
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
}
