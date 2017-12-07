<?php namespace email;

use BooleanField;
use IntegerField;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use function collect;

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
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class DomainModel extends Model
{
	
	public function definitions(Schema $schema) {
		$schema->type        = new IntegerField(true);
		$schema->host        = new StringField(50);
		$schema->subdomains  = new BooleanField();
		$schema->list        = new StringField(15);
		$schema->reason      = new StringField(250);
		$schema->expires     = new IntegerField(true);
	}
	
	/**
	 * Check whether a domain has been blocked by an administrator.
	 * 
	 * @param string $host
	 * @return boolean
	 */
	public static function check($host) {
		$reader = new \mail\domain\implementation\SpitfireReader($this->getTable()->getDb());
		$writer = new \mail\domain\implementation\SpitfireWriter($this->getTable()->getDb());
		
		$domain = new \mail\domain\Domain($host, $reader, $writer);
		return $domain->isBanned();
	}

}