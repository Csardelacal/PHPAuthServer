<?php namespace spitfire\validation;

/**
 * 
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
class ValidatorGroup implements ValidatorInterface
{
	
	const TYPE_AND = 'AND';
	const TYPE_OR  = 'OR';
	
	private $validators;
	
	private $type;
	
	public function __construct($validators, $type = self::TYPE_AND) {
		$this->validators = $validators;
		$this->type = $type;
	}

	public function getMessages() {
		$messages = [];
		
		foreach ($this->validators as $validator) {
			$messages = array_merge($messages, $validator->getMessages());
		}
		
		return $messages;
	}

	public function isOk(): bool {
		$ok = $this->type === 'AND'? true : false;
		
		foreach ($this->validators as $validator) {
			if ($this->type === 'AND') { $ok = $ok && $validator->isOk(); }
			else                       { $ok = $ok || $validator->isOK(); }
		}
		
		return $ok;
	}

	public function validate() {
		if (!$this->isOk()) {
			throw new ValidationException('Validation failure', 0, $this->getMessages());
		}
	}
	
	public function setValue($value) {
		
		foreach ($this->validators as $validator) {
			$validator->setValue($value);
		}
		
		return $this;
	}

}
