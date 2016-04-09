<?php

class AppController extends BaseController
{
		
	public function _onload() {
		parent::_onload();
		
		#Get the user model
		if (!$this->user) { throw new spitfire\exceptions\PublicException('Not logged in', 403); }
		
		#Check if he's an admin
		if (!$this->isAdmin) { throw new spitfire\exceptions\PublicException('Not an admin', 401); }
		
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
			$app->appSecret = base64_encode(openssl_random_pseudo_bytes(35, $secure));
			
			if (!$secure) {
				throw new \spitfire\exceptions\PrivateException('Could not generate safe AppSecret');
			}
			
			if ($_POST['icon'] instanceof spitfire\io\Upload) {
				$app->icon = $_POST['icon']->store();
			}
			
			do {
				$id = $app->appID = mt_rand();
				$count = db()->table('authapp')->get('appID', $id)->count();
			} while ($count !== 0);
			
			$app->store();
			$this->response->getHeaders()->redirect(new URL('app', 'index', null, Array('success' => 'yes')));
		}
		
	}
	
}
