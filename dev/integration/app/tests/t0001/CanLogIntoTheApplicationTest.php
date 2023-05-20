<?php namespace tests\phpas\integration\t0001;

use commishes\qa\runner\SeleniumTestCase;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class CanLogIntoTheApplicationTest extends SeleniumTestCase
{
	
	public function setUp() : void
	{
		parent::setUp();
		
		#Not great, but Spitfire currently does not offer other ways of executing migrations
		file_get_contents('http://web/');
		
		# Load a 
		$this->loadGlobalFixture('user.admin.delete.xml');
		$this->loadGlobalFixture('user.admin.xml');
	}
	
	public function testSucessfulLogin()
	{
		$this->driver()->get('http://web/');
		$this->screenshot('01-login');
		
		$this->assertInstanceOf(
			RemoteWebElement::class,
			$this->driver()->findElement(WebDriverBy::name('username'))
		);
		
		$this->driver()->findElement(WebDriverBy::name('username'))->sendKeys('admin');
		$this->driver()->findElement(WebDriverBy::name('password'))->sendKeys('testtest');
		$this->driver()->findElement(WebDriverBy::id('login'))->click();
		
		$this->screenshot('02-profile');
		
		$this->assertInstanceOf(
			RemoteWebElement::class,
			$this->driver()->findElement(WebDriverBy::partialLinkText('Edit profile'))
		);
	}
}
