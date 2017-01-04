<?php namespace attribute\validator;

class FileMaxSizeValidator implements \attribute\AttributeValidatorInterface
{
	
	private $size;
	
	public function __construct() {
		
	}

	public function getDescription() {
		return sprintf('File size should not exceed %s bytes', $this->size);
	}

	public function getErrorMsg() {
		return sprintf('File size is more than %s bytes', $this->size);
	}

	public function getName() {
		return 'Maximum file size';
	}

	public function load($settings) {
		if (\Strings::endsWith($settings, 'G')) { $settings = ((int) $settings) * 1024 * 1024 * 1024; }
		if (\Strings::endsWith($settings, 'M')) { $settings = ((int) $settings) * 1024 * 1024; }
		if (\Strings::endsWith($settings, 'K')) { $settings = ((int) $settings) * 1024; }
		
		$this->size = $settings;
	}

	public function test($value) {
		//Validate the file size
	}

	public function validates() {
		return 'file';
	}

}

