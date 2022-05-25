<?php namespace magic3w\phpauth\tests;

use Closure;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use BrowserStack\Local;

abstract class BrowserStackTestCase extends TestCase
{
	private static $driver;
	
	private $caps = [
		array(
			"os" => "Windows",
			"os_version" => "11",
			"browser" => "chrome",
			"browser_version" => "101.0",
			"build" => "browserstack-build-1",
			"name" => "Parallel test 1",
			"browserstack.local" => "true"
		),
		array(
		"os" => "Windows",
		"os_version" => "10",
		"browser" => "firefox",
		"browser_version" => "latest",
		"build" => "browserstack-build-1",
		"name" => "Parallel test 2",
		"browserstack.local" => "true"
		),
		array(
		"browserName" => "android",
		"realMobile" => "true",
		"device" => "Samsung Galaxy S20",
		"os_version" => "10.0",
		"build" => "browserstack-build-1",
		"name" => "Parallel test 3",
		"browserstack.local" => "true"
		)
	];
	
	/**
	 * 
	 * @param Closure(RemoteWebDriver):void $action
	 */
	public function do(Closure $action) 
	{
		foreach ( $this->caps as $cap ) {
			# Creates an instance of Local
			$bs_local = new Local();
			
			# You can also set an environment variable - "BROWSERSTACK_ACCESS_KEY".
			$bs_local_args = array("key" => "####");
			
			# Starts the Local instance with the required arguments
			$bs_local->start($bs_local_args);
			
			# Check if BrowserStack local instance is running
			echo $bs_local->isRunning();
			
			# Your test code goes here, from creating the driver instance till the end, i.e. $bs_local->stop()
			self::$driver = $driver = RemoteWebDriver::create(
				"####",
				$cap
			);
			
			$action($driver);
			
			$driver->quit();
			
			# Stop the Local instance
			$bs_local->stop();
		}
		
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
