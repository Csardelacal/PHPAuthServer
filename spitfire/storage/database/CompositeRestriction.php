<?php namespace spitfire\storage\database;

use spitfire\model\Field as Logical;
use spitfire\Model;
use BadMethodCallException;

class CompositeRestriction
{
	private $parent;
	private $field;
	private $value;
	private $operator;
	
	public function __construct(RestrictionGroup$parent, Logical$field = null, $value = null, $operator = Restriction::EQUAL_OPERATOR) {
		
		if ($value instanceof Model) { $value = $value->getQuery(); }
		if ($value instanceof Query) { $value->setAliased(true); }
		else { throw new BadMethodCallException('Composite restriction requires a query / model as value', 1804201334); }
		
		$this->parent = $parent;
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
	}
	
	/**
	 * 
	 * @return Query
	 */
	public function getQuery() {
		return $this->parent? $this->parent->getQuery() : null;
	}
	
	/**
	 * 
	 * @return RestrictionGroup
	 */
	public function getParent() {
		return $this->parent;
	}

	public function setQuery(Query$query) {
		$this->parent = $query;
	}

	public function setParent(RestrictionGroup$query) {
		$this->parent = $query;
		return $this;
	}

	public function getField() {
		return $this->field;
	}

	public function setField(Logical$field) {
		$this->field = $field;
	}
	
	/**
	 * 
	 * @return Query
	 */
	public function getValue() {
		if ($this->value instanceof Model) { $this->value = $this->value->getQuery(); }
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getOperator() {
		return $this->operator === null? '=' : $this->operator;
	}

	public function setOperator($operator) {
		$this->operator = $operator;
	}
	
	public function getSubqueries() {
		$r = array_merge($this->getValue()->getSubqueries(), [$this->getValue()]);
		return $r;
	}
	
	public function replaceQueryTable($old, $new) {
		
		//TODO: The fact that the composite restriction is not using query tables is off-putting
		return true; 
		
	}
	
	public function makeConnector() {
		$field     = $this->getField();
		$value     = $this->getValue();
		$of        = $this->getQuery()->getTable()->getDb()->getObjectFactory();
		$connector = $field->getConnectorQueries($this->getQuery());
		
		$last      = array_pop($connector);
		$last->setId($this->getValue()->getId());
		
		if ($field === null || $value === null) {
			throw new PrivateException('Deprecated: Composite restrictions do not receive null parameters', 2801191504);
		} 

		/**
		 * 
		 * @var MysqlPDOQuery The query
		 */
		$group = $of->restrictionGroupInstance($this->getQuery(), RestrictionGroup::TYPE_AND);

		/**
		 * The system needs to create a copy of the subordinated restrictions 
		 * to be able to syntax a proper SQL query.
		 */
		$group->add($last);
		
		/*
		 * Once we looped over the sub restrictions, we can determine whether the
		 * additional group is actually necessary. If it is, we add it to the output
		 */
		if (!$group->isEmpty()) {
			$this->getValue()->push($group);
		}
		
		return $connector; 
	}
	
	public function negate() {
		switch ($this->operator) {
			case '=': 
				return $this->operator = '<>';
			case '<>': 
				return $this->operator = '=';
			case '!=': 
				return $this->operator = '=';
		}
	}
	
	public function __clone() {
		$this->value = clone $this->value;
	}
	
}
