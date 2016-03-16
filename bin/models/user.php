<?php

use \spitfire\exceptions\PrivateException;

class UserModel extends spitfire\Model
{
	
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->password  = new StringField(90);
		$schema->email     = new StringField(30);
		
		$schema->verified  = new BooleanField();
		$schema->created   = new IntegerField();
		$schema->picture   = new FileField();
		
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
	 * @throws PrivateException
	 */
	public function insert() {
		
		if (function_exists('password_hash')) {
			$this->password = password_hash($this->password, PASSWORD_DEFAULT);
		} else {
			throw new PrivateException('Password hashing algorithm is missing. Please check your PHP version', 201602270011);
		}
		
		return parent::insert();
	}
	
	public function checkPassword($password) {
		
		/*
		 * If there is no password hashing mechanism, we should abort ASAP. Since 
		 * nothing the application could do then would make any sense.
		 */
		if (!function_exists('password_hash')) 
			{ throw new PrivateException('Password hashing algorithm is missing. Please check your PHP version', 201602270012); }
		
		/*
		 * If the password doesn't match, then we need to tell the user that whatever
		 * he wrote into the form was not acceptable.
		 */
		if (!password_verify($password, $this->password)) { return false; }
		
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

}
