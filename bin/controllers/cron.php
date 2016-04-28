<?php

class CronController extends Controller
{
	
	/**
	 * 
	 * @template none
	 */
	public function index() {
		//TODO: Add file locking mechanism
		EmailModel::deliver();
	}
}
