<?php namespace magic3w\phpauth\tests;

use Closure;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use BrowserStack\Local;

abstract class BrowserStackTestCase extends TestCase
{
	private static $driver;
	
	public function setUp() : void
	{
		system('php console database reset');
		system('php console database init test@test.com test testtest');
		
		$username = getenv("BROWSERSTACK_USERNAME");
		$accessKey = getenv("BROWSERSTACK_ACCESS_KEY");
		$config = json_decode(getenv('BROWSERSTACK_CAPS'), true);
			
		self::$driver = self::$driver?: RemoteWebDriver::create(
			"https://" . $username . ":" . $accessKey . "@hub-cloud.browserstack.com/wd/hub",
			$config
		);
	}
	
	public function tearDown() : void
	{
		//self::$driver->quit();
	}
	
	/**
	 * 
	 * @param Closure(RemoteWebDriver):void $action
	 */
	public function do(Closure $action) 
	{
		$action(self::$driver);
	}
	
	
	public static function assertEquals($expected, $actual, string $message = ''): void
	{
		parent::assertEquals($expected, $actual, $message);
		
		# Setting the status of test as 'passed' or 'failed' based on the condition; if title of the web page starts with 'BrowserStack'
		if ($expected === $actual) {
			self::$driver->executeScript('browserstack_executor: {"action": "setSessionStatus", "arguments": {"status":"passed", "reason": "Assertion passed"}}' );
		}
		else {
			self::$driver->executeScript('browserstack_executor: {"action": "setSessionStatus", "arguments": {"status":"failed", "reason": "Failed to assert that strings match"}}');
		}
	}
	
}
