<?php namespace email;

use AuthAppModel;
use cron\FlipFlop;
use IntegerField;
use mail\spam\domain\StorageInterface;
use mail\transport\Contact;
use mail\transport\Email;
use mail\TransportInterface;
use Reference;
use spitfire\core\Environment;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use Strings;
use TextField;
use function db;
use function spitfire;

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
 * @deprecated since version 0.1-dev
 * @property int    $id        The id of the message. This is only used to identify the record inside the DBMS
 * @property string $to        The email address we wish the email to be delivered to
 * @property string $subject   The subject line of the email being sent
 * @property string $body      The full HTML message being sent to the user
 * @property int    $scheduled Timestamp after which the message should be delivered
 * @property int    $delivered Timestamp the message was delivered, or NULL if it wasn't
 */
class OutgoingModel extends Model
{
	
	const SUBJECT_LENGTH = 50;
	
	public function definitions(Schema $schema) {
		$schema->to        = new StringField(50);
		$schema->app       = new Reference(AuthAppModel::class);
		$schema->domain    = new Reference(DomainModel::class);
		$schema->subject   = new StringField(self::SUBJECT_LENGTH);
		$schema->body      = new TextField();
		$schema->scheduled = new IntegerField(true);
		$schema->delivered = new IntegerField(true);
		$schema->clicked   = new IntegerField(true);
		$schema->meta      = new StringField(80);
		$schema->secret    = new StringField(20);
		
		$schema->index($schema->domain, $schema->scheduled);
		$schema->index($schema->delivered);
	}
	
	/**
	 * Pushes an email onto the delivery queue. This function will, if needed,
	 * apply throttling to the delivery of the email.
	 * 
	 * Once the email is queued, the function will notify the cron, so it can deliver
	 * the email to the client as soon as possible.
	 * 
	 * @param AuthAppModel $app
	 * @param string $to
	 * @param string $subject
	 * @param string $body
	 * @param string $meta
	 * @param int $scheduled
	 * @return OutgoingModel
	 */
	public static function queue(AuthAppModel$app, $to, $subject, $body, $meta, $scheduled = null) {
		if ($scheduled === null) { $scheduled = time(); }
		
		/*
		 * Associate the email to the domain for statistical analysis
		 */
		$domain = db()->table('email\domain')->get('type', StorageInterface::TYPE_HOSTNAME)->where('host', explode('@', $to)[1])->first();
		
		/*
		 * If the domain is unknown to the server, we register it, this way an administrator
		 * can see the domains the server is sending email to and collect statistics
		 * on the deliverability issues the server may be encountering.
		 */
		if (!$domain) {
			$domain = db()->table('email\domain')->newRecord();
			$domain->type = StorageInterface::TYPE_HOSTNAME;
			$domain->host = explode('@', $to)[1];
			$domain->list = 'unlisted';
			$domain->reason = 'Automatically created when sending email';
			$domain->deliverability = 0;
			$domain->store();
		}
		
		/*
		 * If the domain is not listed in a white- or blacklist, we will proceed
		 * to throttle the request to send an email appropriately. Depending on 
		 * how good / bad the deliverability of the domain is, the system will 
		 * take measures to either stop the request or just wave it through.
		 */
		if ($domain->list === 'unlisted') {
			if ($domain->deliverability < .05) { $scheduled+= rand(3600 * 3, 36000 * 6); }
			if ($domain->deliverability < .15) { $scheduled+= rand(3600 * 3, 36000 * 6); }
			if ($domain->deliverability > .90) { $scheduled+= rand(3600 * 3, 36000 * 6); }
		}
		
		/*
		 * Store the email sending request to the database.
		 */
		$model = db()->table('email\outgoing')->newRecord();
		$model->app = $app;
		$model->to = $to;
		$model->domain = $domain;
		$model->subject = Strings::ellipsis($subject, self::SUBJECT_LENGTH);
		$model->body = $body;
		$model->scheduled = $scheduled;
		$model->delivered = null;
		$model->meta = $meta;
		$model->secret = substr(str_replace(['/', '+'], '-', base64_encode(random_bytes(20))), 0, 20);
		$model->store();
		
		/*
		 * Inform the cron that this email has to be sent. Please note that due to
		 * the way this system works, the email may be delayed if it's throttled.
		 * 
		 * This is because the cron is not able to set a timer. Effectively requiring
		 * it to poll for throttled mails.
		 */
		try {
			$lock = new FlipFlop(spitfire()->getCWD() . '/bin/usr/.mail.cron.sem');
			$lock->notify();
		}
		catch (\Exception$e) {
			spitfire()->log($e->getMessage());
		}
		
		return $model;
	}
	
	/**
	 * Send the email to the end user.
	 * 
	 * @todo I am not satisfied with the fact that this code is here. It should be
	 * in a dedicated email delivering mechanisms.
	 * 
	 * @return boolean
	 * @throws PrivateException
	 */
	public static function deliver() {
		$email = db()->table('email\outgoing')->get('delivered', null, 'IS')->fetch();
		$transport = Environment::get('email.transport');
		
		if (!$transport || !$transport instanceof TransportInterface) {
			throw new PrivateException('No valid transport method found');
		}
		
		if (!$email) {
			return false; #Everything delivered nicely
		}
		
		$userid = $email->to;
		
		/*
		 * Depending on which kind of user we're sending an email to, we will see 
		 * different behavior. If the recipient is an email address, we will direct
		 * the to the specified inbox.
		 */
		if (filter_var($userid, FILTER_VALIDATE_EMAIL)) {
			$to = $userid;
		}
		
		/*
		 * If the user identifier is numeric, it indicates a registered user accessing
		 * the site and being sent an email.
		 */
		elseif(is_numeric($userid)) {
			$dbuser = db()->table('user')->get('_id', $userid)->fetch(true);
			$to = $dbuser->email;
		}
		
		/*
		 * If there's a user-id prefixed by a colon (:), it means that the system
		 * is responding to an alias. In order to protect privacy, PHPAS will replace
		 * email addresses that are incoming with an alias.
		 * 
		 * This way, a potentially faulty or malicious third party application has 
		 * no access to the user's information and it can be expired without the
		 * need to take down the third party app.
		 */
		elseif (Strings::startsWith($userid, ':') && is_numeric(substr($userid, 1))) {
			$dbalias = db()->table('email\alias')->get('_id', $userid)->fetch(true);
			$to = $dbalias->external;
		}
		
		/*
		 * If the email is neither a user, nor an alias nor an email address, it's
		 * probably an error and we will just mark the email as delivered.
		 */
		else {
			$email->delivered = time();
			$email->store();
			throw new PrivateException("Email #{$email->_id} had no recipient", 1705250222);
		}
		
		/*
		 * Check if the email has registered a "return-path". In PHPAS a return path 
		 * is an inbox that the application sending the email can listen to for 
		 * responses.
		 * 
		 * This bascially allows applications to receive a response to an email they
		 * sent and appropriately match it to the application. If the return path
		 * could not be found, we will send it to the smtp-from
		 */
		$returnPath = db()->table('email\inbox')->get('app', $email->app)->first();
		
		if ($returnPath) {
			list($user, $host) = explode('@', $returnPath->address);
		}
		else {
			list($user, $host) = explode('@', Environment::get('smtp.from'));
		}
		
		/*
		 * Send the email to the transport we're using to deliver it.
		 */
		$transport->deliver(new Email(
			$email->_id, 
			new Contact('User', $to), 
			new Contact('Commishes', sprintf('%s+%s-%s@%s', $user, $email->_id, $email->secret, $host)), 
			$email->subject, 
			$email->body, 
			strip_tags($email->body)
		));
		
		$email->delivered = time();
		$email->store();
		
		return true;
	}

}
