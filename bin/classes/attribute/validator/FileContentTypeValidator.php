<?php namespace attribute\validator;

class FileContentTypeValidator implements \attribute\AttributeValidatorInterface
{
	
	private $type;
	
	public function __construct() {
		
	}

	public function getDescription() {
		return sprintf('File should be of type %s', $this->type);
	}

	public function getErrorMsg() {
		return sprintf('File should be %s', $this->type);
	}

	public function getName() {
		return 'Content type';
	}

	public function load($settings) {
		$this->type = $settings;
	}

	public function test($value) {
		if (!$value instanceof \spitfire\io\Upload) { return false; }
		
		$type = explode('/', $value->get('type'));
		$match = explode('/', $this->type);
		
		foreach ($match as $idx => $piece) {
			if ($piece === '*')                                      { continue; }
			if ($type[$idx] !== $match[$idx] || !isset($type[$idx])) { return new \spitfire\validation\ValidationError($this->getErrorMsg()); }
		}
		
		return false;
	}

	public function validates() {
		return 'file';
	}

}

