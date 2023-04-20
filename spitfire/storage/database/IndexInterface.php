<?php namespace spitfire\storage\database;

use spitfire\collection\Collection;

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
 * This interface allows the database driver to provide the system with indexes
 * it can understand. These indexes will be fed back to the driver in the event
 * the system detects a missing table. Allowing the driver to assemble the table
 * properly.
 */
interface IndexInterface
{
	
	/**
	 * Returns an array of fields, please note that your driver should respect 
	 * the order on these. The order of the fields may affect query performance
	 * heavily on a relational model.
	 * 
	 * @return Collection <Field>
	 */
	function getFields();
	
	/**
	 * Returns a name for the index. In most DBMS this is optional, but allows 
	 * for better understanding of the schema.
	 * 
	 * @return string
	 */
	function getName() : string;
	
	/**
	 * Indicates whether this is a unique index. Therefore requesting the DBMS to
	 * enforce no-duplicates on the index.
	 * 
	 * A driver requesting this value should always OR this value with isPrimary()
	 * like $index->isOptional() || $index->isPrimary() to know whether a index
	 * is unique.
	 * 
	 * @see IndexInterface::isPrimary()
	 * @return bool
	 */
	function isUnique() : bool;
	
	/**
	 *	Indicates whether this index is primary. If your index returns this value
	 * as true, the isUnique() value will be overriden by the system internally.
	 * 
	 * @return bool
	 */
	function isPrimary() : bool;
}
