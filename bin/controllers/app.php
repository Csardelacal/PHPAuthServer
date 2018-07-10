<?php

use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\io\Upload;

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
		$pag   = new \spitfire\storage\database\pagination\Paginator($query);
		
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
			$this->response->getHeaders()->redirect(url('app', 'index', null, Array('message' => 'success')));
			return;
		}
		
	}
	
	public function detail($appID) {
		
		$app = db()->table('authapp')->get('_id', $appID)->fetch();
		
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
			
			$app->store();
		}
		
		$this->view->set('app', $app);
	}
	
	public function delete($appID) {
		
		if (isset($_GET['confirm'])) {
			$app = db()->table('authapp')->get('_id', $appID)->fetch();
			$app->delete();
			
			$this->response->getHeaders()->redirect(url('app', 'index', null, Array('message' => 'deleted')));
			return;
		}
		
		$this->view->set('confirm', url('app', 'delete', $appID, Array('confirm' => 'true')));
	}
	
}
