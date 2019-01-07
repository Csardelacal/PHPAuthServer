<?php

class SetupController extends Controller
{
	
	/**
	 * 
	 * @layout minimal.php
	 * @throws spitfire\exceptions\PublicException
	 */
	public function index() {
		
		if (db()->table('user')->getAll()->count()) { throw new spitfire\exceptions\PublicException('Setup was already executed', 403); }
		
		if ($this->request->isPost()) {
			
			$user  = db()->table('user')->newRecord();
			$group = db()->table('group')->newRecord();
			
			#Create the user
			$user->email    = $_POST['email'];
			$user->verified = true;
			$user->created  = time();
			$user->setPassword($_POST['password']);
			$user->store();
			
			
			$username = db()->table('username')->newRecord();
			$username->user = $user;
			$username->name = $_POST['username'];
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
			
			
			$this->response->getHeaders()->redirect(new URL());
		}
		
		//Render the view to create a new user
	}
	
}