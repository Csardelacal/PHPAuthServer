<?php namespace commishes\qa\runner;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use commishes\qa\runner\fixtures\Database;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class SeleniumTestCase extends TestCase
{
	
	protected $coverage = 'web.coverage';
	
	protected $db;
	protected RemoteWebDriver $driver;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function setUp(): void
	{
		$pdo = new PDO('mysql:dbname=phpas;host=mysql', 'root', 'root');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db = new Database($pdo);
		#$db->loadFromXML('./fixtures/mvp.xml');
		$this->db = $db;
		
		
		$desiredCapabilities = DesiredCapabilities::chrome(); // or DesiredCapabilities::firefox(); etcv.
		$this->driver = RemoteWebDriver::create('http://selenium-hub:4444', $desiredCapabilities);
		$this->driver()->manage()->window()->setSize(new WebDriverDimension(1280, 800));
		
		/**
		 * Set the coverage collection to collect data for this test.
		 */
		file_get_contents(sprintf('http://%s/start.php?testname=%s', $this->coverage, $this->getName()));
		
	}
	
	public function driver() : RemoteWebDriver
	{
		return $this->driver;
	}
	
	public function db() : Database
	{
		return $this->db;
	}
	
	public function loadFixture(string $filename) : bool
	{
		$this->db->loadFromXML($filename);
		return true;
	}
	
	public function loadGlobalFixture(string $filename) : bool
	{
		$dir = realpath(__DIR__ . '/../tests/fixtures/');
		$this->db->loadFromXML($dir . DIRECTORY_SEPARATOR . $filename);
		return true;
	}
	
	public function story(string $classname) : SeleniumUserStory
	{
		$reflection = new ReflectionClass($classname);
		assert($reflection->isSubclassOf(SeleniumUserStory::class));
		return $reflection->newInstance($this->driver);
	}
	
	public function screenshot(string $step)
	{
		$reflection = new ReflectionClass($this);
		$namespace = $reflection->getNamespaceName();
		$casename = $reflection->getShortName();
		$testname = $this->getName();
		
		$_path = array_merge(['.', 'screenshots'], explode('\\', $namespace), [$casename], [$testname]);
		$path = implode(DIRECTORY_SEPARATOR, $_path);
		
		/**
		 * Make the directory for the screenshot if it does not
		 */
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		
		$this->driver->takeScreenshot("{$path}/{$step}.png");
	}
	
	
	/**
	 * Collects all the code coverage from the shared volume with the target container. During
	 * the test, the target container will generate cov files in the /incoming folder that we 
	 * now need to move to a folder with the name of our test.
	 * 
	 * This is a very precarious method IMO, since it hinges on the docker volumes being correctly
	 * set and I would love to see that moved to a solution that doesn't directly depend on it.
	 * 
	 * @see docker/web/Dockerfile (target: integration) for the scripts injected for cov collection
	 * @see docker-compose-test-runner.yml for the shared volume
	 * @see xdebug/after.php
	 */
	public function tearDown() : void
	{
		$this->driver->quit();
	}
}
