<?php namespace commishes\qa\runner\attributes;

use Attribute;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use commishes\qa\runner\SeleniumUserStory;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class Continues
{
	/**
	 * @var class-string
	 */
	private string $previous;
	
	/**
	 * @param class-string $previous
	 */
	public function __construct(string $previous)
	{
		$this->previous = $previous;
	}
	
	public function tell(RemoteWebDriver $driver)
	{
		/**
		 * 
		 * @var ReflectionClass<SeleniumUserStory>
		 */
		$reflection = new ReflectionClass($this->previous);
		assert($reflection->isSubclassOf(SeleniumUserStory::class));
		
		$instance = $reflection->newInstance($driver);
		$instance->tell();
	}
}