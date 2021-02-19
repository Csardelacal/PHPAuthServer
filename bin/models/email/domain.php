<?php namespace email;

use FloatField;
use IntegerField;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;

/**
 * The domain block model allows the application maintainer to add domains to a
 * black list that prevents them from being used to register an account.
 *
 * This is specially helpful when fighting spam, since many users will attempt
 * to register accounts with email services that offer disposable email addresses.
 *
 * @property string $host        The domain being blocked
 * @property bool   $subdomains  Whether subdomains of this are blocked too
 * @property bool   $whitelisted If the server is marked as safe it won't be auto black listed
 * @property string $reason      The argument provided to block this domain.
 *
 * @deprecated since version 0.1-dev
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class DomainModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->type        = new IntegerField(true);
		$schema->host        = new StringField(50);
		$schema->list        = new StringField(15);
		$schema->reason      = new StringField(250);
		
		/*
		 * Indicates whether the system is having a hard time or not sending email
		 * to this domain and users to read it.
		 */
		$schema->deliverability = new FloatField(true);
		
		$schema->updated = new IntegerField(true);
		$schema->created = new IntegerField(true);
	}
	
	public function onbeforesave() {
		if (!$this->created) { $this->created = time(); }
		$this->updated = time();
	}

}
