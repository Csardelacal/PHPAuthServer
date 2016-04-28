<?php

use mail\TransportInterface;
use spitfire\core\Environment;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Schema;

class EmailModel extends Model
{
	
	
	public function definitions(Schema $schema) {
		$schema->to        = new StringField(50);
		$schema->subject   = new StringField(50);
		$schema->body      = new TextField();
		$schema->scheduled = new IntegerField(true);
		$schema->delivered = new IntegerField(true);
	}
	
	public static function queue($to, $subject, $body, $scheduled = null) {
		if ($scheduled === null) { $scheduled = time(); }
		
		$model = db()->table('email')->newRecord();
		$model->to = $to;
		$model->subject = $subject;
		$model->body = $body;
		$model->scheduled = $scheduled;
		$model->delivered = null;
		$model->store();
		
		return $model;
	}
	
	public static function deliver() {
		$email = db()->table('email')->get('delivered', null, 'IS')->fetch();
		$transport = Environment::get('email.transport');
		
		if (!$transport || !$transport instanceof TransportInterface) {
			throw new PrivateException('No valid transport method found');
		}
		
		if (!$email) {
			return; #Everything delivered nicely
		}
		
		$transport->deliver($email);
		
		$email->delivered = time();
		$email->store();
	}

}