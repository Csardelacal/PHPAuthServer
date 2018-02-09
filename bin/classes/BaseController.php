<?php

use spitfire\exceptions\PrivateException;
use spitfire\io\session\Session;

abstract class BaseController extends Controller
{
	/** @var UserModel|null */
	protected $user = null;
	protected $token = null;
	protected $isAdmin = false;
	
	public function _onload() {
		
		#Get the user session, if no session is given - we skip all of the processing
		#The user could also check the token
		$s = Session::getInstance();
		$u = $s->getUser();
		$t = isset($_GET['token'])? db()->table('token')->get('token', $_GET['token'])->fetch() : null;
		
		if (!$u && !$t) { return; }
		
		#Export the user to the controllers that may need it.
		$user = $u? db()->table('user')->get('_id', $u)->fetch() : $t->user;
		$this->user  = $user;
		$this->token = $t;
		
		try {
			#Check if the user is an administrator
			$admingroupid = SysSettingModel::getValue('admin.group');
			$isAdmin      = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $user)->fetch();
		}
		catch (PrivateException$e) {
			$admingroupid = null;
			$isAdmin      = false;
		}
		
		$this->isAdmin = $isAdmin;
		$this->view->set('authUser', $this->user);
		$this->view->set('userIsAdmin', $isAdmin);
		$this->view->set('administrativeGroup', $admingroupid);
	}
	
}
