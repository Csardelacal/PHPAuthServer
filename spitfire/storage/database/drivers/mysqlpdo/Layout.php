<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use Reference;
use spitfire\cache\MemoryCache;
use spitfire\collection\Collection;
use spitfire\exceptions\PrivateException;
use spitfire\model\Index as LogicalIndex;
use spitfire\storage\database\Field;
use spitfire\storage\database\IndexInterface;
use spitfire\storage\database\LayoutInterface;
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
 * The MySQL PDO driver for the table layouts. This allows Spitfire to create,
 * destroy and repair tables on a MySQL or MariaDB database.
 * 
 * @todo This driver contains a lot of legacy code imported from the previous
 * iteration and would require a lot of fixing and maintaining.
 */
class Layout implements LayoutInterface
{
	/**
	 * The table that the system uses to connect layout and relation.
	 *
	 * @var Table
	 */
	private $table;
	
	/**
	 * The prefixed name of the table. The prefix is defined by the environment
	 * and allows to have several environments on the same database.
	 *
	 * @var string
	 */
	private $tablename;
	
	/**
	 * List of the physical fields this table handles. This array is just a 
	 * shortcut to avoid looping through model-fields everytime a query is
	 * performed.
	 *
	 * @var Field[] 
	 */
	private $fields;
	
	/**
	 * An array of indexes that this table defines to manage it's queries and 
	 * data.
	 *
	 * @var MemoryCache
	 */
	private $indexes;
	
	/**
	 * 
	 * @param Table $table
	 */
	public function __construct(Table$table) {
		#Assume the table
		$this->table = $table;
		
		#Get the physical table name. This will use the prefix to allow multiple instances of the DB
		$this->tablename = $this->table->getDb()->getSettings()->getPrefix() . $table->getSchema()->getTableName();
		
		#Create the physical fields
		$fields  = $this->table->getSchema()->getFields();
		$columns = Array();
		
		foreach ($fields as $field) {
			$physical = $field->getPhysical();
			while ($phys = array_shift($physical)) { $columns[$phys->getName()] = $phys; }
		}
		
		$this->fields  = $columns;
		$this->indexes = new MemoryCache();
	}
	
	public function create() {
		$table = $this;
		$definitions = $table->columnDefinitions();
		$indexes     = $table->getIndexes();
		
		#Strip empty definitions from the list
		$clean = array_filter(array_merge(
			$definitions, 
			$indexes->each(function($e) { return $e->definition(); })->toArray()
		));
		
		$stt = sprintf('CREATE TABLE %s (%s) ENGINE=InnoDB CHARACTER SET=utf8',
			$table,
			implode(', ', $clean)
			);
		
		return $this->table->getDb()->execute($stt);
	}

	public function destroy() {
		$this->table->getDb()->execute('DROP TABLE ' . $this);
	}
	
	/**
	 * Fetch the fields of the table the database works with. If the programmer
	 * has defined a custom set of fields to work with, this function will
	 * return the overridden fields.
	 * 
	 * @return Field[] The fields this table handles.
	 */
	public function getFields() {
		return $this->fields;
	}
	
	public function getField($name) : Field {
		#If the data we get is already a DBField check it belongs to this table
		if ($name instanceof Field) {
			if ($name->getTable() === $this->table) { return $name; }
			else { throw new PrivateException('Field ' . $name . ' does not belong to ' . $this); }
		}
		
		if (is_object($name)) {
			throw new PrivateException('Expected a field name, got an object', 1708101329);
		}
		
		#Otherwise search for it in the fields list
		if (isset($this->fields[(string)$name])) { return $this->fields[(string)$name]; }
		
		#The field could not be found in the Database
		throw new PrivateException('Field ' . $name . ' does not exist in ' . $this);
	}
	
	/**
	 * 
	 * @return Collection <Index>
	 */
	public function getIndexes() {
		
		return $this->indexes->get('indexes', function() {
			
			/*
			 * First we get the defined indexes.
			 */
			$logical = $this->table->getSchema()->getIndexes();
			$indexes = $logical->each(function (LogicalIndex$e) {
				return new Index($e);
			});
			
			/*
			 * Then we get those implicitly defined by reference fields. These are 
			 * defined by the driver, sicne they're required for it to work.
			 */
			$fields = array_filter($this->table->getSchema()->getFields(), function($e) { return $e instanceof Reference;});
			
			foreach ($fields as $field) {
				$indexes->push(new ForeignKey(new LogicalIndex([$field])));
			}
			
			return $indexes;
		});
	}

	public function getTableName() : string {
		return $this->tablename;
	}

	public function repair() {
		$stt = "DESCRIBE {$this}";
		$fields = $this->getFields();
		
		foreach ($this->table->getSchema()->getFields() as $f) {
			if ($f instanceof Reference && $f->getTarget() !== $this->table->getSchema()) {
				$f->getTarget()->getTable()->getLayout()->repair();
			}
		}
		//Fetch the DB Fields and create on error.
		try {
			$query = $this->table->getDb()->execute($stt, Array(), false);
		}
		catch(\Exception $e) {
			return $this->create();
		}
		//Loop through the exiting fields
		while (false != ($f = $query->fetch())) {
			try {
				$field = $this->getField($f['Field']);
				unset($fields[$field->getName()]);
			}
			catch(\Exception $e) {/*Ignore*/}
		}
		
		foreach($fields as $field) $field->add();
	}
	
	/**
	 * Creates the column definitions for each column
	 * 
	 * @return mixed
	 */
	protected function columnDefinitions() {
		$fields = $this->getFields();
		foreach ($fields as $name => $f) {
			$fields[$name] = '`'. $name . '` ' . $f->columnDefinition();
		}
		return $fields;
	}
	
	/**
	 * Returns the name of a table as DB Object reference (with quotes).
	 * 
	 * @return string The name of the table escaped and ready for use inside
	 *                of a query.
	 */
	public function __toString() {
		return "`{$this->tablename}`";
	}

}
