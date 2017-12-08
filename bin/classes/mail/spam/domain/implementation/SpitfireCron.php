<?php namespace mail\spam\domain\implementation;

use mail\spam\domain\IP;
use mail\spam\domain\StorageInterface;
use spitfire\core\Collection;
use function db;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class SpitfireCron
{
	
	
	/**
	 * Returns a collection of elements that the system should be rechecking to 
	 * ensure that the IP list is up to date.
	 * 
	 * Refresh time can generally be rather generous, since server IPs don't
	 * change very often and are generally within a given block for big email
	 * servers.
	 * 
	 * Spam servers will often come and go, but these will be better caught by
	 * rechecking their blacklisted status on usage.
	 * 
	 * It seems this class was born dead. I abandoned the idea of adding crons to
	 * the mail blocking system to enhance it's ability of retrieving new domains
	 * automatically.
	 * 
	 * @deprecated since version 0.1-dev 20171208
	 * @param int $state This allows crons to report back the state they were in
	 * @return Collection
	 */
	public function execute($state = null) {
		/*
		 * Fetch the domains that require rechecking to make sure that the data
		 * contained in them still makes sense.
		 */
		$records = db()->table('email\domain')
			->get('expires', time(), '<')
			->addRestriction('type', StorageInterface::TYPE_HOSTNAME)
			->setResultsPerPage(4)
			->fetchAll();
		
		/*
		 * Loop over the expire domains AND the MX records associated to these
		 * domains, allowing us to prevent them from attacking the services again.
		 */
		foreach ($records as $domain) {
			IP::mx($domain->host)->each(function (IP$e) use ($domain) {
				$record = 
					db()->table('mail\domain')->get('type', \mail\spam\domain\StorageInterface::TYPE_IP)->addRestriction('host', $e->getBase64())->fetch()? : 
					db()->table('mail\domain')->newRecord();
				
				$record->expires = time() + 86400 * 90;
				$record->host    = $e->getBase64();
				$record->list    = $domain->list;
				$record->type    = StorageInterface::TYPE_IP;
				$record->store();
			});
		}
	}
}