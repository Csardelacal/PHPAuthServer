<?php

/**
 * This class is intended as a tool to aid creating loops with special events in 
 * them. You can instance the class before running your loop and simply trigger
 * the next() method whenever you need it.
 * 
 * E.g. This is specially useful if you need to print a certain HTML tag every
 * X executions of your loop. You can do it like this:
 * 
 * <code>
 * $every = new Every(3, '<div class="separator"></div>');
 * foreach($elements as $element) {
 *   echo $element;
 *   echo $every->next();
 * }
 * </code>
 * 
 * @author César de la Cal<cesar@magic3w.com>
 */
class Every
{
	/**
	 * The counter that keeps track on which step the loop is. This is incremented
	 * with every call of the next() method until it reaches the value of step and
	 * is reset.
	 *
	 * @var int 
	 */
	private $counter = 0;
	
	/**
	 * The integer value that helps the class to determine whether it should trigger
	 * the action or should still wait.
	 *
	 * @var int
	 */
	private $step;
	
	/**
	 * The action that should be executed and/or returned when this Every is 
	 * triggered.
	 *
	 * @var string|Closure|mixed
	 */
	private $action;
	
	/**
	 * Creates a new every. This class is meant to aid code structure when writing
	 * loops that have special clauses in them when executing. This avoids creating
	 * lots of nexted blocks that may end up confusing the people trying to maintain
	 * the code.
	 * 
	 * @param int $step
	 * @param string|Closure $action
	 */
	public function __construct($step, $action) {
		$this->step = $step;
		$this->action = $action;
	}
	
	/**
	 * This either returns the action passed to the loop so it can be used or it
	 * executes it (in case it is a closure).
	 * 
	 * @return mixed
	 */
	protected function execute() {
		return $this->action instanceof Closure? ($this->action)() : $this->action;
	}
	
	/**
	 * Advances to the next step. Every time this function is called the counter 
	 * is incremented and the system checks whether it reached the expected value 
	 * to trigger the Every.
	 * 
	 * @return mixed
	 */
	public function next() {
		$this->counter++;
		if ($this->step === $this->counter) {
			$this->counter = 0;
			return $this->execute();
		}
		
		return null;
	}
	
}