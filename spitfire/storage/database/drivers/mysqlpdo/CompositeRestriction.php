<?php namespace spitfire\storage\database\drivers\mysqlpdo;

use spitfire\exceptions\PrivateException;
use spitfire\storage\database\CompositeRestriction as ParentClass;
use spitfire\storage\database\RestrictionGroup;

class CompositeRestriction extends ParentClass
{

	/**
	 * When a query is serialized, the composite restrictions generate a list of
	 * simple ones that can be passed onto the database for querying.
	 * 
	 * In the case of MySQLPDO, the driver assumes that the query has been properly
	 * denormalized to be serialized.
	 * 
	 * @return RestrictionGroup
	 */
	public function makeSimpleRestrictions() {
		
		$of    = $this->getQuery()->getTable()->getDb()->getObjectFactory();
		
		/*
		 * Extract the primary fields for the remote table so we can indicate to the
		 * database whether they should be null or not.
		 * 
		 * Please note that we will always use "IS NOT NULL" so the connectors stay
		 * consistent with the rest of the restrictions
		 */
		$fields = $this->getValue()->getQueryTable()->getTable()->getPrimaryKey()->getFields();
		$group  = $of->restrictionGroupInstance($this->getParent());
		
		/*
		 * Loop over the fields and put them in an array so it can be concatenated
		 * before being returned.
		 */
		foreach($fields as $field) {
			$qt = $this->getValue()->getRedirection()? $this->getValue()->getRedirection()->getQueryTable() : $this->getValue()->getQueryTable();
			$group->push($of->restrictionInstance($group, $of->queryFieldInstance($qt, $field), null, $this->getOperator() === '='? 'IS NOT' : 'IS'));
		}
		
		return $group;
	}
	
	public function __toString() {
		$field = $this->getField();
		$value = $this->getValue();
		
		if ($field === null || $value === null) {
			throw new PrivateException('Deprecated: Composite restrictions do not receive null parameters', 2801191504);
		} 
		
		return strval($this->makeSimpleRestrictions());
		
	}
	
}