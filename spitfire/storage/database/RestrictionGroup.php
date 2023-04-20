<?php namespace spitfire\storage\database;

use InvalidArgumentException;
use spitfire\collection\Collection;
use spitfire\exceptions\PrivateException;

/**
 * A restriction group contains a set of restrictions (or restriction groups)
 * that can be used by the database to generate more complex queries.
 * 
 * This groups can be different of two different types, they can be 'OR' or 'AND',
 * changing the behavior of the group by making it more or less restrictive. This
 * OR and AND types are known from most DBMS.
 */
abstract class RestrictionGroup extends Collection
{
	const TYPE_OR  = 'OR';
	const TYPE_AND = 'AND';
	
	private $parent;
	private $type = self::TYPE_OR;
	private $negated = false;
	
	public function __construct(RestrictionGroup$parent = null, $restrictions = Array() ) {
		$this->parent = $parent;
		parent::__construct();
		parent::add(Collection::fromArray($restrictions));
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20170720
	 * @remove 20180711
	 * @param type $restriction
	 */
	public function putRestriction($restriction) {
		parent::push($restriction);
	}
	
	/**
	 * Adds a restriction to the current query. Restraining the data a field
	 * in it can contain.
	 *
	 * @todo This method does not accept logical fields as parameters
	 * @see  http://www.spitfirephp.com/wiki/index.php/Method:spitfire/storage/database/Query::addRestriction
	 *
	 * @deprecated since version 0.1-dev 20170923
	 * @remove 20180711
	 * @param string $fieldname
	 * @param mixed  $value
	 * @param string $operator
	 * @return RestrictionGroup
	 * @throws PrivateException
	 */
	public function addRestriction($fieldname, $value, $operator = '=') {
		return $this->where($fieldname, $operator, $value);
	}

	/**
	 * Adds a restriction to the current query. Restraining the data a field
	 * in it can contain.
	 *
	 * @todo This method does not accept logical fields as parameters
	 * @see  http://www.spitfirephp.com/wiki/index.php/Method:spitfire/storage/database/Query::addRestriction
	 *
	 * @param string $fieldname
	 * @param mixed  $value
	 * @param string $_
	 * @return RestrictionGroup
	 * @throws PrivateException
	 */
	public function where($fieldname, $value, $_ = null) {
		$params = func_num_args();
		$rm     = $this->getQuery()->getTable()->getDb()->getRestrictionMaker();
		
		/*
		 * Depending on how the parameters are provided, where will appropriately
		 * shuffle them to make them look correctly.
		 */
		if ($params === 3) { list($operator, $value) = [$value, $_]; }
		else               { $operator = '='; }
		
		$this->push($rm->make($this, $fieldname, $operator, $value));
		return $this;
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20170720
	 * @param type $restrictions
	 */
	public function getRestrictions() {
		return parent::toArray();
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20170720
	 * @param type $index
	 */
	public function getRestriction($index) {
		return parent::offsetGet($index);
	}
	
	/**
	 * 
	 */
	public function getCompositeRestrictions() {
		
		return $this->each(function ($r) {
			if ($r instanceof CompositeRestriction) {	return $r; }
			if ($r instanceof RestrictionGroup)     { return $r->getCompositeRestrictions(); }
			return null;
		})
		->flatten()
		->filter(function ($e) {
			return $e !== null && ($e instanceof CompositeRestriction || !$e->isEmpty());
		});
	}
	
	/**
	 * @param string $type
	 * @return RestrictionGroup
	 */
	public function group($type = self::TYPE_OR) {
		#Create the group and set the type we need
		$group = $this->getQuery()->getTable()->getDb()->getObjectFactory()->restrictionGroupInstance($this);
		$group->setType($type);
		
		#Add it to our restriction list
		return $this->push($group);
	}
	
	public function endGroup() {
		return $this->parent;
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20180420
	 * @param \spitfire\storage\database\Query $query
	 * @return $this
	 */
	public function setQuery(Query$query) {
		$this->parent = $query;
		return $this;
	}
	
	public function setParent(RestrictionGroup$query) {
		$this->parent = $query;
		
		return $this;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * As opposed to the getParent method, the getQuery method will ensure that
	 * the return is a query.
	 * 
	 * This allows the application to quickly get information about the query even
	 * if the restrictions are inside of several layers of restriction groups.
	 * 
	 * @return Query
	 */
	public function getQuery() {
		return $this->parent->getQuery();
	}
	
	public function setType($type) {
		if ($type === self::TYPE_AND || $type === self::TYPE_OR) {
			$this->type = $type;
			return $this;
		}
		else {
			throw new InvalidArgumentException("Restriction groups can only be of type AND or OR");
		}
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getSubqueries() {
		
		/*
		 * First, we extract the physical queries from the underlying queries.
		 * These queries should be executed first, to make it easy for the system
		 * to retrieve the data the query depends on.
		 */
		$_ret = Array();
		
		foreach ($this as $r) {
			$_ret = array_merge($_ret, $r->getSubqueries());
		}
		
		return $_ret;
	}
	
	public function replaceQueryTable($old, $new) {
		
		
		foreach ($this->getRestrictions() as $r) {
			$r->replaceQueryTable($old, $new);
		}
	}
	
	public function negate() {
		$this->negated = !$this->negated;
		return $this;
	}
	
	public function normalize() {
		if ($this->negated) {
			$this->flip();
		}
		
		$this
			/*
			 * We normalize the children first. This ensures that the normalization
			 * the parent performs is still correct.
			 */
			->filter(function ($e) { return $e instanceof RestrictionGroup; })
			->each(function (RestrictionGroup$e) { return $e->normalize(); })
			
			/*
			 * We remove the groups that satisfy any of the following:
			 * * They're empty
			 * * They only contain one restriction
			 * * They have the same type as the current one. Based on (A AND B) AND C == A AND B AND C
			 */
			->filter(function (RestrictionGroup$e) { return $e->getType() === $this->getType() || $e->count() < 2; })
			->each(function ($e) {
				$this->add($e->each(function ($e) { $e->setParent($this); return $e; }));
				$this->remove($e);
			});
		
		return $this;
	}
	
	/**
	 * When a restriction group is flipped, the system will change the type from
	 * AND to OR and viceversa. When doing so, all the restrictions are negated.
	 * 
	 * This means that <code>$a == $a->flip()</code> even though they have inverted
	 * types. This is specially interesting for query optimization and negation.
	 * 
	 * @return RestrictionGroup
	 */
	public function flip() {
		$this->negated = !$this->negated;
		$this->type = $this->type === self::TYPE_AND? self::TYPE_OR : self::TYPE_AND;

		foreach ($this as $restriction) {
			if ($restriction instanceof Restriction ||
			    $restriction instanceof CompositeRestriction ||
			    $restriction instanceof RestrictionGroup) { $restriction->negate(); }
		}
		
		return $this;
	}
	
	public function isMixed() {
		$found = false;
		
		foreach ($this as $r) {
			if ($r instanceof RestrictionGroup && ($r->getType() !== $this->getType() || $r->isMixed())) {
				$found = true;
			}
		}
		
		return $found;
	}
	
	/**
	 * When cloning a restriction group we need to ensure that the new restrictions
	 * are assigned to the parent, and not some other object.
	 * 
	 * TODO: This would be potentially much simpler if the collection provided a 
	 * walk method that would allow to modify the elements from within.
	 */
	public function __clone() {
		$restrictions = $this->toArray();
		
		foreach ($restrictions as &$r) { 
			$r = clone $r; 
			$r->setParent($this);
		}
		
		$this->reset()->add(Collection::fromArray($restrictions));
	}

	abstract public function __toString();
}
