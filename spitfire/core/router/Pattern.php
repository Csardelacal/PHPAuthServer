<?php namespace spitfire\core\router;

/**
 * The pattern class allows to test an URL fragment (the piece that originates 
 * when splitting the path by '/'). While the router doesn't allow to use complex
 * regular expressions it therefore increases the stability and security.
 * 
 * Users don't need to learn nor understand how a regular expression works in order
 * to use this more simple patterns. This allow a user to test a string and assign
 * it an ID the user can use to retrieve it.
 * 
 * @author César de la Cal <cesar@magic3w.com>
 */
class Pattern
{
	/**
	 * Indicates the pattern is not a wildcard but either a static value or a 
	 * series of values separated by '|'. This pattern will return an empty array
	 * in case of a successful test.
	 */
	const WILDCARD_NONE    = 0;
	
	/**
	 * Indicates the pattern is testing for a string. In this case, every string 
	 * will be matched. This allows your application to use the pattern as name
	 * for the parameter.
	 * 
	 * The constructor will consider a string wildcard every parameter starting
	 * with a colon (:).
	 */
	const WILDCARD_STRING  = 1;
	
	/**
	 * If the string passed with the URL is a integer then it will be accepted as
	 * such and be parsed. Otherwise the router will receive a RouteMismatch Exception
	 * indicating that the string was not valid.
	 */
	const WILDCARD_NUMERIC = 2;
	
	/**
	 * The pattern type. This will define how the Pattern decides which content 
	 * is to be tested as valid. This can be any of the WILDCARD_ constants this 
	 * class defines.
	 * 
	 * @var int
	 */
	private $type;
	
	/**
	 * The name of the variable that is being assigned the content of the result
	 * of this pattern. When the name is set the test will return an exception
	 * or an array like (name => value).
	 * 
	 * @var string
	 */
	private $name;
	
	/**
	 * The pattern to be tested. In case the router is testing for a wildcard this
	 * will contain the name of the parameter to be return in case of a success.
	 *
	 * @var null|string|array|\Closure
	 */
	private $pattern;
	
	/**
	 * Indicates whether the Pattern must to be satisfied or not.
	 * 
	 * If the pattern is optional this variable may contain boolean(true), it may
	 * also contain the default value to be returned if left empty.
	 *
	 * @var boolean|string
	 */
	private $optional = false;
	
	/**
	 * Creates the pattern from the base string that comes with the URL. It will
	 * retrieve information whether the pattern was optional or not, what type it
	 * was and if it is a basic pattern.
	 * 
	 * @param string $pattern
	 */
	public function __construct($pattern) {
		$this->extractType($this->extractOptional($pattern));
	}
	
	/**
	 * This function will check if a pattern is optional. This means that it will
	 * return a valid result when it receives an empty value.
	 * 
	 * Please note that if it receives a value and it's not valid it will return
	 * an error.
	 * 
	 * @param string $pattern
	 * @return string The rest of the pattern
	 */
	protected function extractOptional($pattern) {
		
		$pos = strpos($pattern, '?');
		
		if ($pos !== false) {
			$this->optional = substr($pattern, $pos + 1)? : true;
			$pattern        = substr($pattern, 0, $pos);
		}
		
		return $pattern;
	}
	
	/**
	 * Assigns the type variable and/or pattern that is to be matched by reading
	 * a URL string that can be passed to the router as string. Even though the 
	 * router's matching mechanism is really basic it should be sufficient.
	 * 
	 * @param string $pattern
	 */
	protected function extractType($pattern) {
		
		switch ( substr($pattern, 0, 1) ) {
			case ':':
				$this->type     = self::WILDCARD_STRING;
				$this->pattern  = explode('|', substr($pattern, 1));
				$this->name     = array_shift($this->pattern);
				break;
			case '#':
				$this->type     = self::WILDCARD_NUMERIC;
				$this->pattern  = explode('|', substr($pattern, 1));
				$this->name     = array_shift($this->pattern);
				break;
			default:
				$this->type     = self::WILDCARD_NONE;
				$this->name     = null;
				$this->pattern  = explode('|', $pattern);
				break;
		}
	}
	
	/**
	 * Tests whether a string staisfies the pattern being optional. This will always
	 * return false if the pattern is not optional or the string being tested is 
	 * not empty.
	 * 
	 * @param string $str
	 * @return array|false
	 */
	public function testOptional($str) {
		if ($this->optional && empty($str)) {
			return Array($this->name => $this->optional !== true? $this->optional : null);
		}
		
		return false;
	}
	
	/**
	 * Tests whether the string matches the current pattern and returns an array
	 * in case it does. Otherwise it throws an exception.
	 * 
	 * @todo This could probably be better if split into several functions.
	 * @param string $str
	 * @return string[]
	 * @throws RouteMismatchException
	 */
	public function testString($str) {
		#Numeric patterns need to be made re-tested to ensure that they're actually numeric
		if ($this->type === self::WILDCARD_NUMERIC && !is_numeric($str)) {
			throw new RouteMismatchException('Expected number for ' . $this->name);
		}
		
		#Check whether the pattern is correct
		if ($this->testPattern($str)) { 
			return $this->name? [$this->name => $str] : [];
		}
		
		#If the pattern wasn't matched throw us out of it
		throw new RouteMismatchException();
	}
	
	/**
	 * Tests whether a string satisfies this pattern and returns the value it read
	 * out or throws an exception indicating that the route wasn't matched.
	 * 
	 * @throws RouteMismatchException
	 * @param string $str
	 * @return string
	 */
	public function test($str) {
		$r = $this->testOptional($str);
		return ($r !== false)? $r : $this->testString($str);
	}
	
	/**
	 * Returns whether the pattern was matched. This depends on the type of pattern.
	 * * If it's a Closure the result of the closure will determined if it's ok
	 * * Arrays will be searched for a match
	 * * Strings will be split by pipe characters (|) and then searched
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	public function testPattern($value) {
		#If the pattern is null then it is always valid
		if ($this->pattern === null) {
			return true;
		}
		
		#If the pattern is an array then we search it for the value
		elseif (is_array($this->pattern)) {
			return empty($this->pattern) || in_array($value, $this->pattern);
		}
		
		#If the pattern has been passed as a closure it will execute it and return the value
		elseif ($this->pattern instanceof \Closure) {
			$t = $this->pattern; //This workaround is a necessity for PHP5 based systems
			return $t($value);
		}
		
		#Otherwise
		else {
			return in_array($value, explode('|', $this->pattern));
		}
	}
	
	/**
	 * Defines the pattern to be used to test. This can be either a string, closure
	 * or an array.
	 * 
	 * @param null|string|array|\Closure $pattern
	 */
	public function setPattern($pattern) {
		$this->pattern = $pattern;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getPattern() {
		return $this->pattern;
	}
	
	public function isOptional() {
		return $this->optional !== false;
	}
	
	/**
	 * Returns whether the pattern parses a variable out of the strings it 
	 * receives. This allows the app to check whether the pattern can return
	 * data.
	 * 
	 * Usually this doesn't make much of a difference, since nameless patterns
	 * return the exact same data (except for it being empty), but it allows the 
	 * application to check whether a given set of data is missing. It also allows
	 * it to list variables that a URIpattern would return / accept.
	 * 
	 * @return bool
	 */
	public function isVariable() {
		return $this->name !== null;
	}
	
	/**
	 * Returns bool(false) if the pattern is not optional and the value if it is
	 * optional. It's recommended to test whether 
	 * 
	 * @return false|string
	 */
	public function getDefault() {
		return $this->optional;
	}
}
