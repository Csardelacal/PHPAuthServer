<?php

use spitfire\exceptions\HTTPMethodException;
use spitfire\exceptions\PublicException;
use spitfire\io\Image;
use spitfire\io\Upload;
use spitfire\validation\ValidationException;

class AdminController extends BaseController
{
	
	public function _onload() {
		parent::_onload();
		
		#All of the functions this method provides are restricted to administrators.
		if (!$this->isAdmin) 
			{ throw new PublicException('Not allowed. PLease log in using a admin account', 403); }
	}
	
	public function index() {
		//That's it, the admin index is static
	}
	
	/*
	 * This method allows the admins to change the logo for the application. This
	 * will be displayed on the log in and registration screens, as well as any 
	 * other public areas like the "forgot password" screen.
	 */
	public function logo() {
		
		if ($this->request->isPost() && isset($_POST['file']) && $_POST['file'] instanceof Upload) {
			$location = $_POST['file']->validate()->store();
			
			$img = media()->load($location);
			$img->scale(500);
			$img->store($location);
			
			SysSettingModel::setValue('page.logo', $location->uri());
		}
		
		
	}
	
	/**
	 * Sets the settings for the CptnH00k integration.
	 * 
	 * @validate >> POST#app(positive number required)
	 */
	public function hook() {
		
		$valid = $this->hook && $this->hook->authenticate();
		
		try {
			if (!$this->request->isPost()) { throw new HTTPMethodException('Not posted', 1807101814); }
			if (!$this->validation->isEmpty()) { throw new ValidationException('Validation failed', 1807101815, $this->validation->toArray()); }
			
			#Record the setting, validating that CptnH00k works.
			$app  = db()->table('authapp')->get('_id', $_POST['app'])->first(true);
			$hook = new hook\Hook($app->url, $this->signature->make($app->appID, $app->appSecret, $app->appID));
			
			if (!$hook->authenticate()->authenticated) {
				throw new ValidationException('Hook rejected the connection', 1807111058, ['Cannot connect to hook server']);
			}
			
			SysSettingModel::setValue('cptn.h00k', $app->_id);
			return $this->response->setBody('Redirection...')->getHeaders()->redirect(url('admin', 'hook'));
		} 
		catch (HTTPMethodException$e) {
			#Show the form
		}
		catch (ValidationException$e) {
			#The user selected something absolutely invalid, we should inform them
		}
		
		$this->view->set('apps', db()->table('authapp')->getAll()->all());
		$this->view->set('selected', SysSettingModel::getValue('cptn.h00k'));
		$this->view->set('valid', $valid);
	}
	
}
