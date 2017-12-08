<?php namespace mail\spam\domain;

/**
 * 
 * 
 * @todo This class should be split into several sub-classes. 
 * - One for domain data
 * - One for a list entry
 * - One for the hypervisor / dispatcher
 */
class Domain
{
	
	
	/**
	 * The array containing the domain name split by dot characters (.).
	 *
	 * @var string[]
	 */
	private $pieces;
	
	/**
	 * 
	 * @param string|string[] $pieces
	 * @param ReaderInterface $reader
	 * @param WriterInterface $writer
	 */
	public function __construct($pieces) {
		$this->pieces = is_string($pieces)? explode('.', $pieces) : $pieces;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getHostname() {
		return implode('.', $this->pieces);
	}
	
	/**
	 * Returns the list of components that conform the hostname.
	 * 
	 * @return string[]
	 */
	public function getPieces() {
		return $this->pieces;
	}
	
	public function getParent() {
		return new Domain(array_slice($this->pieces, 1));
	}

}
