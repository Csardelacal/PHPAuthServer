<?php namespace webhook;

use Exception;
use IntegerField;
use Reference;
use Request;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use TextField;
use function db;

/**
 * 
 * @deprecated since version 0.1-dev 20180705
 */
class CallModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->hook     = new Reference('webhook\hook');
		$schema->target   = new StringField(60);
		$schema->called   = new IntegerField(true);
		$schema->response = new TextField();
		$schema->tried    = new IntegerField();
	}
	
	public static function run() {
		$next = db()->table('webhook\call')->get('called', null, 'IS')->fetch();
		
		if (!$next) { return; } //Everything is fine
		
		$url  = $next->hook->url;
		
		$payload = $next->hook->mask2Array();
		$payload['id'] = $next->target;
		
		$request = new Request($url);
		
		try {
			$next->response = $request->send($payload);
			$next->called   = time();
			$next->store();
		} 
		catch (Exception$e) {
			$next->tried    = $next->tried + 1;
			$next->called   = $next->tried > 3? time() : null;
			$next->store();
		}
	}

}