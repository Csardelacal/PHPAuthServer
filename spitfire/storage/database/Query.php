<?php namespace spitfire\storage\database;

use Exception;
use spitfire\exceptions\PrivateException;
use spitfire\exceptions\PublicException;
use spitfire\Model;
use spitfire\model\Field as LogicalField;

abstract class Query extends RestrictionGroup
{
	/** 
	 * The result for the query. Currently, this is attached to the query - this 
	 * means that whenever the query is "re-executed" the result is overwritten 
	 * and could potentially damage the resultset.
	 * 
	 * This would require a significant change in the API, since it requires the
	 * app to not loop fetch() calls over the query but actually retrieve the 
	 * result element and loop over that.
	 * 
	 * @todo This should be removed in favor of an actual collector for the results
	 * @deprecated since version 0.1-dev 20170414
	 * @var \spitfire\storage\database\ResultSetInterface|null
	 */
	protected $result;
	
	/** 
	 * The table this query is retrieving data from. This table is wrapped inside
	 * a QueryTable object to ensure that the table can refer back to the query
	 * when needed.
	 * 
	 * @var QueryTable
	 */
	protected $table;
	
	/**
	 *
	 * @todo We should introduce a class that allows these queries to sort by multiple,
	 * and even layered (as in, in other queries) columns.
	 * @var string
	 */
	protected $order;
	protected $groupby = null;

	/** @param Table $table */
	public function __construct($table) {
		$this->table = $table->getDb()->getObjectFactory()->queryTableInstance($table);
		
		#Initialize the parent
		parent::__construct(null, Array());
		$this->setType(RestrictionGroup::TYPE_AND);
	}
	
	/**
	 * 
	 * @param string $fieldname
	 * @param string $value
	 * @param string $operator
	 * @deprecated since version 0.1-dev 20170414
	 * @return Query
	 */
	public function addRestriction($fieldname, $value, $operator = '=') {
		$this->result = null;
		return parent::addRestriction($fieldname, $value, $operator);
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20160406
	 * @remove 20180711
	 * @param boolean $aliased
	 */
	public function setAliased($aliased) {
		$this->table->setAliased($aliased);
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20160406
	 * @remove 20180711
	 * @return boolean
	 */
	public function getAliased() {
		return $this->table->isAliased();
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20160406
	 * @remove 20180711
	 * @return int
	 */
	public function getId() {
		return $this->table->getId();
	}
	
	/**
	 * 
	 * @param int $id
	 * @deprecated since version 0.1-dev 20160406
	 * @remove 20180711
	 * @return \spitfire\storage\database\Query
	 */
	public function setId($id) {
		$this->table->setId($id);
		return $this;
	}
	
	/**
	 * Since a query is the top Level of any group we can no longer climb up the 
	 * ladder.
	 * 
	 * @throws PrivateException
	 */
	public function endGroup() {
		throw new PrivateException('Called endGroup on a query', 1604031547);
	}
	
	public function getQuery() {
		return $this;
	}

	/**
	 * Sets the amount of results returned by the query.
	 *
	 * @deprecated since version 0.1-dev 20180509
	 * @remove 20180911
	 * @param int $amt
	 *
	 * @return self
	 */
	public function setResultsPerPage($amt) {
		trigger_error('Deprecated Query::setResultsPerPage() invoked', E_USER_DEPRECATED);
		throw new PrivateException('Pagination has been moved. Please refer to the documentation', 1805111238);
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20180509
	 * @remove 20180911
	 * @return int The amount of results the query returns when executed.
	 */
	public function getResultsPerPage() {
		return false;
	}
	
	/**
	 * @deprecated since version 0.1-dev 20170414
	 * @param int $page The page of results currently displayed.
	 * @removed 20180511
	 * @return boolean Returns if the page se is valid.
	 */
	public function setPage ($page) {
		trigger_error('Deprecated Query::setPage() invoked', E_USER_DEPRECATED);
		throw new PrivateException('Pagination has been moved. Please refer to the documentation', 1805111238);
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20170414
	 * @remove 20180911
	 * @return int
	 */
	public function getPage() {
		trigger_error('Deprecated Query::getPage() invoked', E_USER_DEPRECATED);
		return false;
	}
	
	//@TODO: Add a decent way to sorting fields that doesn't resort to this awful thing.
	public function setOrder ($field, $mode) {
		try {
			$this->order['field'] = $this->table->getTable()->getLayout()->getField($field);
		} catch (Exception $ex) {
			$physical = $this->table->getTable()->getModel()->getField($field)->getPhysical();
			$this->order['field'] = reset($physical);
		}
		
		$this->order['mode'] = $mode;
		return $this;
	}
	
	/**
	 * Returns a record from a database that matches the query we sent.
	 * 
	 * @deprecated since version 0.1-dev 20170414
	 * @return Model
	 */
	public function fetch() {
		if (!$this->result) { $this->query(); }
		
		$data = $this->result->fetch();
		return $data;
	}
	
	/**
	 * This method returns a single record from a query, allowing your application
	 * to quickly query the database for a record it needs.
	 * 
	 * The onEmpty parameter allows you to inject a callback in the event the query
	 * returns no value. You can provide a callable or an Exception (which will 
	 * be thrown), reducing the amount of if/else in your controllers.
	 * 
	 * The code
	 * <code>
	 * $user = db()->table('user')->get('_id', $uid)->first();
	 * if (!$user) { throw new PublicException('No user found', 404); }
	 * </code>
	 * 
	 * Can therefore be condensed into:
	 * <code> 
	 * $user = db()->table('user')->get('_id', $uid)->first(new PublicException('No user found', 404));
	 * </code>
	 * 
	 * If you wish to further condense this, you can just provide onEmpty as `true`
	 * which will then cause the system to raise a `PublicException` with a standard
	 * 'No %tablename found' and a 404 code. Causing this code to boil down to:
	 * 
	 * <code> 
	 * $user = db()->table('user')->get('_id', $uid)->first(true);
	 * </code>
	 * 
	 * While this seems more unwieldy at first, the code gains a lot of clarity 
	 * when written like this
	 * 
	 * @param callable|\Exception|true|null $onEmpty
	 * @return Model|null
	 */
	public function first($onEmpty = null) {
		$res = $this->execute(null, 0, 1)->fetch();
		
		if (!$res && $onEmpty) {
			if ($onEmpty instanceof \Exception) { throw $onEmpty;}
			elseif(is_callable($onEmpty))       { return $onEmpty(); }
			elseif($onEmpty === true)           { throw new PublicException(sprintf('No %s found', $this->getTable()->getSchema()->getName()), 404); }
		}
		
		return $res;
	}
	
	/**
	 * This method returns a finite amount of items matching the parameters from 
	 * the database. This method always returns a collection, even if the result
	 * is empty (no records matched the query)
	 * 
	 * @param int $skip
	 * @param int $amt
	 * @return \spitfire\collection\Collection
	 */
	public function range($skip = 0, $amt = 1) {
		if ($skip < 0 || $amt < 1) {
			throw new \InvalidArgumentException('Query received invalid arguments', 1805091416);
		}
		
		return $this->execute(null, $skip, $amt)->fetchAll();
	}
	
	/**
	 * 
	 * @return \spitfire\collection\Collection
	 */
	public function all() {
		return $this->execute()->fetchAll();
	}

	/**
	 * Returns all the records that the query matched. This method wraps the records
	 * inside a collection object to make them easier to access.
	 * 
	 * @deprecated since version 0.1-dev 20180509
	 * @return \spitfire\collection\Collection[]
	 */
	public function fetchAll() {
		if (!$this->result) { $this->query(); }
		return $this->result->fetchAll();
	}
	
	/**
	 * 
	 * @deprecated since version 0.1-dev 20180509 We do no longer provide the option to not return the result
	 * @param mixed[] $fields
	 * @param bool    $returnresult
	 * @return type
	 */
	protected function query($fields = null, $returnresult = false) {
		$result = $this->execute($fields);
		
		if ($returnresult) { return $result; }
		else               { return $this->result = $result; }
	}

	/**
	 * Deletes the records matching this query. This will not retrieve the data and
	 * therefore is more efficient than fetching and later deleting.
	 * 
	 * @todo Currently does not support deleting of complex queries.
	 * @return int Number of affected records
	 */
	public abstract function delete();
	
	/**
	 * Counts the number of records a query would return. If there is a grouping
	 * defined it will count the number of records each group would return.
	 * 
	 * @todo This method's behavior is extremely inconsistent
	 * @return int
	 */
	public function count() : int {
		//This is a temporary fix that will only count distinct values in complex
		//queries.
		$query = $this->query(Array('COUNT(DISTINCT ' . $this->table->getTable()->getPrimaryKey()->getFields()->join(', ') . ')'), true)->fetchArray();
		$count = reset($query);
		return (int)$count;
		
	}
	
	/**
	 * Defines a column or array of columns the system will be using to group 
	 * data when generating aggregates.
	 * 
	 * @todo When adding aggregation, the system should automatically use the aggregation for extraction
	 * @todo Currently the system only supports grouping and not aggregation, this is a bit of a strange situation that needs resolution
	 * 
	 * @param LogicalField|FieLogicalFieldld[]|null $column
	 * @return Query Description
	 */
	public function aggregateBy($column) {
		if (is_array($column))   { $this->groupby = $column; }
		elseif($column === null) { $this->groupby = null; }
		else                     { $this->groupby = Array($column); }
		
		return $this;
	}
	
	
	/**
	 * Creates the execution plan for this query. This is an array of queries that
	 * aid relational DBMSs' drivers when generating SQL for the database.
	 * 
	 * This basically generate the connecting queries between the tables and injects
	 * your restrictions in between so the system egenrates logical routes that 
	 * will be understood by the relational DB.
	 * 
	 * @deprecated since version 0.1-dev 20180510
	 * @todo Move somewhere else. This only pertains to relational DBMS systems
	 * @return Query[]
	 */
	public function makeExecutionPlan() {
		$_ret = $this->getPhysicalSubqueries();
		array_push($_ret, $this);
		return $_ret;
	}
	
	public function getOrder() {
		return $this->order;
	}
	
	/**
	 * Returns the current 'query table'. This is an object that allows the query
	 * to alias it's table if needed.
	 * 
	 * @return QueryTable
	 */
	public function getQueryTable() {
		return $this->table;
	}
	
	public function cloneQueryTable() {
		$table = clone $this->table;
		$table->newId();
		
		$this->replaceQueryTable($this->table, $table);
		
		$this->table = $table;
		return $this->table;
	}
	
	/**
	 * Returns the actual table this query is searching on. 
	 * 
	 * @return Table
	 */
	public function getTable() {
		return $this->table->getTable();
	}
	
	public function __toString() {
		return $this->getTable()->getLayout()->getTableName() . implode(',', $this->toArray());
	}
	
	/**
	 * 
	 * @return ResultSetInterface
	 */
	public abstract function execute($fields = null, $offset = null, $max = null);
}
