<?php namespace magic3w\phpauth\tests\avatarDelete;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use magic3w\phpauth\tests\BrowserStackTestCase;

class LoginFailureTest extends BrowserStackTestCase
{
	
	public function testDelete() 
	{
		$this->do(function (RemoteWebDriver $driver) : void {
			# Searching for 'BrowserStack' on google.com
			$driver->get("http://localhost:8085");
			
			$element = $driver->findElement(WebDriverBy::name("username"));
			$element->sendKeys("nobody");
			
			$element = $driver->findElement(WebDriverBy::name("password"));
			$element->sendKeys("test");
			
			$element->submit();
			print $driver->getTitle();
			
			$result = $driver->findElement(WebDriverBy::cssSelector(".message.error"));
			$this->assertNotEmpty($result);
			$this->assertEquals("Username or password did not match", $result->getText());
			
		});
	}
}
