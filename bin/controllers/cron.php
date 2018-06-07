<?php

class CronController extends Controller
{
	
	/**
	 * 
	 * @template none
	 */
	public function index() {
		
		return;
		$lock = 'bin/usr/.cron.lock';
		$fh = fopen($lock, file_exists($lock)? 'r' : 'w+');
		
		if (flock($fh, LOCK_EX|LOCK_NB)) {
			#Send emails to the users
			EmailModel::deliver();
			
			#Call the expecting hooks
			\webhook\CallModel::run();
			
			flock($fh, LOCK_UN);
		}
	}
	
}
