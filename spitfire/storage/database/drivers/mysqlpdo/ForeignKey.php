<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\collection\Collection;
use spitfire\storage\database\Field;
use spitfire\storage\database\ForeignKeyInterface;

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
 * A foreign key is a special index in MySQL that allows the system to improve
 * performance on joins and to maintain data consistency in the database in 
 * the event of data being modified.
 * 
 * Spitfire defaults to cascading changes inside the database, since it's models
 * imply that when a child is orphaned it should be removed. If you still wish
 * to orphan a model it's recommended you orphan it before removing the parent.
 * 
 * This is similar to how you would expect the garbage collector of a object 
 * oriented language to work. If a element is deleted and the children are not 
 * referenced elsewhere we'd expect them to be gone.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ForeignKey extends Index implements ForeignKeyInterface
{
	
	/**
	 * Retrieves a collection of the fields referenced by this index. The fields
	 * returned are provided in the same order as they are defined.
	 * 
	 * @return Collection of database fields
	 */
	public function getReferenced(): Collection {
		$fields  = $this->getFields();
		$_ret    = new Collection();
		
		$fields->each(function(Field$e) use ($_ret) {
			$_ret->push($e->getReferencedField());
		});
		
		return $_ret;
	}
	
	/**
	 * Returns the name of the index. Since this refers to a remote table we will
	 * just appropriately prefix it.
	 * 
	 * @return string
	 */
	public function getName(): string {
		return 'foreign_' . parent::getName();
	}
	
	/**
	 * When the driver is assembling an SQL statement to assemble the table or
	 * repair it, this method will provide it with a statement to generate the 
	 * key.
	 * 
	 * @return string
	 */
	public function definition() {
		$referenced = $this->getReferenced();
		$table      = $referenced->first()->getTable();
		
		return sprintf(
			'FOREIGN KEY `%s` (%s) REFERENCES %s(%s) ON DELETE CASCADE ON UPDATE CASCADE',
			$this->getName(),
			$this->getFields()->each(function ($e) { return sprintf('`%s`', $e->getName()); })->join(', '),
			$table->getLayout(),
			$referenced->each(function ($e) { return sprintf('`%s`', $e->getName()); })->join(', ')
		);
	}

}
