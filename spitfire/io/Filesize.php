<?php namespace spitfire\io;

use spitfire\exceptions\PrivateException;

class Filesize 
{
	/** 
	 * The length of the data in bytes.
	 * 
	 * @var int 
	 */
	private $bytes;
	
	
	public static $units = Array('', 'K', 'M', 'G', 'T');

	/**
	 * Creates the formatter with the passed value
	 *
	 * @param int $value
	 */
	public function __construct($value){
		$this->bytes = $value;
	}
	
	public function getSize() {
		return $this->bytes;
	}

	/**
	 * Parses the size provided as a string and returns a Filesize instance or 
	 * throws on failure.
	 *
	 * @param string $str
	 *
	 * @return Filesize
	 * @throws PrivateException
	 */
	static function parse($str){
		if (!preg_match('/^(\d+)\s*([TGMK])b?$/i', $str, $matches)) {
			throw new PrivateException("Unable to parse file size ($str)");
		}
		
		$unit = $matches[2];
		$value = intval($matches[1], 10);
		
		switch(strtoupper($unit)){
			case 'T':
				$value *= 1024;
			case 'G':
				$value *= 1024;
			case 'M':
				$value *= 1024;
			case 'K':
				$value *= 1024;
		}
		
		return new Filesize($value);
	}
	
	function __toString() {
		$bytes = $this->bytes;
		$units = 0;
		
		/*
		 * Although I think the one liner that was here before was extremely
		 * impressive. It had some considerable readability issues.
		 */
		while ($bytes > 1024) {
			$bytes = (int)($bytes / 1024);
			$units++;
		}
		
		return sprintf('%s %sB', $bytes, self::$units[$units]);
	}
}
