<?php namespace spitfire\model;

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
 * An Index allows your application to define a series of fields that the DBMS 
 * should index in order to improve performance of data retrieval.
 * 
 * Please note that indexes on the logical level are "suggestions" that allow
 * the DBMS to improve performance, but these are not required to be followed.
 */
class Index
{
	
	/**
	 * The fields that the index contains. Please note that many DBMS (basically 
	 * all of them) are sensitive to the order in which the fields are provided
	 * to the index and they will therefore perform better (or worse) when used 
	 * properly.
	 *
	 * @var Collection
	 */
	private $fields;
	
	/**
	 * The name to be given to this index. This will be then suggested to the 
	 * DBMS. This does not guarantee to be the name the system will end up choosing.
	 *
	 * @var string
	 */
	private $name;
	
	/**
	 * Indicates whether this index is unique. Please note that spitfire will 
	 * override this setting to bool(true) if the index is also primary.
	 *
	 * @var bool 
	 */
	private $unique = false;
	
	/**
	 * Indicates whether this key is a primary key. Every table can only have one 
	 * primary key and it is required to have one to create relations between the
	 * tables.
	 *
	 * @var bool
	 */
	private $primary = false;
	
	/**
	 * Creates a new index for the schema.
	 * 
	 * @param Field[] $fields
	 */
	public function __construct($fields = null) {
		$this->fields = Collection::fromArray($fields);
	}
	
	/**
	 * Return the field collection
	 * 
	 * @return Collection containing the <code>Field</code>s in this index
	 */
	public function getFields() {
		return $this->fields;
	}
	
	/**
	 * Indicates whether a field is contained in this index. This allows an app
	 * to check whether it needs to remove an index when a field is removed.
	 * 
	 * @param \spitfire\model\Field $f
	 * @return bool
	 */
	public function contains(Field$f) {
		return $this->fields->contains($f);
	}
	
	/**
	 * Returns the name of the index (if given) and generates a standard name for
	 * the index when there is none. The format for these is
	 * 
	 * idx_tablename_field1_field2
	 * 
	 * @return string
	 */
	public function getName() {
		/*
		 * If the index already has a name we roll with that.
		 */
		if(!empty($this->name)) { return $this->name; }
		
		/*
		 * Get the table name, this way we can generate a meaningful index name
		 * when it's written to the database.
		 */
		$tablename  = $this->fields->first()->getSchema()->getTableName();
		
		/*
		 * Implode the names of the fields being passed to the index. This way the 
		 * 
		 */
		$imploded = $this->fields->each(function ($e) { 
			return $e->getName();
		})->join('_');
		
		/*
		 * Generate a name from the fields for the index
		 * - All indexes are identified by idx
		 * - Then comes the table name
		 * - Lastly we add the fields composing the index
		 */
		return $this->name = 'idx_' . $tablename . '_' . $imploded;
	}
	
	public function isUnique() {
		return $this->unique || $this->primary;
	}
	
	public function isPrimary() {
		return $this->primary;
	}
	
	public function setFields($fields) {
		$this->fields = $fields;
		return $this;
	}
	
	public function putField(Field$field) {
		$this->fields->push($field);
	}
	
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	public function unique($unique = true) {
		$this->unique = $unique;
		return $this;
	}
	
	public function setPrimary($isPrimary) {
		$this->primary = $isPrimary;
		return $this;
	}
}
