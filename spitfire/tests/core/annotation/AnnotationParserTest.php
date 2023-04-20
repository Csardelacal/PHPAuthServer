<?php namespace tests\core\annotation;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use spitfire\core\annotations\AnnotationParser;


class AnnotationParserTest extends TestCase
{
	
	public function testParser() {
		
		$string = "/**\n * @param test A \n * @param test B \n */";
		$parser = new AnnotationParser();
		
		$annotations = $parser->parse($string);
		
		#Test the element is actually there
		$this->assertArrayHasKey('param', $annotations);
		
		#Ensure it did parse the same annotation twice and properly structure the array
		$this->assertCount(1, $annotations);
		$this->assertCount(2, $annotations['param']);
		
		#Test the value is what we expect
		$this->assertEquals('test A',    $annotations['param'][0]);
		$this->assertEquals('test B',    $annotations['param'][1]);
		
	}
	
	/**
	 * 
	 * @sometest test A
	 * @sometest test B
	 */
	public function testParserReflection() {
		
		$parser = new AnnotationParser();
		$reflec = new ReflectionClass($this);
		
		$annotations = $parser->parse($reflec->getMethod('testParserReflection'));
		
		#Test the element is actually there
		$this->assertArrayHasKey('sometest', $annotations);
		
		#Ensure it did parse the same annotation twice and properly structure the array
		$this->assertCount(1, $annotations);
		$this->assertCount(2, $annotations['sometest']);
		
		#Test the value is what we expect
		$this->assertEquals('test A',    $annotations['sometest'][0]);
		$this->assertEquals('test B',    $annotations['sometest'][1]);
	}
}
