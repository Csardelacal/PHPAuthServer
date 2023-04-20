<?php namespace spitfire\storage\database\tablelocator;

use spitfire\exceptions\PrivateException;
use spitfire\storage\database\DB;

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

/**
 * The OTF Table locator allows the system to create models on the fly when the
 * programmer tries to access a table that exists on the DBMS but is not defined
 * within the application's models.
 */
class OTFTableLocator implements TableLocatorInterface
{
	
	
	/**
	 * The database context for this locator to work.
	 *
	 * @var DB
	 */
	private $db;
	
	/**
	 * Creates a new on the fly locator. This locator will be required to retrieve
	 * a dynamically generated Schema from the database and inject it into a Table.
	 * 
	 * @param DB $db
	 */
	public function __construct($db) {
		$this->db = $db;
	}
	
	/**
	 * Retrieves a table from the DBMS on the fly. Please note that this locator
	 * is very inefficient and should only be used in development and legacy apps.
	 * 
	 * @param string $tablename
	 * @return boolean
	 */
	public function locate(string $tablename) {
		
		#Get the OTF model
		try {	return $this->db->getObjectFactory()->getOTFSchema($this->db, $tablename); }
		catch (PrivateException$e) { return false; }
	}

}