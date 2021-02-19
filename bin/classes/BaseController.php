<?php

use magic3w\hook\sdk\Hook;
use signature\Helper;
use spitfire\core\Collection;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\io\session\Session;

abstract class BaseController extends Controller
{
	/** 
	 * @var UserModel|null 
	 */
	protected $user = null;
	protected $token = null;
	protected $isAdmin = false;
	
	/**
	 *
	 * @var Helper
	 */
	protected $signature;
	protected $authapp;
	
	/**
	 * 
	 * @var Collection
	 */
	protected $level;
	
	/**
	 *
	 * @var Hook
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
		
		if ($u || $t) { 
		
			#Export the user to the controllers that may need it.
			$user = $u? db()->table('user')->get('_id', $u)->fetch() : $t->user;
			$this->user  = $user;
			$this->token = $t;
			
			#Retrieve the user's authentication level
			$this->level = db()->table('authentication\challenge')
				->get('session', db()->table('session')->get('_id', $s->sessionId())->first())
				->where('cleared', '!=', null)
				->where('expires', '>', time())
				->all();
			
			$isAdmin = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $user)->fetch();
		}
		
		$this->signature = new Helper(db());
		
		if (isset($_GET['signature']) && is_string($_GET['signature'])) {
			list($signature, $src, $target) = $this->signature->verify();
			
			if ($target) {
				throw new PublicException('_GET[signature] must not have remotes', 401);
			}
			
			$this->authapp = $src;
			$this->level   = collect();
		}
		
		/*
		 * Webhook initialization
		 */
		if (null !== $hookapp = SysSettingModel::getValue('cptn.h00k')) {
			$hook = db()->table('authapp')->get('_id', $hookapp)->first();
			#TODO: Add a token to the webhook
			//$this->hook = new Hook($hook->url, null);
		}
		
		$this->isAdmin = $isAdmin?? false;
		
		$this->view->set('level', $this->level);
		$this->view->set('authUser', $this->user);
		$this->view->set('authApp',  $this->app);
		$this->view->set('userIsAdmin', $isAdmin ?? false);
		$this->view->set('administrativeGroup', $admingroupid);
	}
	
}
