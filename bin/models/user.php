<?php

use \spitfire\exceptions\PrivateException;

class UserModel extends spitfire\Model
{
	
	
	public function definitions(\spitfire\storage\database\Schema $schema)
	{
		$schema->password  = new StringField(90);
		$schema->email     = new StringField(50);
		
		$schema->verified  = new BooleanField();
		$schema->created   = new IntegerField();
		$schema->picture   = new FileField();
		
		$schema->modified  = new IntegerField();
		$schema->disabled  = new IntegerField(); #Timestamp the user was disabled
		
		$schema->usernames = new ChildrenField('username', 'user');
		$schema->attributes= new ChildrenField('user\attribute', 'user');
		$schema->memberof  = new ChildrenField('user\group', 'user');
		
		$schema->email->setUnique(true);
	}
	
	/**
	 * We do replace the insert function to add the password hashing functionality.
	 * By doing so, we reduce the need for the user to manually encrypt the
	 * password.
	 *
	 * @todo This secxtion makes no sense. Why would there be an unencrypted password in
	 * the model?
	 *
	 * @throws PrivateException
	 */
	public function insert()
	{
		
		if (function_exists('password_hash')) {
			$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		} else {
			throw new PrivateException(
				'Password hashing algorithm is missing. Please check your PHP version',
				1602270011
			);
		}
		
		return parent::insert();
	}
	
	public function onbeforesave()
	{
		$this->modified = time();
	}
	
	public function checkPassword($password)
	{
		
		/*
		 * If there is no password hashing mechanism, we should abort ASAP. Since
		 * nothing the application could do then would make any sense.
		 */
		if (!function_exists('password_hash')) {
			throw new PrivateException(
				'Password hashing algorithm is missing. Please check your PHP version',
				1602270012
			);
		}
		
		/*
		 * If the password doesn't match, then we need to tell the user that whatever
		 * he wrote into the form was not acceptable.
		 */
		if (!password_verify($password, $this->password)) {
			return false;
		}
		
		/*
		 * Getting here means the password was correct, we can now ensure that it's
		 * up to speed with the latest encryption and rehash it in case it's needed.
		 */
		if (password_needs_rehash($this->password, PASSWORD_DEFAULT)) {
			$this->password = password_hash($password, PASSWORD_DEFAULT);
			$this->store();
		}
		
		return true;
	}
	
	/**
	 * Set a new password for the user. The method will automatically encrypt it
	 * in an according manner.
	 *
	 * Please note that this function does not invoke the store() function. You
	 * need to do that manually.
	 *
	 * @param string $password
	 * @throws PrivateException
	 *
	 * @return self
	 */
	public function setPassword($password)
	{
		/*
		 * If there is no password hashing mechanism, we should abort ASAP. Since
		 * nothing the application could do then would make any sense.
		 */
		if (!function_exists('password_hash')) {
			throw new PrivateException(
				'Password hashing algorithm is missing. Please check your PHP version',
				1604251501
			);
		}
		
		/*
		 * Hash and set the new password. Please note that this function does not
		 * invoke the store() function. This prevents the method from being called
		 * by accident.
		 */
		$this->password = password_hash($password, PASSWORD_DEFAULT);
		
		/*
		 * For the sake of method chaining we return the pointer to this.
		 */
		return $this;
	}
	
	public function isSuspended()
	{
		$suspensions = db()->table('user\suspension')
			->get('user', $this)
			->where('expires', time(), '>')
			->fetch();
		
		return $suspensions;
	}
	
	public function __toString()
	{
		
		$q = db()->table('username')->get('user__id', $this->_id);
		$q->addRestriction('expires', null, 'IS');
		
		$record = $q->fetch();
		$username = $record->name;
		
		return sprintf('User (%s)', $username);
	}
}
