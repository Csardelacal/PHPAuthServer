<?php namespace webhook;

use IntegerField;
use Reference;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

class HookModel extends Model
{
	
	const USER         = 0x000F;
	const USER_CREATED = 0x0001;
	const USER_UPDATED = 0x0002;
	const USER_DELETED = 0x0004;
	const USER_BANNED  = 0x0008;
	
	const TOKEN         = 0x00F0;
	const TOKEN_CREATED = 0x0010;
	const TOKEN_UPDATED = 0x0020;
	const TOKEN_DELETED = 0x0040;
	
	const GROUP         = 0x0F00;
	const GROUP_CREATED = 0x0100;
	const GROUP_UPDATED = 0x0200;
	const GROUP_DELETED = 0x0400;
	const GROUP_MEMBER  = 0x0800;
	
	const APP           = 0xF000;
	const APP_CREATED   = 0x1000;
	const APP_UPDATED   = 0x2000;
	const APP_DELETED   = 0x4000;	
	
	public function definitions(Schema $schema) {
		$schema->app = new Reference('authapp');
		$schema->listen = new IntegerField(true);
		$schema->name = new StringField(50);
		$schema->url = new StringField(255);
	}
	
	public static function notify($change, $target) {
		$all = db()->table('webhook\hook')->getAll()->fetchAll();
		
		$all->each(function ($e) use ($change, $target) {
			/*
			 * If the hook is not listening for this change, why bother?
			 */
			if (!($change & $e->listen)) { return; }
			
			$call = db()->table('webhook\call')->newRecord();
			$call->hook   = $e;
			$call->target = $target->_id;
			$call->called = null;
			$call->store();
		});
	}
	
	public static function getHooksFor($listen) {
		$all = db()->table('webhook\hook')->getAll()->fetchAll();
		
		return $all->filter(function($e) use ($listen) {
			return !!($e->listen & $listen);
		});
	}

}