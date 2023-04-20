<?php namespace spitfire\storage\database;

use spitfire\model\Field as LogicalField;

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
 * The database object factory is a class that allows a driver to provide SF's 
 * ORM with all the required bits and pieces to operate. Usually a driver needs
 * to provide it's own Table, Query, Field... objects that implement / extend
 * the behavior required for the ORM to work.
 * 
 * Historically, a query would provide only the pieces it needed, as well as the
 * table would. But for consistency, and to avoid generating classes that only 
 * need to extend in order to provide factories we're merging those behaviors
 * in this single factory.
 */
interface ObjectFactoryInterface
{
	
	/**
	 * Creates a relation. These wrap the typical record operations on a table 
	 * into a separate layer.
	 * 
	 * @param Table $table
	 *
	 * @return Relation
	 */
	function makeRelation(Table$table);
	
	/**
	 * Creates a table layout to generate an appropriate schema for the DBMS to 
	 * store the data.
	 * 
	 * @param Table $table
	 * 
	 * @return LayoutInterface The layout for the table
	 */
	function makeLayout(Table$table);
	
	/**
	 * Creates a new On The Fly Schema. These allow the system to interact with a 
	 * database that was not modeled after Spitfire's models or that was not 
	 * reverse engineered previously.
	 *
	 * @param string $modelname
	 * 
	 * @return Table Instance of the table class the driver wants the system to use
	 * @todo Rename to generateSchema
	 */
	function getOTFSchema(DB$db, $modelname);
	
	/**
	 * Creates an instance of the Database field compatible with the current
	 * DBMS. As opposed to the Logical fields, physical fields do not accept 
	 * complex values, just basic types that any DBMS can handle.
	 * 
	 * @param Field    $field
	 * @param string   $name
	 * @param Field  $references
	 *
	 * @return Field Field
	 * @todo Rename to makeField
	 */
	function getFieldInstance(LogicalField$field, $name, Field$references = null);
	
	/**
	 * Creates a new restriction. This combines a query with a field and a value
	 * which allows to create the queries that we need to construct in order to 
	 * retrieve data.
	 * 
	 * @param string      $query
	 * @param QueryField  $field
	 * @param mixed       $value
	 * @param string|null $operator
	 * 
	 * @return Restriction|CompositeRestriction
	 * @todo Rename to makeRestriction
	 */
	function restrictionInstance($query, QueryField$field, $value, $operator = null);
	
	/**
	 * 
	 * @todo This is supposed to take a RestrictionGroup
	 * @param RestrictionGroup        $parent
	 * @param LogicalField $field
	 * @param mixed $value
	 * @param string $operator
	 */
	function restrictionCompositeInstance(RestrictionGroup$parent, LogicalField$field = null, $value = null, $operator = null);
	
	/**
	 * Creates a restriction group. This allows to associate several restrictions
	 * with each other to create more complicated queries when writing.
	 * 
	 * @param RestrictionGroup $parent
	 * @param int $type
	 * @return RestrictionGroup A restriction group
	 */
	function restrictionGroupInstance(RestrictionGroup$parent = null, $type = RestrictionGroup::TYPE_OR);

	/**
	 * Creates a new query. A query is created with a table to provide information
	 * where the data should be retrieved some and some information on the fields
	 * that we want it to provide.
	 *
	 * @param Table|Relation $table
	 *
	 * @return Query
	 * @todo Rename to makeQuery
	 */
	function queryInstance($table);
	
	
	/**
	 * These objects connect a field with a query, providing an aliased name for 
	 * the field when necessary.
	 * 
	 * @todo The second parameter should only accept physical and not logical fields
	 * 
	 * @param QueryTable       $queryTable
	 * @param Field|QueryField $field
	 * @return QueryField
	 */
	function queryFieldInstance(QueryTable$queryTable, $field);
	
	
	/**
	 * These objects connect a field with a query, providing an aliased name for 
	 * the field when necessary.
	 * 
	 * @param QueryTable|Table $table
	 * @return QueryTable
	 */
	function queryTableInstance($table);
}
