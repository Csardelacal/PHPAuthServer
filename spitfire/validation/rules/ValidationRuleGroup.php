<?php namespace spitfire\validation\rules;

use spitfire\validation\ValidationError;
use spitfire\validation\ValidationRule;
use spitfire\validation\ValidatorInterface;

/**
 * Validation groups allow an application to use several different criteria to 
 * create complex validation schemes that would otherwise require writing complex
 * rules or if statements.
 * 
 * Please note that it is encouraged to replace validation groups with custom
 * rules that will yield higher code maintainability.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class ValidationRuleGroup implements ValidationRule
{
	/**
	 * The AND type implies that one failed validation will cause the entire group's
	 * return to be <i>failed</i>.
	 */
	const TYPE_AND = 'and';
	
	/**
	 * When this is active, a rule passing means that messages will be discarded
	 * and the validation will be passed.
	 */
	const TYPE_OR  = 'or';
	
	/**
	 * The parent element. It is used to allow chaining rules and groups in the 
	 * same validator.
	 *
	 * @var ValidatorInterface
	 */
	private $parent;
	
	/**
	 * The type of the group. You may use AND rules inside of OR rules. Using AND
	 * without encapsulating them in an OR group is useless since the Validator 
	 * uses AND by default.
	 *
	 * @var string 
	 */
	private $type = self::TYPE_OR;
	
	/**
	 * This array maintains the rules used to validate the data in this group.
	 * 
	 * @var ValidationRule[] 
	 */
	private $rules = Array();
	
	/**
	 * Creates a new validation group rule. These rules allow applications to 
	 * validate complex scenarios - like types that need to be submitted matching
	 * a regex or a number.
	 * 
	 * This scenarios are rare and should usually be implemented with custom rules.
	 * 
	 * @param type $parent
	 */
	public function __construct($parent = null) {
		$this->parent = $parent;
	}
	
	/**
	 * Adds a new rule to the validation group. This can be another group, but 
	 * nesting groups is heavily discouraged.
	 * 
	 * @param ValidationRule $rule
	 * @return ValidationRuleGroup
	 */
	public function addRule(ValidationRule $rule) {
		$this->rules[] = $rule;
		return $this;
	}
	
	/**
	 * Use one of the class constants TYPE_AND or TYPE_OR to select the behavior
	 * you wish to achieve in the validation rule.
	 * 
	 * @param string $type
	 * @return ValidationRuleGroup
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	
	/**
	 * Uses the rules provided to test whether the value provided matches the 
	 * rules that were defined previously.
	 * 
	 * @param mixed $value
	 * @return boolean|ValidationError
	 */
	public function test($value) {
		$result = $this->iterateRules($value);
		
		if (is_array($result)) { return reset($result); }
		else                   { return $result; }
	}
	
	public function iterateRules($value) {
		
		$t = Array();
		
		foreach ($this->rules as $rule) { $t[] = $rule->test($value); }
		
		$result = array_filter($t);
		
		if (empty($result))                     { return false; }
		elseif ($this->type === self::TYPE_AND) { return $result; }
		else                                    { return count($result) !== count($this->rules)? false : $result; }
		
		return false;
	}
	
	public function end() {
		return $this->parent;
	}

}