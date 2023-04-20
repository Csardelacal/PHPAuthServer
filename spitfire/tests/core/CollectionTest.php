<?php namespace tests\spitfire\core;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use spitfire\collection\Collection;

class CollectionTest extends TestCase
{
	
	/**
	 * 
	 * @covers \spitfire\collection\Collection::containsOnly
	 */
	public function testContainsOnly() {
		$c1 = Collection::fromArray([1, 2, 3, 4, 5, 6]);
		$c2 = Collection::fromArray(['a', 'b', 'c']);
		$c3 = Collection::fromArray([[], []]);
		$c4 = Collection::fromArray([$c1, $c2]);
		$c5 = Collection::fromArray([1.1, 2.2, 3.3, 4.4, 5.5, 6.6]);
		$c6 = Collection::fromArray([1.1, 2.2, 3.3, 4.4, 5.5, 6]);
		$c7 = Collection::fromArray(['1', '2', '3']);
		
		$this->assertEquals(true, $c1->containsOnly('int'));
		$this->assertEquals(true, $c1->containsOnly('number'));
		
		$this->assertEquals(true, $c2->containsOnly('string'));
		
		$this->assertEquals(true, $c3->containsOnly('array'));
		$this->assertEquals(false, $c3->containsOnly(Collection::class));
		
		$this->assertEquals(true, $c4->containsOnly(Collection::class));
		$this->assertEquals(false, $c4->containsOnly('array'));
		
		$this->assertEquals(true, $c5->containsOnly('float'));
		$this->assertEquals(true, $c5->containsOnly('number'));
		
		$this->assertEquals(false, $c6->containsOnly('float'));
		$this->assertEquals(true, $c6->containsOnly('number'));
		
		$this->assertEquals(true, $c7->containsOnly('string'));
		$this->assertEquals(true, $c7->containsOnly('number'));
	}
	
	/**
	 * 
	 * @covers \spitfire\collection\Collection::avg
	 */
	public function testAverage() {
		
		$collection = Collection::fromArray([1, 2, 3]);
		$this->assertEquals($collection->avg(), 2, 'Average of 1, 2 and 3 is 2');
		
	}
	
	/**
	 * 
	 * @covers \spitfire\collection\Collection::avg
	 * @expectedException BadMethodCallException
	 */
	public function testAverage2() {
		$collection = Collection::fromArray([]);
		$collection->avg();
	}
	
	/**
	 * 
	 * @covers \spitfire\collection\Collection::avg
	 * @expectedException BadMethodCallException
	 */
	public function testAverage3() {
		$collection = Collection::fromArray(['a', 'b', 'c']);
		$collection->avg();
	}
	
	/**
	 * 
	 * @covers \spitfire\collection\Collection::each
	 */
	public function testExtraction() {
		$collection = Collection::fromArray([['a' => 1, 'b' => 2], ['a' => 'c', 'b' => 4]]);
		$result     = $collection->each(function ($e) { return $e['b']; })->avg();
		
		$this->assertEquals($result, 3, 'The average value of 2 and 4 is expected to be 3');
	}
	
	public function testExtract() {
		$collection = Collection::fromArray([['a' => 1, 'b' => 2], ['a' => 'c', 'b' => 4]]);
		$result     = $collection->extract('b')->avg();
		
		$this->assertEquals($result, 3, 'The average value of 2 and 4 is expected to be 3');
	}
	
	/**
	 * Tests whether the filtering algorithm of a collection works properly.
	 */
	public function testFilter() {
		$collection = Collection::fromArray([0, 1, 0, 2, 0, 3]);
		
		$this->assertInstanceOf(Collection::class, $collection->filter());
		$this->assertEquals(3, $collection->filter()->count());
		$this->assertEquals(1, $collection->filter(function ($e) { return $e === 1; })->shift());
	}
	
	public function testIsset() {
		$collection = Collection::fromArray([0, 1, null, false, true]);
		
		$this->assertEquals(true, isset($collection[0]));
		$this->assertEquals(true, isset($collection->{0}));
		
		$this->assertEquals(true, isset($collection[3]));
		$this->assertEquals(true, isset($collection->{3}));
		
		$this->assertEquals(true, isset($collection[4]));
		$this->assertEquals(true, isset($collection->{4}));
		
		$this->assertEquals(false, isset($collection['a']));
		$this->assertEquals(false, isset($collection->a));
	}
	
	
}
