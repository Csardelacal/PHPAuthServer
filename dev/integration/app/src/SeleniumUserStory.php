<?php namespace commishes\qa\runner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use commishes\qa\runner\attributes\Continues;
use ReflectionClass;
use ReflectionMethod;
use spitfire\provider\Container;

abstract class SeleniumUserStory
{
	
	protected $selenium;
	
	private $hooks;
	
	/**
	 * If a chapter is marked as the end of a story, we will not continue
	 * afterwards.
	 * 
	 * @var string|null
	 */
	private ?string $end = null;
	
	/**
	 * 
	 * @var string[]
	 */
	private array $skip = [];
	
	public function __construct(RemoteWebDriver $selenium)
	{
		$this->selenium = $selenium;
		$this->hooks = [
			'before' => [],
			'after'  => []
		];
	}
	
	public function listChapters() : array
	{
		$reflection = new ReflectionClass($this);
		
		if ($attributes = $reflection->getAttributes(Continues::class)) {
			foreach ($attributes as $attr) {
				$attr->newInstance()->tell($this->selenium);
			}
		}
		
		$methods = get_class_methods($this);
		
		return array_filter(
			$methods,
			fn($e) => strpos($e, 'chapter') === 0
		);
	}
	
	public function before(string $chaptername, callable $do)
	{
		assert($this->has($chaptername));
		$this->hooks['before'][$this->normalize($chaptername)] = $do;
	}
	
	public function after(string $chaptername, callable $do)
	{
		assert($this->has($chaptername));
		$this->hooks['after'][$this->normalize($chaptername)] = $do;
	}
	
	public function normalize(string $chaptername) : string
	{
		if (strpos($chaptername, 'chapter') === 0) {
			return strtolower($chaptername);
		}
		
		return 'chapter' . strtolower($chaptername);
	}
	
	public function has(string $chaptername) : bool
	{
		$lc = array_map(fn($e) => strtolower($e), $this->listChapters());
		return array_search($this->normalize($chaptername), $lc) !== false;
	}
	
	public function endAfter(string $chaptername) : void
	{
		assert($this->has($chaptername));
		$this->end = $this->normalize($chaptername);
	}
	
	/**
	 * 
	 * @param array<string,mixed> $options Configure the story so certain aspects may be told differently.
	 * @return void
	 */
	public function tell(array $options = []) : void
	{
		$chapters = array_map(fn($e) => $this->normalize($e), $this->listChapters());
		
		/**
		 * Remove any chapters that we wish to skip.
		 */
		$chapters = array_filter(
			$chapters, 
			fn($name) => array_search($name, $this->skip) === false
		);
		
		if ($this->end !== null) {
			$chapters = array_slice(
				$chapters,
				0,
				array_search($this->end, $chapters) + 1
			);
		}
		
		foreach ($chapters as $chapter) {
			if (isset($this->hooks['before'][$this->normalize($chapter)])) {
				($this->hooks['before'][$this->normalize($chapter)])();
			}
			
			$method = (new ReflectionMethod($this, $chapter))->getClosure($this);
			(new Container())->call($method, $options);
			
			if (isset($this->hooks['after'][$this->normalize($chapter)])) {
				($this->hooks['after'][$this->normalize($chapter)])();
			}
		}
	}
}
