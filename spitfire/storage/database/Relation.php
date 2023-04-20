<?php namespace spitfire\storage\database;

/*
 * The MIT License
 *
 * Copyright 2016 César de la Cal Bretschneider <cesar@magic3w.com>.
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
use spitfire\exceptions\PrivateException;
use spitfire\Model;

/**
 * When we think about a table in a common DBMS we think of the combination of
 * a table schema, which defines the fields and what data they can contain, and
 * a relation of records which can be used to maintain your data.
 * 
 * This class represents the collection, it will provide the mechanisms to CRUD 
 * records and also may provide caching for the database.
 * 
 * @todo Relations should also provide a proper caching mechanism
 * @todo Move "below" a table.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
abstract class Relation
{
	
	/**
	 * Contains the information about the schema used to construct the database 
	 * table and methods for table manipulation.
	 * 
	 * Usually when we speak of a table in a RDBMS we talk about the combination
	 * of schema + data which assembles the table. The spitfire table is the 
	 * schema part and the collection the data.
	 *
	 * @var Table
	 */
	private $table;
	
	/**
	 * Creates the collection. We need the table as it provides the infrastructure
	 * for the collection to work.
	 * 
	 * @param Table $table
	 */
	public function __construct(Table$table) {
		$this->table = $table;
	}
	
	/**
	 * Returns the table this collection uses. The table is the object providing
	 * schema, and table operations on the RDBMS.
	 * 
	 * @return Table
	 */
	public function getTable() {
		return $this->table;
	}
	
	public function getDb() {
		return $this->table->getDb();
	}

	/**
	 * Returns the bean this model uses to generate Forms to feed itself with data
	 * the returned value normally is a class that inherits from CoffeeBean.
	 *
	 * @param string|null $name
	 *
	 * @return \CoffeeBean
	 */
	public function getBean($name = null) {
		
		if (!$name) { $beanName = $this->getTable()->getSchema()->getName() . 'Bean'; }
		else        { $beanName = $name . 'Bean'; }
		
		$bean = new $beanName($this->getTable());
		
		return $bean;
	}

	/**
	 * Creates a new record in this table
	 *
	 * @param array $data
	 *
	 * @return Model Record for the selected table
	 */
	public function newRecord($data = []) {
		$classname = $this->table->getModel()->getName() . 'Model';
		
		if (class_exists($classname)) { return new $classname($this->getTable(), $data); }
		else { return new \spitfire\model\OTFModel($this->getTable(), $data); }
	}
	
	/**
	 * Increments a value on high read/write environments. Using update can
	 * cause data to be corrupted. Increment requires the data to be in sync
	 * aka. stored to database.
	 *
	 * @param Model     $record
	 * @param string    $key
	 * @param int|float $diff
	 *
	 * @throws PrivateException
	 */
	public abstract function increment(Model$record, $key, $diff = 1);

	/**
	 * Creates a simple query with a simple restriction applied to it. This
	 * is especially useful for id related queries.
	 *
	 * @param string      $field
	 * @param string      $value
	 * @param string|null $operator
	 * @return Query
	 */
	public function get($field, $value, $operator = null) {
		#Create the query
		$table = $this->getTable();
		$query = $table->getDb()->getObjectFactory()->queryInstance($table);
		$query->addRestriction($field, $value, $operator);
		#Return it
		return $query;
	}

	/**
	 * Creates an empty query that would return all data. This is a syntax
	 * friendliness oriented method as it does exactly the same as startQuery
	 *
	 * @return Query
	 */
	public function getAll() {
		$table = $this->getTable();
		$query = $table->getDb()->getObjectFactory()->queryInstance($table);
		return $query;
	}

	public abstract function delete(Model$record);
	public abstract function insert(Model$record);
	public abstract function update(Model$record);

	/**
	 * While development is ongoing, we will try to recover from errors due to the
	 * separation between Tables and Collections by sending all calls that were
	 * intended for a table to the table itself
	 *
	 * @todo Remove
	 *
	 * @param string $name
	 * @param mixed  $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		#Pass on
		trigger_error('Called Relation::__call. This should not happen. Missing argument: ' . $name, E_USER_DEPRECATED);
		return call_user_func_array(Array($this->getDb(), $name), $arguments);
	}
}
