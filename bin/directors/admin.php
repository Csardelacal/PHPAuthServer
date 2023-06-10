<?php

use spitfire\mvc\Director;

class AdminDirector extends Director
{
	
	public function setup(string $username, string $email, string $password)
	{
		/**
		 * Set up can't be run twice
		 */
		if (db()->table('user')->getAll()->count()) { 
			throw new spitfire\exceptions\PublicException('Setup was already executed', 403); 
		}
		
		
		$user  = db()->table('user')->newRecord();
		$group = db()->table('group')->newRecord();
		
		#Create the user
		$user->email    = $email;
		$user->verified = true;
		$user->created  = time();
		$user->setPassword($password);
		$user->store();
		
		
		$username = db()->table('username')->newRecord();
		$username->user = $user;
		$username->name = $username;
		$username->store();
		
		#Create the group
		$group->creator = $user;
		$group->name    = 'Administrators';
		$group->description = 'System administrators';
		$group->groupId     = 'sysadmins';
		$group->public  = true;
		$group->open    = 0;
		$group->store();
		
		#Set the group as admin group
		SysSettingModel::setValue('admin.group', $group->_id);
		
		#Add the user to the group
		$membership = db()->table('user\group')->newRecord();
		$membership->user  = $user;
		$membership->group = $group;
		$membership->role  = 'owner';
		$membership->store();
	}
}
