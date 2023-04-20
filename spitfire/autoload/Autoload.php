<?php namespace spitfire\autoload;

use spitfire\autoload\RegisteredClassLocator;

/**
 * As with every application, the autoload is in charge of retrieving classes for
 * the system to be used without the developer explicitely indicating where the
 * system should retrieve them.
 * 
 * This autoloader supports the use of ClassLocators, which will actually perform
 * the task of retrieving the class. It will loop over these locators sequentially
 * and stopping once one of the locators has had a positive match.
 * 
 * Therefore you should avoid having locators that could generate false positives.
 * Since these might screw your system up.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class AutoLoad
{
	
	/**
	 * The autoloader instance. In most scenarios (aka. outside of specific testing)
	 * it makes no sense to maintain several instances of autoload.
	 * 
	 * While it is possible and the autoloads should not cause interference, it's 
	 * not tested behavior and PHP may do whatever it wants with the different
	 * autoloads.
	 *
	 * @var AutoLoad
	 */
	static $instance            = null;

	private $imported_classes   = Array();
	private $locators           = Array();

	public function __construct() {
		self::$instance = $this;
		spl_autoload_register(Array($this, 'retrieveClass'));
	}

	public function register($className, $location) {
		RegisteredClassLocator::register($className, $location);
	}

	public function retrieveClass($className) {
		foreach ($this->locators as $locator) {
			if (false !== $file = $locator->getFilenameFor($className)) {
				include $file;
				$this->imported_classes[$className] = $file;
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * 
	 * @param \spitfire\autoload\ClassLocator $locator
	 */
	public function registerLocator($locator) {
		$this->locators[] = $locator;
	}

	#######STATIC METHODS#####################

	public static function registerClass($class, $location) {
		self::$instance->register($class, $location);
	}
	
	/**
	 * Autoload uses the singleton pattern (you don't have several autoloads in 
	 * Spitfire) to find classes it needs. Use ClassLocators to add functionality
	 * to the autoload.
	 * 
	 * @return \spitfire\AutoLoad
	 */
	public static function getInstance() {
		return self::$instance;
	}

}