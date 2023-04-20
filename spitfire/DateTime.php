<?php namespace spitfire;

class DateTime extends \DateTime {
	
	public function __toString() {
		return $this->format(core\Environment::get('datetime.format'));
	}
	
}