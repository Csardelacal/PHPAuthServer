<?php namespace spitfire\storage\database;

use InvalidArgumentException;
use spitfire\cache\MemoryCache;
use spitfire\exceptions\PrivateException;
use spitfire\storage\database\tablelocator\CacheLocator;
use spitfire\storage\database\tablelocator\NameLocator;
use spitfire\storage\database\tablelocator\OTFTableLocator;
use spitfire\storage\database\tablelocator\TableLocatorInterface;
use spitfire\storage\database\tablelocator\TypoCacheLocator;
use spitfire\storage\database\tablelocator\TypoLocator;
use Strings;

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
 * Contains a table list for a database. Please note that this system is neither
 * caps sensitive nor is it plural sensitive. When looking for the table
 * "deliveries" it will automatically check for "delivery" too.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class TablePool
{
	
	/**
	 * The database this contains tables for. This is important since the database
	 * offloads table "makes" to the pool.
	 *
	 * @var TableLocatorInterface[]
	 */
	private $tableLocators;
	
	private $cache;
	
	/**
	 * Creates a new Table pool object. This object is designed to cache tables 
	 * across several queries, allowing for them to refer to the same schemas and
	 * data-caches that the tables provide.
	 * 
	 * @param DB $db
	 */
	public function __construct(DB$db) {
		$this->cache         = new MemoryCache();
		
		$this->tableLocators = [
			 new CacheLocator($this->cache),
			 new NameLocator($db),
			 new TypoCacheLocator($this->cache, function ($e) { return Strings::singular($e); }),
			 new TypoCacheLocator($this->cache, function ($e) { return Strings::plural($e); }),
			 new TypoLocator($db, function ($e) { return Strings::singular($e); }),
			 new TypoLocator($db, function ($e) { return Strings::plural($e); }),
			 new OTFTableLocator($db)
		];
	}
	
	/**
	 * Pushes a table into the pool. This method will check that it's receiving a
	 * proper table object.
	 * 
	 * @param string $key
	 * @param Table $value
	 * @return Table
	 * @throws InvalidArgumentException
	 */
	public function set($key, $value) {
		if (!$value instanceof Table) { 
			throw new InvalidArgumentException('Table is required'); 
		}
		
		return $this->cache->set(strtolower($key), $value);
	}
	
	/**
	 * Returns the Table that the user is requesting from the pool. The pool will
	 * automatically check if the table was misspelled.
	 * 
	 * @param string $key
	 * @return Table
	 * @throws PrivateException
	 */
	public function get($key) {
		$table = false;
		$locators = $this->tableLocators;
		
		while (!$table && $locators) {
			$locator = array_shift($locators);
			$table   = $locator->locate($key);
		}
		
		if ($table) {
			return $this->set($key, $table);
		}
		
		throw new PrivateException(sprintf('Table %s was not found', $key));
	}
	
	public function contains($key) {
		
		try {
			$this->get($key);
			return true;
		} catch (PrivateException $ex) {
			return false;
		}
	}
	
	public function getCache() {
		return $this->cache;
	}
	
}