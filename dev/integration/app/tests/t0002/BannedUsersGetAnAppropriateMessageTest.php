<?php namespace tests\phpas\integration\t0002;

use commishes\qa\runner\SeleniumTestCase;
use Facebook\WebDriver\WebDriverBy;

class BannedUsersGetAnAppropriateMessageTest extends SeleniumTestCase
{
	public function setUp() : void
	{
		parent::setUp();
		
		# Load a banned user so we can check if they can log into the app
		$this->loadGlobalFixture('user.banned.delete.xml');
		$this->loadGlobalFixture('user.banned.xml');
	}
	
	public function testLogIntoBannedAccount()
	{
		$this->driver()->get('http://web/');
		$this->screenshot('01-hello');
		
		$this->driver()->findElement(WebDriverBy::name('username'))->sendKeys('banned');
		$this->driver()->findElement(WebDriverBy::name('password'))->sendKeys('testtest');
		$this->driver()->findElement(WebDriverBy::id('login'))->click();
		
		$this->screenshot('02-message');
		
		$this->assertStringContainsStringIgnoringCase(
			'Test suspension',
			$this->driver()->findElement(WebDriverBy::tagName('body'))->getText()
		);
	}
}
