<?php namespace tests\phpas\integration\t0001;

use commishes\qa\runner\SeleniumTestCase;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;

class CanInvokeTheApplicationTest extends SeleniumTestCase
{
	public function testOpenRoot()
	{
		$this->driver()->get('http://web/');
		$this->screenshot('hello');
		
		$this->assertInstanceOf(
			RemoteWebElement::class,
			$this->driver()->findElement(WebDriverBy::name('username'))
		);
	}
}
