<?php namespace spitfire\storage\database;

use spitfire\model\Field as Logical;

/**
 * The query field object is a component that allows a Query to wrap a field and
 * connect it to itself. This is important for the DBA since it allows the app
 * to establish connections between the different queries when assembling SQL
 * or similar.
 * 
 * When a query is connected to a field, you may use this to establish relationships
 * and create complex queries that can properly be joined.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 * @abstract
 */
abstract class QueryField
{
	/** 
	 * The actual database field. Note that this field is 
	 * 
	 * @var Logical 
	 */
	private $field;
	
	/**
	 *
	 * @var QueryTable
	 */
	private $table;
	
	public function __construct(QueryTable$table, $field) {
		$this->table = $table;
		$this->field = $field;
	}
	
	/**
	 * Returns the parent Table for this field. 
	 * 
	 * @return QueryTable
	 */
	public function getQueryTable() : QueryTable {
		return $this->table;
	}
	
	public function setQueryTable(QueryTable $table) {
		$this->table = $table;
		return $this;
	}
	
	/**
	 * Returns the source field for this object.
	 * 
	 * @return Logical|Field
	 */
	public function getField() {
		return $this->field;
	}
	
	/**
	 * 
	 * 
	 * @return bool
	 */
	public function isLogical() : bool {
		return $this->field instanceof Logical;
	}
	
	/**
	 * Returns an array of fields that compose the physical components of the 
	 * field. This method automatically converts the fields to QueryField so they
	 * can be used again.
	 * 
	 * @return Field[]
	 */
	public function getPhysical() : array {
		/*
		 * Get the object factory for the current DB connection. It is then used 
		 * to create physical copies of logical fields.
		 */
		$of = $this->table->getTable()->getDb()->getObjectFactory();
		
		if ($this->isLogical()) {
			$fields = $this->field->getPhysical();
			
			foreach ($fields as &$field) {
				$field = $of->queryFieldInstance($this->table, $field);
			}
			
			return $fields;
		}
		
		return [$of->queryFieldInstance($this->table, $this->field)];
	}
	
	/**
	 * Many drivers use this objects to generate "object identifiers", strings that
	 * indicate what field in which table is being adressed. So we're forcing driver
	 * vendors to implement the __toString method to achieve the most consistent
	 * result possible.
	 * 
	 * This may not be the case for your driver. In this event, just return a string
	 * that may be used for debugging and create an additional method for your
	 * driver.
	 * 
	 * @return string
	 */
	abstract public function __toString();
}
