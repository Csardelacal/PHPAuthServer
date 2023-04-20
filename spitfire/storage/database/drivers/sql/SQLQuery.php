<?php namespace spitfire\storage\database\drivers\sql;

use spitfire\collection\Collection;
use spitfire\exceptions\PrivateException;
use spitfire\storage\database\drivers\mysqlpdo\CompositeRestriction;
use spitfire\storage\database\Query;
use spitfire\storage\database\RestrictionGroup;

abstract class SQLQuery extends Query
{
	
	/**
	 * The redirection object is required only when assembling queries. Sometimes,
	 * a query has unmet dependencies that it cannot satisfy. In this case, it's 
	 * gonna copy itself and move all of it's restrictions to the new query.
	 * 
	 * This means that when serializing the query, the composite restriction should
	 * not print <code>old.primary IS NOT NULL</code> but <code>new.primary IS NOT NULL</code>.
	 * 
	 * But! When the parent injects the restrictions to connect the queries with 
	 * the parent, the old query must answer the call and assimilate them.
	 * 
	 * To achieve this behavior, I found it reasonable that the query introduces 
	 * a redirection property. When a composite restriction finds this, it will
	 * automatically use the target of the redirection.
	 * 
	 * NOTE: Composite queries do not follow multiple redirections.
	 *
	 * @var SQLQuery|null
	 */
	private $redirection = null;
	
	/**
	 * It retrieves all the subqueries that are needed to be executed on a relational
	 * DB before the main query.
	 * 
	 * We could have used a single method with a flag, but this way seems cleaner
	 * and more hassle free than otherwise.
	 * 
	 * @return Query[]
	 */
	public function makeExecutionPlan() {
		
		/*
		 * Inject the current query into the array. The data for this query needs
		 * to be retrieved last.
		 */
		$copy = clone $this;
		$_ret = $copy->physicalize(true);
		
		$copy->denormalize(true);
		
		foreach ($_ret as $q) {
		$q->normalize();
		}
		
		return $_ret;
	}
	
	public function physicalize($top = false) {
		
		$copy = $this;
		$_ret = [$this];
		
		$composite = $copy->getCompositeRestrictions();
		
		foreach ($composite as $r) {
			
			$q = $r->getValue();
			$p = $q->physicalize();
			$c = $r->makeConnector();
			$_ret = array_merge($_ret, $c, $p);
		}
		
		if (!$top && $copy->isMixed() && !$composite->isEmpty()) {
			
			$clone = clone $copy;
			$of    = $copy->getTable()->getDb()->getObjectFactory();
			
			$clone->cloneQueryTable();
			$group = $of->restrictionGroupInstance($clone);
			
			foreach ($copy->getTable()->getPrimaryKey()->getFields() as $field) {
				$group->where(
					$of->queryFieldInstance($copy->getQueryTable(), $field),
					$of->queryFieldInstance($clone->getQueryTable(), $field)
				);
			}
			
			$copy->reset();
			$copy->setRedirection($clone);
			$clone->push($group);
			$_ret[] = $clone;
		}
		
		return $_ret;
	}
	
	/**
	 * 
	 * @todo This method could be much simpler with an import function in rGroups which take all the children
	 * @param type $root
	 * @return Collection
	 * @throws PrivateException
	 */
	public function denormalize($root = false) {
		if (!$root && $this->isMixed() && !$this->getCompositeRestrictions()->isEmpty()) {
			throw new PrivateException('Impossible condition satisfied. This is a bug.', 1804292159);
		}
		
		$_ret      = new Collection();
		
		$composite = $this->getCompositeRestrictions();
		$of        = $this->getQuery()->getTable()->getDb()->getObjectFactory();
		
		/*
		 * Loop over the composite restrictions inside this query. This allows the
		 * system to extract the conditions that need to assimilated at the end of
		 * a query.
		 * 
		 * Please note that this only can be achieved because the driver has 
		 * previously physicalized the queries and prepared them in a manner that
		 * allows for their denormalization (deferred the mixed ones).
		 */
		foreach ($composite as /*@var $r CompositeRestriction*/$r) {
			
			$sg = $of->restrictionGroupInstance($r->getParent(), RestrictionGroup::TYPE_AND);
			$d  = $r->getValue()->denormalize();
			
			/*
			 * Once the subquery has been denormalized, we enter to retrieve the 
			 * previously denormalized blocks. To do so, we loop over the collection
			 * containing the references, remove the group from their actual location,
			 * put it in their new home and append that to the $sg variable.
			 */
			foreach ($d as $v) {
				$group = $of->restrictionGroupInstance($this, RestrictionGroup::TYPE_AND);
				$group->push($v);
				$v->getParent()->remove($v);
				$v->setParent($group);
				$sg->push($group->setParent($sg));
			}
			
			$sg->push($r);
			
			$r->getParent()->remove($r)->push($sg);
			$r->setParent($sg);
			$_ret->push($sg);
		}
		
		return $_ret;
	}
	
	/**
	 * 
	 * @return SQLQuery|null
	 */
	public function getRedirection() {
		return $this->redirection;
	}
	
	/**
	 * This is a driver specific method. If you're not exactly sure what a query
	 * redirection is, please avoid using this method.
	 * 
	 * @param SQLQuery|null $redirection
	 * @return $this
	 */
	public function setRedirection($redirection = null) {
		$this->redirection = $redirection;
		return $this;
	}
	
}
