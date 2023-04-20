<?php namespace tests\spitfire\core\autoload;

use PHPUnit\Framework\TestCase;
use spitfire\autoload\NamespacedClassLocator;

class NamespacedclasslocatorTest extends TestCase
{
	
	private $locator;
	
	public function testNameSpacedClassLocator() {
		$this->locator = new NamespacedClassLocator('spitfire', SPITFIRE_BASEDIR);
		
		$this->assertEquals(false, $this->locator->getFilenameFor('\spitfire\somenamespace\someRandomClassThatDoesNotExist'), 
				'If a class does not exist the test should fail.');
		
		$this->assertEquals(false, $this->locator->getFilenameFor('\controllers\MyController'), 
				'A class locator should not match wrong namespaces.');
		
		$this->assertNotEquals(false, $this->locator->getFilenameFor('spitfire\core\router\Router'), 
				'The class Locator in spitfire should find the router. No leading slash.');
		
		$this->assertNotEquals(false, $this->locator->getFilenameFor('\spitfire\core\router\Router'), 
				'The class Locator in spitfire should find the router.');
	}
	
}