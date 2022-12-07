<?php

class CertificatesController extends BaseController
{
	
	public function index()
	{
		
		#Get the user model
		if (!$this->user) {
			throw new PublicException('Not logged in', 403);
		}
		
		#Check if he's an admin
		if (!$this->isAdmin) {
			throw new PublicException('Not an admin', 401);
		}
		
		$keys = db()->table('key')->getAll()->all();
		$this->view->set('keys', $keys);
	}
	
	public function keygen()
	{
		#Get the user model
		if (!$this->user) {
			throw new PublicException('Not logged in', 403);
		}
		
		#Check if he's an admin
		if (!$this->isAdmin) {
			throw new PublicException('Not an admin', 401);
		}
		
		[$private, $public] = KeyModel::generate();
		
		$existing = db()->table('key')->get('expires', null)->first();
		
		if ($existing) {
			$existing->expires = time() + 14 * 86400;
			$existing->store();
		}
		
		$key = db()->table('key')->newRecord();
		$key->public = $public;
		$key->private = $private;
		$key->store();
		
		return $this->response->setBody('Redirect')->getHeaders()->redirect(url('certificates'));
	}
	
	public function public_key()
	{
		$keys = db()->table('key')->getAll()->where('expires', null)->all();
		$this->view->set('keys', $keys);
	}
}
