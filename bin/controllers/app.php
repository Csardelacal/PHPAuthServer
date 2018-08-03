<?php

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
		
	public function _onload() {
		parent::_onload();
		
		#Get the user model
		if (!$this->user) { throw new PublicException('Not logged in', 403); }
		
		#Check if he's an admin
		if (!$this->isAdmin) { throw new PublicException('Not an admin', 401); }
		
	}
	
	public function index() {
		
		$query = db()->table('authapp')->getAll();
		$pag   = new Paginator($query);
		
		$this->view->set('pagination', $pag);
		
	}
	
	public function create() {
		
		if ($this->request->isPost()) {
			$app = db()->table('authapp')->newRecord();
			$app->name      = $_POST['name'];
			$app->appSecret = preg_replace('/[^a-z\d]/i', '', base64_encode(random_bytes(35)));
			$app->system    = false;
			$app->drawer    = false;
			
			if ($_POST['icon'] instanceof Upload) {
				$app->icon = $_POST['icon']->validate()->store();
			}
			
			do {
				$id = $app->appID = mt_rand();
				$count = db()->table('authapp')->get('appID', $id)->count();
			} while ($count !== 0);
			
			$app->store();
			$this->response->getHeaders()->redirect(url('app', 'index', Array('message' => 'success')));
			return;
		}
		
	}
	
	public function detail(AuthAppModel$app) {
		
		if ($this->request->isPost()) {
			
			#The name of the application
			if (isset($_POST['name'])) {
				$app->name = trim($_POST['name']);
			}
			
			#The URL users can use to access the app
			if (isset($_POST['url'])) {
				$app->url = trim($_POST['url']);
			}
			
			if ($_POST['icon'] instanceof Upload) {
				$app->icon = $_POST['icon']->store();
			}
			
			$app->system = isset($_POST['system']);
			$app->drawer = isset($_POST['drawer']);
			
			$app->store();
		}
		
		$this->view->set('app', $app);
		
		try {
			$hookapp = db()->table('authapp')->get('_id', SysSettingModel::getValue('cptn.h00k'))->first(true)->appID;
			$this->view->set('webhooks', $this->hook->on($hookapp, $app->appID)->listeners);
		} catch (Exception $ex) {
			$this->view->set('webhooks', []);
		}
	}
	
	public function delete($appID) {
		$xsrf = new \spitfire\io\XSSToken();
		
		if (isset($_GET['confirm']) && $xsrf->verify($_GET['confirm'])) {
			$app = db()->table('authapp')->get('_id', $appID)->fetch();
			$app->delete();
			
			$this->response->getHeaders()->redirect(url('app', 'index', Array('message' => 'deleted')));
			return;
		}
		
		$this->view->set('confirm', url('app', 'delete', $appID, Array('confirm' => $xsrf->getValue())));
	}
	
}
