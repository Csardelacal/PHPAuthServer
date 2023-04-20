<?php namespace spitfire\core;

use ArrayAccess;
use BadMethodCallException;
use spitfire\exceptions\OutOfBoundsException;
use spitfire\exceptions\OutOfRangeException;

/**
 * The collection class is intended to supercede the array and provide additional
 * functionality and ease of use to the programmer.
 */
class Collection implements ArrayAccess, CollectionInterface
{
	private $items;
	
	/**
	 * The collection element allows to extend array functionality to provide
	 * programmers with simple methods to aggregate the data in the array.
	 * 
	 * @param Collection|mixed $e
	 */
	public function __construct($e = null) {
		if ($e === null)                  {	$this->items = []; }
		elseif ($e instanceof Collection) { $this->items = $e->toArray(); }
		elseif (is_array($e))             { $this->items = $e; }
		else                              { $this->items = [$e]; }
	}
	
	/**
	 * This method iterates over the elements of the array and applies a provided
	 * callback to each of them. The value your function returns if placed in the
	 * array.
	 * 
	 * @param callable|array $callable
	 * @return Collection
	 * @throws BadMethodCallException
	 */
	public function each($callable) {
		
		/*
		 * If the callback provided is not a valid callable then the function cannot
		 * properly continue.
		 */
		if (!is_callable($callable)) { 
			throw new BadMethodCallException('Invalid callable provided to collection::each()', 1703221329); 
		}
		
		return Collection::fromArray(array_map($callable, $this->items));
	}
	
	/**
	 * Reduces the array to a single value using a callback function.
	 * 
	 * @param callable $callback
	 * @param mixed    $initial
	 * @return mixed
	 */
	public function reduce($callback, $initial = null) {
		return array_reduce($this->items, $callback, $initial);
	}
	
	public function flatten() {
		$_ret  = new self();
		
		foreach ($this->items as $item) {
			if ($item instanceof Collection) { $_ret->add($item->flatten()); }
			elseif (is_array($item))         { $c = new self($item); $_ret->add($c->flatten()); }
			else { $_ret->push($item); }
		}
		
		return $_ret;
	}
	
	/**
	 * This function checks whether a collection contains only elements with a 
	 * given type. This function also accepts base types.
	 * 
	 * Following base types are accepted:
	 * 
	 * <ul>
	 * <li>int</li><li>float</li>
	 * <li>number</li><li>string</li>
	 * <li>array</li>
	 * <ul>
	 * 
	 * @param string $type Base type or class name to check.
	 * @return boolean
	 */
	public function containsOnly($type) {
		switch($type) {
			case 'int'   : return $this->reduce(function ($p, $c) { return $p && is_int($c); }, true);
			case 'float' : return $this->reduce(function ($p, $c) { return $p && is_float($c); }, true);
			case 'number': return $this->reduce(function ($p, $c) { return $p && is_numeric($c); }, true);
			case 'string': return $this->reduce(function ($p, $c) { return $p && is_string($c); }, true);
			case 'array' : return $this->reduce(function ($p, $c) { return $p && is_array($c); }, true);
			default      : return $this->reduce(function ($p, $c) use ($type) { return $p && is_a($c, $type); }, true);
		}
	}
	
	/**
	 * Reports whether the collection is empty.
	 * 
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->items);
	}
	
	public function has($idx) {
		return isset($this->items[$idx]);
	}
	
	public function contains($e) {
		return array_search($e, $this->items, true);
	}
	
	/**
	 * Filters the collection using a callback. This allows a collection to shed
	 * values that are not useful to the programmer.
	 * 
	 * Please note that this will return a copy of the collection and the original
	 * collection will remain unmodified.
	 * 
	 * @param callable $callback
	 * @return \spitfire\collection\Collection
	 */
	public function filter($callback = null) {
		#If there was no callback defined, then we filter the array without params
		if ($callback === null) { return Collection::fromArray(array_filter($this->items)); }
		
		#Otherwise we use the callback parameter to filter the array
		return Collection::fromArray(array_filter($this->items, $callback));
	}
	
	/**
	 * Removes all duplicates from the collection.
	 * 
	 * @return \spitfire\collection\Collection
	 */
	public function unique() {
		return Collection::fromArray(array_unique($this->items));
	}
	
	/**
	 * Counts the number of elements inside the collection.
	 * 
	 * @return int
	 */
	public function count() {
		return count($this->items);
	}
	
	/**
	 * Adds up the elements in the collection. Please note that this method will
	 * double check to see if all the provided elements are actually numeric and
	 * can be added together.
	 * 
	 * @return int|float
	 * @throws BadMethodCallException
	 */
	public function sum() {
		if ($this->isEmpty())               { throw new BadMethodCallException('Collection is empty'); }
		if (!$this->containsOnly('number')) { throw new BadMethodCallException('Collection does contain non-numeric types'); }
		
		return array_sum($this->items);
	}
	
	public function sort($callback = null) {
		$copy = $this->items;
		
		if (!$callback) { sort($this->items); }
		else            { usort($this->items, $callback); }
		
		return Collection::fromArray($copy);
	}
	
	/**
	 * Returns the average value of the elements inside the collection.
	 * 
	 * @throws BadMethodCallException If the collection contains non-numeric values
	 * @return int|float
	 */
	public function avg() {
		return $this->sum() / $this->count();
	}
	
	public function join($glue) {
		return implode($glue, $this->items);
	}
	
	/**
	 * Extracts a certain key from every element in the collection. This requires
	 * every element in the collection to be either an object or an array.
	 * 
	 * The method does not accept values that are neither array nor object, but 
	 * will return null if the key is undefined in the array or object being used.
	 * 
	 * @param mixed $key
	 */
	public function extract($key) {
		return Collection::fromArray(array_map(function ($e) use ($key) {
			if (is_array($e))  { return isset($e[$key])? $e[$key] : null; }
			if (is_object($e)) { return isset($e->$key)? $e->$key : null; }
			
			throw new OutOfBoundsException('Collection::extract requires array to contain only arrays and objects');
		}, $this->items));
	}
	
	public function push($element) {
		$this->items[] = $element;
		return $element;
	}
	
	public function add($elements) {
		if ($elements instanceof Collection) { $elements = $elements->toArray(); }
		
		$this->items = array_merge($this->items, $elements);
		return $this;
	}
	
	public function groupBy($callable) {
		$groups = new self();
		
		$this->each(function ($e) use ($groups, $callable) {
			$key = $callable($e);
			
			if (!isset($groups[$key])) {
				$groups[$key] = new self();
			}
			
			$groups[$key]->push($e);
		});
		
		return $groups;
	}
	
	public function remove($element) {
		$i = array_search($element, $this->items, true);
		if ($i === false) { throw new OutOfRangeException('Not found', 1804292224); }
		
		unset($this->items[$i]);
		return $this;
	}
	
	public function reset() {
		$this->items = [];
		return $this;
	}
	
	public function reverse() {
		return Collection::fromArray(array_reverse($this->items));
	}
	
	public function current() {
		return current($this->items);
	}
	
	public function key() {
		return key($this->items);
	}
	
	public function next() {
		return next($this->items);
	}
	
	public function offsetExists($offset) : bool {
		return array_key_exists($offset, $this->items);
	}
	
	public function offsetGet($offset) {
		if (!array_key_exists($offset, $this->items)) {
			throw new OutOfRangeException('Undefined index: ' . $offset, 1703221322);
		}
		
		return $this->items[$offset];
	}
	
	public function offsetSet($offset, $value) : void {
		$this->items[$offset] = $value;
	}
	
	public function offsetUnset($offset) {
		unset($this->items[$offset]);
	}
	
	public function rewind() {
		return reset($this->items);
	}
	
	public function last() {
		if (!isset($this->items)) { throw new \spitfire\exceptions\PrivateException('Collection error', 1709042046); }
		return end($this->items);
	}

	public function shift() {
		return array_shift($this->items);
	}
	
	/**
	 * Indicates whether the current element in the Iterator is valid. To achieve
	 * this we use the key() function in PHP which will return the key the array
	 * is currently forwarded to or (which is interesting to us) NULL in the event
	 * that the array has been forwarded past it's end.
	 * 
	 * @see key
	 * @return boolean
	 */
	public function valid() {
		return null !== key($this->items);
	}
	
	/**
	 * Returns the items contained by this Collection. This method may only work
	 * if the data the collection is managing is actually a defined set and not a
	 * pointer or something similar.
	 * 
	 * @return mixed[]
	 */
	public function toArray() {
		return $this->items;
	}
	
	public function __isset($name) {
		return isset($this->items[$name]);
	}

}
