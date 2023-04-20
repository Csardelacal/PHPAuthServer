<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\collection\Collection;
use spitfire\model\Index as LogicalIndex;
use spitfire\storage\database\IndexInterface;

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
 * The MySQLPDO friendly implementation of indexes.
 * 
 */
class Index implements IndexInterface
{
	
	private $logical;
	
	public function __construct(LogicalIndex$index) {
		$this->logical = $index;
	}
	
	public function getFields() {
		/*
		 * Prepare an array for the fields.
		 */
		$arr = new \spitfire\collection\Collection();

		/**
		 * Each field has one / many physical fields that need to be brought into
		 * the physical index to be generated.
		 */
		$this->logical->getFields()->each(function ($p) use ($arr) { 
			$arr->add(Collection::fromArray($p->getPhysical())); 
		});
		
		return $arr;
	}

	public function getName(): string {
		return $this->logical->getName();
	}

	public function isPrimary(): bool {
		return $this->logical->isPrimary();
	}

	public function isUnique(): bool {
		return $this->logical->isUnique();
	}
	
	/**
	 * 
	 * @return LogicalIndex
	 */
	public function getLogical() : LogicalIndex {
		return $this->logical;
	}
	
	public function definition() {
		return sprintf(
			'%s `%s` (%s)',
			$this->isPrimary()? 'PRIMARY KEY' : ($this->isUnique()? 'UNIQUE INDEX' : 'INDEX'),
			$this->getName()? : '',
			$this->getFields()->each(function ($e) { return sprintf('`%s`', $e->getName()); })->join(', ')
		);
	}

}
