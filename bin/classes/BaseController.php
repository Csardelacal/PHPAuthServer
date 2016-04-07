<?php

abstract class BaseController extends Controller
{
	
	protected $user = null;
	protected $isAdmin = false;
	
	public function _onload() {
		
		#Get the user session, if no session is given - we skip all of the processing
		$s = new session();
		if (!$s->getUser()) { return; }
		
		#Export the user to the controllers that may need it.
		$user = db()->table('user')->get('_id', $s->getUser())->fetch();
		$this->user = $user;
		
		try {
			#Check if the user is an administrator
			$admingroupid = SysSettingModel::getValue('admin.group');
			$isAdmin      = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $user)->fetch();
		}
		catch (spitfire\exceptions\PrivateException$e) {
			$isAdmin      = false;
		}
		
		$this->isAdmin = $isAdmin;
		$this->view->set('userIsAdmin', $isAdmin);
	}
	
}
