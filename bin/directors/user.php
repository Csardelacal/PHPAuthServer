<?php

use spitfire\mvc\Director;

class UserDirector extends Director
{
	
	public function delete(string $username)
	{
		$_username = db()->table('username')->get('name', $username)->first(true);
		$_user = $_username->user;
		$_user->delete();
		console()->success('Deleted')->ln();
	}
}
