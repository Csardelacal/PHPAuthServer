<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use BadMethodCallException;
use spitfire\exceptions\PrivateException;
use spitfire\model\Field as LogicalField;
use spitfire\storage\database\DB;
use spitfire\storage\database\drivers\mysqlpdo\Field as MysqlField;
use spitfire\storage\database\drivers\mysqlpdo\Query;
use spitfire\storage\database\drivers\mysqlpdo\Restriction;
use spitfire\storage\database\drivers\mysqlpdo\RestrictionGroup;
use spitfire\storage\database\Field;
use spitfire\storage\database\LayoutInterface;
use spitfire\storage\database\ObjectFactoryInterface;
use spitfire\storage\database\QueryField as AbstractQueryField;
use spitfire\storage\database\QueryTable as AbstractQueryTable;
use spitfire\storage\database\Relation as RelationAbstract;
use spitfire\storage\database\RestrictionGroup as AbstractRestrictionGroup;
use spitfire\storage\database\Schema;
use spitfire\storage\database\Table;
use TextField;

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

/**
 * The object factory class allows a Database to centralize a point where the 
 * database objects can retrieve certain items from. As opposed to having this
 * algorithms in every class, as some classes would just be overriding one factory
 * method they needed in a completely standard class.
 * 
 * This allows Spitfire to define certain behaviors it expects from DB objects
 * and then have the driver provide this to not disturb Spitfire's logic.
 *
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ObjectFactory implements ObjectFactoryInterface
{
	
	/**
	 * Creates a new on the fly model. This means that the model is created during
	 * runtime, and by reverse engineering the tables that the database already
	 * has.
	 * 
	 * Please note, that this model would not perfectly replicate a model you could
	 * build with a proper definition yourself.
	 * 
	 * @todo  At the time of writing this, the method does not use adequate types.
	 * @param string $modelname
	 * @return Table
	 */
	public function getOTFSchema(DB$db, $modelname) {
		#Create a Schema we can feed the data into.
		$schema  = new Schema($modelname);
		
		#Make the SQL required to read in the data
		$sql    = sprintf('DESCRIBE `%s%s`', $schema->getTableName(), $modelname);
		/** @var $fields Query */
		$fields = $db->execute($sql, false);
		
		while ($row = $fields->fetch()) { 
			$schema->{$row['Field']} = new TextField(); 
		}
		
		return new Table($db, $schema);
	}
	
	/**
	 * Creates a new MySQL PDO Field object. This receives the fields 'prototype',
	 * name and reference (in case it references an externa field).
	 * 
	 * This represents an actual field in the DBMS as opposed to the ones in the 
	 * model. That's why here we talk of "physical" fields
	 * 
	 * @todo  This should be moved over to a DBMS specific object factory.
	 * @param Field   $field
	 * @param string  $name
	 * @param Field $references
	 * @return MysqlField
	 */
	public function getFieldInstance(LogicalField$field, $name, Field$references = null) {
		return new MysqlField($field, $name, $references);
	}

	public function restrictionInstance($query, AbstractQueryField$field, $value, $operator = null) {
		return new Restriction($query,	$field, $value, $operator);
	}

	/**
	 * Makes a new query on a certain table.
	 *
	 * @param Table $table
	 *
	 * @return Query
	 * @throws PrivateException
	 */
	public function queryInstance($table) {
		if ($table instanceof RelationAbstract){ $table = $table->getTable(); }
		if (!$table instanceof Table) { throw new PrivateException('Need a table object'); }
		
		return new Query($table);
	}

	public function makeRelation(Table $table) {
		return new Relation($table);
	}
	
	public function __call($name, $args) {
		throw new BadMethodCallException("Called ObjectFactory::$name. Method does not exist");
	}

	public function makeLayout(Table $table): LayoutInterface {
		return new Layout($table);
	}

	public function restrictionGroupInstance(AbstractRestrictionGroup$parent = null, $type = AbstractRestrictionGroup::TYPE_OR): AbstractRestrictionGroup {
		$g = new RestrictionGroup($parent);
		$g->setType($type);
		return $g;
	}
	
	
	public function queryFieldInstance(AbstractQueryTable$queryTable, $field) {
		if ($field instanceof AbstractQueryField) {return $field; }
		return new QueryField($queryTable, $field);
	}
	
	
	public function queryTableInstance($table) {
		if ($table instanceof Relation) { $table = $table->getTable(); }
		if ($table instanceof AbstractQueryTable) { $table = $table->getTable(); }
		
		
		if (!$table instanceof Table) { throw new PrivateException('Did not receive a table as parameter'); }
		
		return new QueryTable($table);
	}

	public function restrictionCompositeInstance(AbstractRestrictionGroup$parent, LogicalField$field = null, $value = null, $operator = null) {
		return new CompositeRestriction($parent, $field, $value, $operator);
	}

}
