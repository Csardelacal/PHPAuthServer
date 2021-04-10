<?php

use magic3w\hook\sdk\Hook;
use spitfire\core\Collection;
use spitfire\exceptions\PrivateException;
use spitfire\io\session\Session;

abstract class BaseController extends Controller
{
	/** 
	 * @var UserModel|null 
	 */
	protected $user = null;
	protected $isAdmin = false;
	protected $session;
	
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
		
		try {
			#Check if the user is an administrator
			$admingroupid = SysSettingModel::getValue('admin.group');
		}
		catch (PrivateException$e) {
			$admingroupid = null;
		}
		
		#Find the application for the SSO Server
		$self = db()->table(AuthAppModel::class)->get('_id', SysSettingModel::getValue('app.self'))->first();
		
		try { 
		
			#Export the user to the controllers that may need it.
			$sess = db()->table('session')->get('_id', $u)->fetch(true);
			$user = $sess->user;
			$this->user  = $user;
			
			#Retrieve the user's authentication level
			$this->level = db()->table('authentication\challenge')
				->get('session', $sess)
				->where('cleared', '!=', null)
				->where('expires', '>', time())
				->all();
			
			$isAdmin = !!db()->table('user\group')->get('group__id', $admingroupid)->addRestriction('user', $user)->fetch();
		}
		catch (\spitfire\exceptions\PrivateException $ex) {
			#There was a problem loading the session
			$this->level = collect();
			$this->user  = null;
			$isAdmin = false;
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
		$this->session = $sess?? null;
		
		$this->view->set('level', $this->level);
		$this->view->set('authUser', $this->user);
		$this->view->set('authApp',  $this->app);
		$this->view->set('userIsAdmin', $isAdmin ?? false);
		$this->view->set('administrativeGroup', $admingroupid);
	}
	
}
