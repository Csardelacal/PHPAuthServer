<?php namespace spitfire\core\router;

use spitfire\exceptions\PrivateException;
use Strings;

/* 
 * The MIT License
 *
 * Copyright 2017 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The URIPattern class is a wrapper to gather several patterns and test them
 * simultaneously.
 */
class URIPattern
{
	/**
	 * The patterns used by this class to test the URL it receives. 
	 *
	 * @var Pattern[]
	 */
	private $patterns;
	
	/**
	 * Indicates whether this is an open ended pattern. This implies that it'll
	 * accept URL that are longer than the patterns it can test with (these 
	 * additional parameters will get appended to the object).
	 *
	 * @var boolean
	 */
	private $open;
	
	/**
	 * Instances a new URI Pattern. This class allows your application to test 
	 * whether a URL matches the pattern you gave to the constructor.
	 * 
	 * @param string $pattern
	 */
	public function __construct($pattern) {
		/*
		 * If the pattern ends in a slash it's not considered open ended, this is
		 * important for how we parse the pattern.
		 */
		$this->open     = !Strings::endsWith($pattern, '/');
		
		/*
		 * The patterns allow the system to test the URL piece by piece, making it
		 * more granular.
		 */
		$this->patterns = array_values(array_map(function ($e) { return new Pattern($e); }, array_filter(explode('/', $pattern))));
	}
	
	/**
	 * Tests whether a given $uri matches the patterns that this object holds.
	 * Please note that if the URI is too long and the pattern is not open
	 * ended it will throw a missmatch exception.
	 * 
	 * @param string $uri
	 * @return \spitfire\core\router\Parameters
	 * @throws RouteMismatchException
	 */
	public function test($uri) {
		$pieces = is_array($uri)? $uri : array_filter(explode('/', $uri));
		$params = new Parameters();
		
		/*
		 * Walk the patterns and test whether they're all satisfied. Remember that
		 * test raises an Exception when unsatisfied, so there's no need to check -
		 * if the code runs the route was satisfied
		 */
		for ($i = 0; $i < count($this->patterns); $i++) {
			$params->addParameters($this->patterns[$i]->test(array_shift($pieces)));
		}
		
		if (count($pieces)) {
			if ($this->open) { $params->setUnparsed($pieces); }
			else             { throw new RouteMismatchException('Too many parameters', 1705201331); }
		}
		
		return $params;
	}
	
	/**
	 * Takes a parameter list and constructs a string URI from the combination
	 * of patterns and parameters.
	 * 
	 * @param type $parameters
	 * @return type
	 * @throws PrivateException
	 * @throws RouteMismatchException
	 */
	public function reverse($parameters) {
		
		/*
		 * If the data we're receiving is a parameters object, then we'll extract
		 * the raw data from it in order to work with it.
		 */
		$params = $parameters instanceof Parameters? $parameters->getParameters() : $parameters;
		$add    = $parameters instanceof Parameters? $parameters->getUnparsed() : [];
		
		/*
		 * If there is parameters that exceed the predefined length then we drop
		 * them if the route does not support them.
		 */
		if (!empty($add) && !$this->open) {
			throw new PrivateException('This route rejects additional params', 1705221031);
		}
		
		/*
		 * Prepare a pair of variables we need for the loop. First of all we need
		 * to ensure that we have an array to write the replacements to, and then
		 * we need a counter to make sure that all the parameters given were also
		 * used.
		 */
		$replaced = [];
		$left     = count($params);
		
		/*
		 * Loop over the patterns and test the parameters. There's one quirk - the
		 * system assumes that a parameter is never used twice but won't fail
		 * gracefully if the user ignores that restriction.
		 */
		foreach($this->patterns as $p) {
			
			#Static URL elements
			if(!$p->getName()) { 
				$replaced[] = $p->getPattern()[0]; 
				continue;
			}
			
			/*
			 * For non static URL elements we check whether the appropriate parameter
			 * is defined. Then we test it and if the parameter was defined we will
			 * accept it.
			 */
			$defined  = isset($params[$p->getName()])? $params[$p->getName()] : null;
			$replaced = array_merge($replaced, $p->test($defined));
			
			$defined? $left-- : null;
		}
		
		/*
		 * After the system has assembled all the parts for the URL, it tries to 
		 * clean up by removing all components that were provided that are optional
		 * and set to the default value.
		 * 
		 * By making this, I'd expect URL's to gain longevity, since urls like
		 * <code>/about</code> are more likely to be remembered than <code>/about/index/</code>
		 */
		foreach (array_reverse($this->patterns) as $p) {
			if ($p->isOptional() && $p->getDefault() === end($replaced)) {
				array_pop($replaced);
			}
			/*
			 * Once we found an element that is either, not optional or not the default
			 * value, we stop searching. This is because all preceeding parameters
			 * are needed to override the current value.
			 * 
			 * For example, the route /about/:company?m3w/:department?it will be 
			 * reversed to /about when [company => m3w, department => it] is provided.
			 * 
			 * It will also, rather obviously, generate /about/ibm when we provide it
			 * with [company => ibm, department => it] or just [company => ibm]
			 * 
			 * But, it will generate /about/m3w/finance if provided with [department => finance]
			 * this is, because the URL /about/finance would be ambiguous.
			 */
			else {
				break;
			}
		}
		
		/*
		 * Leftover parameters indicate that the system was unable to reverse the 
		 * route properly
		 */
		if ($left) {
			throw new PrivateException('Parameter count exceeded pattern count', 1705221044);
		}
		
		return '/' . implode('/', array_merge($replaced, $add)) . '/';
	}
	
	public static function make($str) {
		if ($str instanceof URIPattern) { return $str; }
		elseif (is_string($str)) { return new URIPattern($str); }
		else { throw new \InvalidArgumentException('Invalid pattern for URIPattern::make', 1706091621); }
	}
	
}
