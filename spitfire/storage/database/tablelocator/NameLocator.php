<?php namespace spitfire\storage\database\tablelocator;

use ReflectionClass;
use ReflectionException;
use spitfire\exceptions\PrivateException;
use spitfire\Model;
use spitfire\storage\database\DB;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Table;

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
 * Finds a table by the exact name provided.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class NameLocator implements TableLocatorInterface
{
	
	/**
	 * The database context for this locator to work.
	 *
	 * @var DB
	 */
	private $db;
	
	/**
	 * This locator will look for a model that matches the provided table names.
	 * 
	 * @param DB $db
	 */
	public function __construct(DB$db) {
		$this->db = $db;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * This method will look for a model that matches the provided name. If the 
	 * model exists and can be instanced it will be and returned.
	 * 
	 * @param string $tablename
	 * @return Table
	 * @throws PrivateException
	 */
	public function locate(string $tablename) {
		try {
			#Create a reflection of the Model
			$className = ucfirst($tablename) . 'Model';
			$reflection = new ReflectionClass($className);
			
			#Run some basic checks
			if ($reflection->isAbstract()) { throw new PrivateException('Model is abstract', 1710122036); }
			if (!$reflection->isSubclassOf(Model::class)) { throw new PrivateException('Model is not subclass of Model', 1710122037); }
		
			#Create a schema and a model
			$schema = new Schema($tablename);
			$model = $reflection->newInstance();
			$model->definitions($schema);
			
			#Return the newly created table
			return new Table($this->db, $schema);
		} 
		catch (ReflectionException$ex) {
			throw new PrivateException('No table ' . $tablename, 0, $ex);
		}
	}

}
