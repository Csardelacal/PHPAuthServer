<?php

use spitfire\exceptions\PublicException;

class AdminController extends BaseController
{
	
	public function index() {
		if (!$this->isAdmin) 
			{ throw new PublicException('Not allowed. PLease log in using a admin account', 403); }
		
		//THat's it, the admin index is static
	}
	
}
