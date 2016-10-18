<?php

/**
 * 
 * @todo Add ownership to the apps. So a certain user can administrate his own apps
 */
class AuthAppModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->appID  = new StringField(20);
		$schema->appSecret = new StringField(50);
		
		$schema->name   = new StringField(20);
		$schema->icon   = new FileField();
		
		$schema->appID->setUnique(true);
	}
	
	public function __toString() {
		return sprintf('App (%s)', $this->name);
	}

}
