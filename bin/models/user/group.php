<?php namespace user;

class GroupModel extends spitfire\Model
{
	
	public function definitions(\spitfire\storage\database\Schema $schema) {
		$schema->user  = new \Reference('user');
		$schema->group = new \Reference('group');
		$schema->role  = new \EnumField('member', 'mod', 'admin');
	}

}
