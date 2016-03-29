<?php

/**
 * This controller greets the users when they get to the application. If the app
 * was not yet set up, it will redirect the user over to the SetupController
 * 
 */
class HomeController extends Controller
{
	public function index() {
		
		#If the user table has no records, we need to set up the application
		if (db()->table('user')->getAll()->count() < 1) { return $this->response->getHeaders()->redirect(new URL('setup')); }
		
		$s = new session();
		
		#Get the user model
		$user = $s->getUser()? db()->table('user')->get('_id', $s->getUser())->fetch() : null;
		
		#If the user is logged in, we show them their dashboard, otherwise we'll send them away to get logged in.
		if ($user !== null) { $this->view->set('user', $user); } 
		else                { return $this->response->getHeaders()->redirect(new URL('user', 'login')); }
		
		#Check if the user is an administrator
		$admingroupid = SysSettingModel::getValue('admin.group');
		$isAdmin      = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $user)->fetch();
		
		$this->view->set('userIsAdmin', $isAdmin);
	}
}
