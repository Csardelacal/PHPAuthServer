<?php namespace email;

use BooleanField;
use IntegerField;
use spitfire\Model;
use spitfire\storage\database\Schema;
use StringField;
use function db;

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
	
	const TYPE_DOMAIN = 0;
	const TYPE_IP     = 1;
	
	public function definitions(Schema $schema) {
		$schema->type        = new IntegerField(true);
		$schema->host        = new StringField(50);
		$schema->subdomains  = new BooleanField();
		$schema->whitelisted = new BooleanField();
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
		$pieces = explode('.', $host);
		$first  = true;
		$db     = $this->getTable()->getDb();
		
		while (!empty($pieces)) {
			#For some fancy tld like .co.uk we need to stop early.
			$whitelist = ['org', 'co', 'uk', 'com', 'ca', 'au', 'es', 'de'];
			
			if(count($pieces) < 3 && collect($pieces)->reduce(function ($e, $p) use ($whitelist) { 
				return $p && strlen($e) <= 3 && in_array($e, $whitelist);
			}, true)) { return false; }
			
			#Check the record
			if(!getmxrr($host, $mxhosts)) { return true; }
			
			$ips = collect($mxhosts)->each(function ($e) { return base64encode(inet_pton(gethostbyname($e))); });
			
			#Search the table for the domain
			$query = $db->table('email\domain')->getAll();
		   $query->group()
				->addRestriction('host', implode('.', $pieces))
				->addRestriction('host', $ips->toArray());
			
			#If we're checking on a parent
			if (!$first) { $query->addRestriction('subdomains', true); }
			else         { $first = false; }
			
			$record = $query->fetch();
			
			#If the query is a hit, we return that the domain is blocked
			if ($record && !$record->whitelisted) { 
				return true; 
			}
			
			#Otherwise we shorten the domain
			array_shift($pieces);
		}
		
		return false;
	}

}