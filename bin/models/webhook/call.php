<?php namespace webhook;

use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;

class CallModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->hook   = new Reference('webhook\hook');
		$schema->target = new \StringField(25);
		$schema->called = new IntegerField(true);
	}
	
	public static function run() {
		$next = db()->table('webhook\call')->get('called', null, 'IS')->fetch();
		$url  = $next->hook->url;
		
		if ($next->hook->listen & HookModel::USER)  { $payload = ['type' => 'user']; }
		if ($next->hook->listen & HookModel::TOKEN) { $payload = ['type' => 'token']; }
		
		$payload['id'] = $next->target;
		
		$request = new \Request($url);
		$request->send($payload);
		
		$next->called = time();
		$next->store();
	}

}