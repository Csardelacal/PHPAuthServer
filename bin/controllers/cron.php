<?php

class CronController extends Controller
{
	
	/**
	 * 
	 * @template none
	 */
	public function index() {
		$fh = fopen('bin/usr/.cron.lock', 'w+');
		if (flock($fh, LOCK_EX|LOCK_NB)) {
			#Send emails to the users
			EmailModel::deliver();
			
			#Call the expecting hooks
			\webhook\CallModel::run();
			
			flock($fh, LOCK_UN);
		}
	}
	
	public function testGeo() {
		var_dump(IP::makeLocation());
		die();
	}
}
