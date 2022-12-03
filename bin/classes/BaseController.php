<?php

use spitfire\exceptions\PrivateException;
use spitfire\io\session\Session;
use spitfire\exceptions\PublicException;

abstract class BaseController extends Controller
{
	/** @var UserModel|null */
	protected $user = null;
	protected $token = null;
	protected $isAdmin = false;
	
	protected ?SessionModel $session;
	
	/**
	 *
	 * @var \signature\Helper
	 */
	protected $signature;
	protected $authapp;
	
	/**
	 *
	 * @var hook\Hook
	 */
	protected $hook;
	
	public function _onload() {
		
		#Get the user session, if no session is given - we skip all of the processing
		#The user could also check the token
		$s = Session::getInstance();
		$u = $s->getUser();
		$t = isset($_GET['token'])? db()->table('token')->get('token', $_GET['token'])->fetch() : null;
		
		try {
			#Check if the user is an administrator
			$admingroupid = SysSettingModel::getValue('admin.group');
		}
		catch (PrivateException$e) {
			$admingroupid = null;
		}
		
		if ($u) {
			$this->session = db()->table('session')->get('_id', SessionModel::TOKEN_PREFIX . Session::sessionId())->first();
			
			if ($this->session === null) {
				$this->session = db()->table('session')->newRecord();
				$this->session->_id = SessionModel::TOKEN_PREFIX . Session::sessionId();
				$this->session->store();
			}
		}
		
		if ($u || $t) { 
		
			#Export the user to the controllers that may need it.
			$user = $u? db()->table('user')->get('_id', $u)->fetch() : $t->user;
			$this->user  = $user;
			$this->token = $t;

			$isAdmin = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $user)->fetch();
		}
		
		$this->signature = new \signature\Helper(db());
		
		if (isset($_GET['signature']) && is_string($_GET['signature'])) {
			list($signature, $src, $target) = $this->signature->verify();
			
			if ($target) {
				throw new PublicException('_GET[signature] must not have remotes', 401);
			}
			
			$this->authapp = $src;
		}
		
		/*
		 * Webhook initialization
		 */
		if (null !== $hookapp = SysSettingModel::getValue('cptn.h00k')) {
			$hook = db()->table('authapp')->get('_id', $hookapp)->first();
			$sig = $this->signature->make($hook->appID, $hook->appSecret, $hook->appID);
			$this->hook = new hook\Hook($hook->url, $sig);
		}
		
		$this->isAdmin = $isAdmin?? false;
		$this->view->set('authUser', $this->user);
		$this->view->set('authApp',  $this->app);
		$this->view->set('userIsAdmin', $isAdmin ?? false);
		$this->view->set('administrativeGroup', $admingroupid);
	}
	
}
