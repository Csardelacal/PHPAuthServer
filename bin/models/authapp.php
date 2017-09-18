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
		$schema->url    = new StringField(100);
		$schema->icon   = new FileField();
		
		/*
		 * The webhook allows the App developer to provide a URL that will be called
		 * when a user modifies it's data.
		 */
		$schema->webhook= new StringField(100);
		
		$schema->appID->setUnique(true);
	}
	
	public function __toString() {
		return sprintf('App (%s)', $this->name);
	}

}
