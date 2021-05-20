<?php

use client\CredentialModel;
use client\ScopeModel;
use spitfire\exceptions\PublicException;
use spitfire\io\Upload;
use spitfire\storage\database\pagination\Paginator;

/**
 * This controller allows administrators (and only those) to manage the applications
 * that can connect to the server and manage their preferences and default
 * access level settings.
 * 
 * Only admins receive access since this is the strongest vector for a malicious
 * application to raise it's privileges and access data it's not supposed to have.
 */
class AppController extends BaseController
{
		
	public function _onload() 
	{
		parent::_onload();
		
		#Get the user model
		if (!$this->user) { throw new PublicException('Not logged in', 403); }
		
	}
	
	public function index() 
	{
		
		$query = db()->table('authapp')->get('owner', $this->user);
		$pag   = new Paginator($query);
		
		$this->view->set('pagination', $pag);
		
	}
	
	public function create() 
	{
		
		if ($this->request->isPost()) {
			$app = db()->table('authapp')->newRecord();
			$app->owner = $this->user;
			$app->name  = $_POST['name'];
			
			/**
			 * If the icon was not uploaded, we cannot continue
			 */
			if (!($_POST['icon'] instanceof Upload)) {
				throw new PublicException('No icon was provided');
			}
			
			$icon = db()->table(IconModel::class)->newRecord();
			$icon->file = $_POST['icon']->store()->uri();
			$icon->store();
			
			/**
			 * Generate a random application id. This application id must be unique and cannot
			 * be used by other applications. Also, the app id is immutable, meaning that once
			 * an application has received a specific app id it cannot be changed.
			 */
			do {
				$id = $app->appID = mt_rand();
				$count = db()->table('authapp')->get('appID', $id)->count();
			} while ($count !== 0);
			
			$app->store();
			
			/**
			 * Generate a credential for the application. We default to generating a credential
			 * that has no expiration (meaning the the user will never be requested to refresh
			 * the credential), since generally credentials do offer a good level of security.
			 */
			$secret = db()->table(CredentialModel::class)->newRecord();
			$secret->client = $app;
			$secret->store();
			
			/**
			 * The icon for the standard scope is created by generating a copy of the application's
			 * icon. Usually this is a sensible approach to take in this situation.
			 * 
			 * The storage code here is creating a copy of the application's icon to the same directory
			 * so we can make sure that the icon is not deleted whenever the user changes the
			 * icon for the app, or the icon for the app being removed when the user changes this
			 * icon.
			 */
			$icon_scope = db()->table(IconModel::class)->newRecord();
			$icon_scope->file = storage()
				->retrieve($_POST['icon']->store()->uri() . '_')
				->write(storage()->retrieve($_POST['icon']->store()->uri() . '_')->read())
				->uri();
			$icon_scope->store();
			
			/**
			 * If we happen to not have a scope for the 'basic' authentication of a user
			 * on the audience, we cannot continue, since this provides the baseline for 
			 * the user being logged into the application at all.
			 * 
			 * This means that all applications must have a basic scope to authenticate
			 * the user. Otherwise the application will fail.
			 * 
			 * The basic scope is required for applications to create a token. Without it
			 * the application cannot issue tokens.
			 */
			$scope = db()->table(ScopeModel::class)->newRecord();
			$scope->identifier = sprintf('%s.basic', $app->appID);
			$scope->icon = $icon_scope;
			$scope->caption = 'Basic data';
			$scope->description = 'Access basic data about your account on this application';
			$scope->store();
			
			$this->response->getHeaders()->redirect(url('app', 'index', Array('message' => 'success')));
			return;
		}
		
	}
	
	public function detail(AuthAppModel$app) 
	{
		
		if ($app->owner->_id != $this->user->_id) {
			throw new PublicException('Not allowed', 403);
		}
		
		if ($this->request->isPost()) {
			
			#The name of the application
			if (isset($_POST['name'])) {
				$app->name = trim($_POST['name']);
			}
			
			$app->store();
		}
		
		$this->view->set('app', $app);
		
		try {
			$hookapp = db()->table('authapp')->get('_id', SysSettingModel::getValue('cptn.h00k'))->first(true)->appID;
			//$this->view->set('webhooks', $this->hook->on($hookapp, $app->appID)->listeners);
		} catch (Exception $ex) {
			$this->view->set('webhooks', []);
		}
	}
	
	public function putIcon(AuthAppModel $app) 
	{
		
		if ($app->owner->_id != $this->user->_id) {
			throw new PublicException('Not allowed', 403);
		}
		
		$handle = storage()->retrieve('tmp://' . uniqid());
		$target = storage()->retrieve('uploads://' . uniqid('icon-', true) . '.jpg');
		
		$handle->write(file_get_contents('php://input'));
		
		#Compress the file to a more manageable size
		#This allows the application to quickly create thumbs on demand.
		$media = media()->load($handle);
		$media->poster()->fit(512, 512);
		$media->store($target);
		
		$icon = db()->table('icon')->newRecord();
		$icon->file = $target->uri();
		$icon->store();

		#Expire the old icon
		if ($app->icon) {
			$app->icon->expires = time();
			$app->icon->store();
		}
		
		#Queue the incineration of the old icon.
		defer(\defer\incinerate\IconTask::class, $app->icon->_id);

		#Set the new one
		$app->icon = $icon;
		$app->store();
		
		#This endpoint responds mainly by using status codes.
		$this->response->setBody('Success');
	}
	
	public function delete($appID) 
	{
		$xsrf = new \spitfire\io\XSSToken();
		$app = db()->table('authapp')->get('_id', $appID)->fetch();
		
		if ($app->owner->_id != $this->user->_id) {
			throw new PublicException('Not allowed', 403);
		}
		
		if (isset($_GET['confirm']) && $xsrf->verify($_GET['confirm'])) {
			$app->delete();
			
			$this->response->getHeaders()->redirect(url('app', 'index', Array('message' => 'deleted')));
			return;
		}
		
		$this->view->set('confirm', url('app', 'delete', $appID, Array('confirm' => $xsrf->getValue())));
	}
	
}
