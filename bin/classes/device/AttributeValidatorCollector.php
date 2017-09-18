<?php namespace attribute;

use DirectoryIterator;
use ReflectionClass;
use spitfire\cache\MemoryCache;

/**
 * This class is put in charge of iterating over the validators that the system
 * makes available and then makes them available to the system in order to load
 * them.
 * 
 * Please note that this class should not be used to "load" validators for defined
 * settings. This class is pretty impactful on the performance of the system and
 * should therefore only be used to populate lists the administrator uses to
 * configure the system.
 */
class AttributeValidatorCollector
{
	
	/**
	 * The directory containing the validators, this will be fed into realpath().
	 */
	const VALIDATOR_DIR = 'validator';
	
	/**
	 * Since the operation of looping over the directories is pointless after the
	 * first time, we do cache the results for the application to reuse as often
	 * as needed within the same iterator.
	 * 
	 * @var MemoryCache
	 */
	private $cache;
	
	public function __construct() {
		$this->cache = new MemoryCache();
	}
	
	public function getValidators($type = null) {
		$validators = $this->cache->get('validators', function () { return $this->makeValidators(); });
		
		if ($type) {
			$validators = array_filter($validators, function ($e) use ($type) {
				return $e->validates === $type;
			});
		}
		
		return $validators;
	}
	
	public function makeValidators() {
		#First of all we need an iterator capable of looping over the classes
		$iterator   = new DirectoryIterator(realpath(dirname(__FILE__) . '/' . self::VALIDATOR_DIR));
		
		foreach($iterator as $file) {
			if (!$file->isDot() && !$file->isDir()) { include_once $file->getRealPath(); }
		}
		
		#Once we included the files we look for iterators we might have imported
		$validators = array_filter(get_declared_classes(), function ($e) {
			$r = new ReflectionClass($e);
			return $r->isInstantiable() && $r->isSubclassOf(AttributeValidatorInterface::class);
		});
		
		#Iterate over the validators we got. This way we can create instances that
		#can easily be reused
		foreach ($validators as &$v) { $v = new $v(); }
		
		return $validators;
	}
	
}
