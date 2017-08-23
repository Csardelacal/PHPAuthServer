<?php

class CronController extends Controller
{
	
	/**
	 * 
	 * @template none
	 */
	public function index() {
		
		$lock = 'bin/usr/.cron.lock';
		$fh = fopen($lock, file_exists($lock)? 'r' : 'w+');
		
		if (flock($fh, LOCK_EX|LOCK_NB)) {
			#Send emails to the users
			EmailModel::deliver();
			
			flock($fh, LOCK_UN);
		}
	}
}
