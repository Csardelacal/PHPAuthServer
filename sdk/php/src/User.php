<?php namespace magic3w\phpauth\sdk;

use Exception;

class User
{
	
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * The current (and therefore canonical) username for this account.
	 *
	 * @var string
	 */
	private $username;
	
	/**
	 * A list of known aliases that the user has had.
	 *
	 * @var string[]
	 */
	private $aliases;
	
	/**
	 * A list of groups the user is a part of. Auth groups provide the user with certain authority.
	 *
	 * @var string[]
	 */
	private $groups;
	
	/**
	 * Indicates whether the user confirmed their account using an email address.
	 *
	 * @var bool
	 */
	private $verified;
	
	/**
	 * Timestamp of the user's sign up.
	 *
	 * @var int
	 */
	private $registered;
	
	/**
	 *
	 * @var object
	 */
	private $avatar;
	private $attributes;
	
	/**
	 *
	 * @param int $id
	 * @param string $username
	 * @param string[] $aliases
	 * @param string[] $groups
	 * @param bool $verified
	 * @param int $registered
	 * @param object $attributes
	 * @param object $avatar
	 */
	public function __construct(
		int $id,
		string $username,
		array $aliases,
		array $groups,
		bool $verified,
		int $registered,
		object $attributes,
		object $avatar
	) {
		$this->id = $id;
		$this->username = $username;
		$this->aliases = $aliases;
		$this->groups = $groups;
		$this->verified = $verified;
		$this->registered = $registered;
		$this->attributes = $attributes;
		$this->avatar = $avatar;
	}
	
	/**
	 * Returns the user's id. This is immutable and should be used to refer to the account
	 * inside applications
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * The username selected by the account holder. Please note that this is mutable and the
	 * user can change it.
	 *
	 * @return string
	 */
	public function getUsername() : string
	{
		return $this->username;
	}
	
	/**
	 * Returns the avatar for the user in the selected size.
	 *
	 * @return string
	 */
	public function getAvatar(int $size) : string
	{
		return $this->avatar->{$size};
	}
	
	/**
	 * The array of groups this user is a part of.
	 *
	 * @return string[]
	 */
	public function getGroups() : array
	{
		return $this->groups;
	}
	
	public function getAttribute($name)
	{
		if (!isset($this->attributes->{$name})) {
			throw new Exception("Attribute {$name} is not readable");
		}
		if (!isset($this->attributes->{$name}->value)) {
			throw new Exception("Attribute {$name} is not set");
		}
		if (!is_object($this->attributes->{$name}->value)) {
			return $this->attributes->{$name};
		}
		
		$data = $this->attributes->{$name}->value;
		
		switch ($data->type) {
			case 'file':
				return new File($data->preview, $data->download);
			default:
				throw new Exception('Invalid data type');
		}
	}
}
