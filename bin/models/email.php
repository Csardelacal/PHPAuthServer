<?php

use mail\TransportInterface;
use spitfire\core\Environment;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Schema;

/**
 * The Email queue model allows the application to send emails in an asynchronous
 * way. This will avoid requests to the user being delayed due to the server 
 * having a high latency connection with the email provider.
 * 
 * Instead, the web server can push the email to a queue and let the email be sent 
 * at a later point in time. This should improve, both latency and usability.
 * 
 * The message class property is a value specifically used to prevent the server
 * from notifying a certain behavior twice. Imagine a user that uploads an auction
 * with several slots. The system would send several messages at once, which is
 * not the preferred behavior.
 * 
 * @property int    $id        The id of the message. This is only used to identify the record inside the DBMS
 * @property string $to        The email address we wish the email to be delivered to
 * @property string $subject   The subject line of the email being sent
 * @property string $body      The full HTML message being sent to the user
 * @property int    $scheduled Timestamp after which the message should be delivered
 * @property int    $delivered Timestamp the message was delivered, or NULL if it wasn't
 */
class EmailModel extends Model
{
	
	const SUBJECT_LENGTH = 50;
	
	public function definitions(Schema $schema) {
		$schema->to        = new StringField(50);
		$schema->subject   = new StringField(self::SUBJECT_LENGTH);
		$schema->body      = new TextField();
		$schema->scheduled = new IntegerField(true);
		$schema->delivered = new IntegerField(true);
	}
	
	public static function queue($to, $subject, $body, $scheduled = null) {
		if ($scheduled === null) { $scheduled = time(); }
		
		$model = db()->table('email')->newRecord();
		$model->to = $to;
		$model->subject = Strings::ellipsis($subject, self::SUBJECT_LENGTH);
		$model->body = $body;
		$model->scheduled = $scheduled;
		$model->delivered = null;
		$model->store();
		
		//Notify
		try {
			$lock = new cron\FlipFlop(spitfire()->getCWD() . '/bin/usr/.mail.cron.sem');
			$lock->notify();
		} 
		catch (\Exception$e) {
			spitfire()->log($e->getMessage());
		}
		
		return $model;
	}
	
	public static function deliver() {
		$email = db()->table('email')->get('delivered', null, 'IS')->fetch();
		$transport = Environment::get('email.transport');
		
		if (!$transport || !$transport instanceof TransportInterface) {
			throw new PrivateException('No valid transport method found');
		}
		
		if (!$email) {
			return false; #Everything delivered nicely
		}
		
		if (!$email->to) {
			$email->delivered = time();
			$email->store();
			throw new PrivateException("Email #{$email->_id} had no recipient", 1705250222);
		}
		
		$transport->deliver($email);
		
		$email->delivered = time();
		$email->store();
		
		return true;
	}

}