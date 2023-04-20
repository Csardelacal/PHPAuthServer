<?php namespace spitfire\storage\database;

use spitfire\exceptions\PrivateException;

/**
 * A restriction indicates a condition a record in a database's relation must 
 * satisfy to be returned by a database query.
 * 
 * Restrictions can be either simple (like these) or composite. These simple ones
 * can only contain basic data-types like integers, floats, strings or enums as
 * their value.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
abstract class Restriction
{
	/** 
	 * 
	 * @var RestrictionGroup 
	 */
	private $parent;
	
	/**
	 *
	 * @var QueryField
	 */
	private $field;
	private $value;
	private $operator;
	
	const LIKE_OPERATOR  = 'LIKE';
	const EQUAL_OPERATOR = '=';
	
	public function __construct($parent, $field, $value, $operator = '=') {
		if (is_null($operator)) { 
			$operator = self::EQUAL_OPERATOR;
		}
		
		if (!$parent instanceof RestrictionGroup && $parent !== null) { 
			throw new PrivateException("A restriction's parent can only be a group", 1804292129); 
		}
		
		if (!$field instanceof QueryField) {
			throw new PrivateException("Invalid field");
		}
		
		$this->parent    = $parent;
		$this->field    = $field;
		$this->value    = $value;
		$this->operator = trim($operator);
	}
	
	public function getTable(){
		return $this->field->getField()->getTable();
	}
	
	public function setTable() {
		throw new PrivateException('Deprecated');
	}
	
	public function getField() {
		return $this->field;
	}
	
	/**
	 * Returns the query this restriction belongs to. This allows a query to 
	 * define an alias for the table in order to avoid collissions.
	 * 
	 * @return Query
	 */
	public function getQuery() {
		return $this->parent->getQuery();
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function setParent($parent) {
		$this->parent = $parent;
	}
	
	/**
	 * 
	 * @param Query $query
	 * @deprecated since version 0.1-dev 1604162323
	 */
	public function setQuery($query) {
		$this->parent = $query;
		$this->field->setQuery($query);
	}
	
	public function getOperator() {
		if (is_array($this->value) && $this->operator != 'IN' && $this->operator != 'NOT IN') return 'IN';
		return $this->operator;
	}

	public function getValue() {
		return $this->value;
	}
	
	
	public function getPhysicalSubqueries() {
		return Array();
	}
	
	
	public function getSubqueries() {
		return Array();
	}
	
	public function getConnectingRestrictions() {
		return Array();
	}
	
	public function replaceQueryTable($old, $new) {
		
		if ($this->field->getQueryTable() === $old) {
			$this->field->setQueryTable($new);
		}
		
		if ($this->value instanceof QueryField && $this->value->getQueryTable() === $old) {
			$this->value->setQueryTable($new);
		}
	}
	
	public function negate() {
		switch ($this->operator) {
			case '=': 
				return $this->operator = '<>';
			case '<>': 
				return $this->operator = '=';
			case '>': 
				return $this->operator = '<';
			case '<': 
				return $this->operator = '>';
			case 'IS': 
				return $this->operator = 'IS NOT';
			case 'IS NOT': 
				return $this->operator = 'IS';
			case 'LIKE': 
				return $this->operator = 'NOT LIKE';
			case 'NOT LIKE': 
				return $this->operator = 'LIKE';
		}
	}
	
	/**
	 * Restrictions must be able to be casted to string. This is not only often
	 * necessary for many drivers to generate queries but also for debugging.
	 * 
	 * @return string
	 */
	abstract public function __toString();
}
