<?php namespace spitfire\cache;

/**
 * The memory cache class allows an application to maintain a simple array cache
 * that would otherwise be rather bothersome to do.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class MemoryCache implements CacheInterface
{
	
	/**
	 * This array contains the data we're caching. It's a simple array (map) that
	 * will hold the data for us, this class adapts an array to our Cache interface
	 *
	 * @var mixed 
	 */
	private $data = [];
	
	/**
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function contains($key) {
		return array_key_exists($key, $this->data);
	}
	
	/**
	 * @param string $key
	 */
	public function delete($key) {
		unset($this->data[$key]);
	}
	
	/**
	 * @param string $key
	 * @param \Closure|null $fallback
	 * @return mixed
	 */
	public function get($key, $fallback = null) {
		if ($this->contains($key)) { return $this->data[$key]; }
		return $this->data[$key] = $fallback? $fallback() : false;
	}
	
	public function getAll() {
		return $this->data;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value) {
		return $this->data[$key] = $value;
	}

}

