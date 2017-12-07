<?php namespace mail\domain\implementation;

use mail\domain\ReaderInterface;

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

class SpitfireReader implements ReaderInterface
{
	
	/**
	 *
	 * @var \spitfire\storage\database\DB
	 */
	private $db;
	
	public function __construct(\spitfire\storage\database\DB $db) {
		$this->db = $db;
	}
	
	/**
	 * 
	 * @param type $timestamp
	 */
	public function getDomainsRefreshedBefore($timestamp) {
		$records = db()->table('email\domain')
			->get('expires', $timestamp, '<')
			->addRestriction('type', ReaderInterface::TYPE_HOSTNAME)
			->setResultsPerPage(4)
			->fetchAll();
		
		return $records->each(function ($e) {
			return new \mail\domain\Domain($e->host, $this, new SpitfireWriter($this->db));
		});
	}
	
	/**
	 * 
	 * @param type $host
	 * @param type $type
	 * @return type
	 */
	public function isBlacklisted($host, $type = ReaderInterface::TYPE_HOSTNAME) {
		
		$record = db()->table('email\domain')
			->get('host', $host)
			->addRestriction('type', $type)
			->addRestriction('expires', time(), '>')
			->fetch();
		
		return !!$record;
	}

}
