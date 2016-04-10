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
		$pag   = new Pagination($query, 'app');
		
		$this->view->set('query',      $query);
		$this->view->set('pagination', $pag);
		
	}
	
	public function create() {
		
		if ($this->request->isPost()) {
			$secure = false;
			
			$app = db()->table('authapp')->newRecord();
			$app->name      = $_POST['name'];
			$app->appSecret = str_replace(Array('&', '=', '+'), '', base64_encode(openssl_random_pseudo_bytes(35, $secure)));
			
			if (!$secure) {
				throw new PrivateException('Could not generate safe AppSecret');
			}
			
			if ($_POST['icon'] instanceof Upload) {
				$app->icon = $_POST['icon']->store();
			}
			
			do {
				$id = $app->appID = mt_rand();
				$count = db()->table('authapp')->get('appID', $id)->count();
			} while ($count !== 0);
			
			$app->store();
			$this->response->getHeaders()->redirect(new URL('app', 'index', null, Array('message' => 'success')));
			return;
		}
		
	}
	
	public function detail($appID) {
		
		$app = db()->table('authapp')->get('_id', $appID)->fetch();
		
		if ($this->request->isPost()) {
			
			#The name of the application is, together with the icon, the only thing we can change
			$app->name = $_POST['name'];
			
			if ($_POST['icon'] instanceof Upload) {
				$app->icon = $_POST['icon']->store();
			}
			
			$app->store();
		}
		
		$this->view->set('app', $app);
	}
	
	public function delete($appID) {
		
		if (isset($_GET['confirm'])) {
			$app = db()->table('authapp')->get('_id', $appID)->fetch();
			$app->delete();
			
			$this->response->getHeaders()->redirect(new URL('app', 'index', null, Array('message' => 'deleted')));
			return;
		}
		
		$this->view->set('confirm', new URL('app', 'delete', $appID, Array('confirm' => 'true')));
	}
	
}
