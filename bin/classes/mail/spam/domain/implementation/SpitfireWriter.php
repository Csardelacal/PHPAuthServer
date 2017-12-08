<?php namespace mail\spam\domain\implementation;

use mail\domain\ReaderInterface;
use mail\domain\WriterInterface;

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

class SpitfireWriter implements WriterInterface
{
	
	private $db;
	
	/**
	 * 
	 * @param type $db
	 */
	public function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * 
	 * @param string $host
	 * @param int    $list
	 * @param int    $type
	 * @param int    $subdomains
	 * @param string $reason
	 */
	public function addEntry($host, $list, $type, $subdomains, $reason) {
		
		$record = $this->db->table('email\domain')->newRecord();
		$record->type = $type & ReaderInterface::TYPE_IP? ReaderInterface::TYPE_IP : ReaderInterface::TYPE_HOSTNAME;
		$record->host = $host;
		$record->list = $list;
		$record->subdomains = $subdomains;
		$record->reason  = $reason;
		$record->expires = time() + 86400 * 90;
		$record->store();
		
		return true;
	}

}
