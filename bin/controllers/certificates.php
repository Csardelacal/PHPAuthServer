<?php

use spitfire\exceptions\PublicException;

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
		
		$keys = db()->table('key')->getAll()->setOrder('expires', 'DESC')->all();
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
	
	public function expire(KeyModel $key)
	{
		if ($key) {
			$key->expires = time() + 14 * 86400;
			$key->store();
		}
		
		$this->response->setBody('Redirect')->getHeaders()->redirect(url('certificates'));
		return;
	}
	
	public function publickey()
	{
		$keys = db()->table('key')->getAll()
			->group()
				->where('expires', null)
				->where('expires', '>', time())
			->endGroup()
			->all();
		$this->view->set('keys', $keys);
	}
}
