<?php

class GroupModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->creator     = new Reference('user');
		$schema->name        = new StringField(30);
		$schema->description = new TextField();
		
		
		$schema->public      = new BooleanField(); //A public page appears in search results
		$schema->open        = new BooleanField(); //An open page accepts members without admin approval
		
		$schema->members     = new ChildrenField('user\group', 'group');
	}

}

