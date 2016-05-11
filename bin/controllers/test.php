<?php

class TestController extends BaseController
{
	
	public function index() {
		var_dump($_POST);
		die();
	}
	
}
