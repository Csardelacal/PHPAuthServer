<?php namespace tests\spitfire;

use PHPUnit\Framework\TestCase;
use spitfire\core\router\Router;
use function spitfire;

class URLTest extends TestCase
{
	
	private $setup = false;
	
	public function setUp() {
		\spitfire\core\Environment::get()->set('base_url', '/');
		
		if (!$this->setup) {
			#Create a route with parameters
			Router::getInstance()->request('/:a/:b', ['controller' => 'test', 'action' => ':b', 'object' => ':a']);
			
			#Create a route for a specific server with parameters
			Router::getInstance()->server(':lang.:tld.com')->request('/hello/', ['controller' => 'test', 'action' => 'a', 'object' => 'a']);
			
			#Create a redirection
			Router::getInstance()->request('/about', '/static/about');
			Router::getInstance()->request('/static/:page', ['controller' => 'content', 'action' => 'page', 'object' => ':page']);
			
			spitfire()->createRoutes();
			
			$this->setup = true;
		}
	}
	
	public function testBlankSerializer() {
		
		$url = url();
		$this->assertEquals('/', strval($url));
	}
	
	public function testBlankSerializer2() {
		$url = url('home', 'index');
		$this->assertEquals('/', strval($url));
	}
	
	public function testAnotherSerializer() {
		$url = url('account', 'test');
		$this->assertEquals('/account/test/', strval($url));
	}
	
	public function testAnotherSerializerWithParams() {
		$url = url('account', 'test', ['a' => 3]);
		$this->assertEquals('/account/test/?a=3', strval($url));
	}
	
	public function testArrayReverser() {
		$this->assertEquals('/url/my/',       strval(url('test',  'my', 'url')));
		$this->assertEquals('/test2/my/url/', strval(url('test2', 'my', 'url')));
	}
	
	public function testServerReverser() {
		$absURL = url('test', 'a', 'a')->absolute(['lang' => 'en', 'tld' => 'test']);
		$this->assertEquals('http://en.test.com/hello/', strval($absURL));
		$this->assertNotEquals('http://en.test.com/hello/', strval(url('test', 'a', 'b')->absolute(['lang' => 'en', 'tld' => 'test'])));
	}
	
	public function testServerReverser2() {
		$absURL = url('test', 'a', 'a')->absolute('test.com');
		$this->assertEquals('http://test.com/a/a/', strval($absURL));
	}
	
	public function testRedirectionReverser() {
		$urla = url('content', 'page', 'about');
		$urlb = url('content', 'page', 'me');
		
		$this->assertEquals('/static/me/', strval($urlb));
		$this->assertEquals('/about/', strval($urla));
	}
	
}