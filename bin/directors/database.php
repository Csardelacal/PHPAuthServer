<?php

use spitfire\mvc\Director;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class DatabaseDirector extends Director
{
	
	public function reset() {
		
		try {
			db()->destroy();
		}
		catch (\Exception $e) {
			trigger_error('Database did not exist. Destroying it failed.');
		}
		
		db()->create();
		
		return 0;
		
	}
	
	public function init($email, $username, $password)
	{
		
		if (db()->table('user')->getAll()->count()) { 
			console()->error('Database is initialized');
			return 1;
		}
		
		
		$user  = db()->table('user')->newRecord();
		$group = db()->table('group')->newRecord();
		
		#Create the user
		$user->email    = $email;
		$user->verified = true;
		$user->created  = time();
		$user->setPassword($password);
		$user->store();
		
		
		$_username = db()->table('username')->newRecord();
		$_username->user = $user;
		$_username->name = $username;
		$_username->store();
		
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
