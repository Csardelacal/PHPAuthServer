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
			
			/*
			 * Create the application for the SSO server itself
			 * 
			 * SSO can use this to authenticate itself against other applications whenever needed
			 * Originally, SSO would sign as the receiving application when sending 
			 * requests. While this is technically possible, it makes many behaviors
			 * confusing and log files unnecesarily convoluted.
			 * 
			 * Instead, we can now have SSO identify itself as the application sending
			 * the requests. Reducing the complexity of the system considerably.
			 */
			$app = db()->table('authapp')->newRecord();
			$app->name      = 'PHPAS - Single-sign-on server';
			#Usually we check for collissions, but it's technically not possible since it's the first
			$app->appID     = mt_rand(); 
			$app->appSecret = preg_replace('/[^a-z\d]/i', '', base64_encode(random_bytes(35)));
			$app->drawer    = false;
			$app->icon      = storage()->get('app://assets/img/app.png')->uri();
			$app->store();
			
			SysSettingModel::setValue('app.self', $app->_id);
			
			$this->response->getHeaders()->redirect(url());
		}
		
		//Render the view to create a new user
	}
	
}