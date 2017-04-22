<?php

use spitfire\exceptions\PublicException;
use spitfire\io\Upload;

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
			
			$img = new spitfire\io\Image($location);
			$img->resize(500);
			$resized = $img->store('./assets/img/' . basename($location));
			
			SysSettingModel::setValue('page.logo', substr($resized, strlen('./assets/')));
		}
		
		
	}
	
}
