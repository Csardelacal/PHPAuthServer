<?php namespace spitfire\core\router;

use spitfire\collection\Collection;
use spitfire\core\Path;
use spitfire\exceptions\PrivateException;

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
 * The parameterized path is a class that intends to hold patterns inside the 
 * components of the Path. These patterns allow it to extract the parameters 
 * from a given path and to assemble a proper path from the parameters.
 */
class ParametrizedPath extends Path
{
	
	/**
	 * Creates a path from the given parameters (as a Parameters object or array),
	 * including the unparsed data from a Parameter object (in the event it's 
	 * provided.
	 * 
	 * @param mixed|Parameters $data
	 */
	public function replace($data) {
		
		/*
		 * Parameter objects are treated in a special manner. We can use them to 
		 * extract the additional (so called 'unparsed') parameters
		 */
		if ($data instanceof Parameters) {
			$add  = $data->getUnparsed();
			$data = $data->getParameters();
		}
		/*
		 * Arrays have no additional data so we do not need to splice them.
		 */
		else {
			$add  = Array();
		}
		
		/*
		 * Construct the path that contains the replaced parameters and therefore 
		 * can be used to construct the path that can be used to handle a request
		 * with controller, action and object.
		 */
		$path = new Path(
			self::replaceIn($this->getApp(), $data), 
			self::replaceIn($this->getController(), $data), 
			self::replaceIn($this->getAction(), $data), 
			array_merge(self::replaceIn($this->getObject(), $data), $add), 
			$this->getFormat(), 
			self::replaceIn($this->getParameters(), $data)
		);
		
		return $path;
	}
	
	/**
	 * Extracts data from the given Path into a series of parameters. These can, 
	 * for example, then be used to either sequence a URI using URIPattern.
	 * 
	 * This function does not detect conflicting parameters, broken 
	 * 
	 * @return Parameters
	 */
	public function extract(Path$from) {
		/*
		 * Since we have to loop over several elements we use a function that we 
		 * then can call anonymously.
		 * 
		 * The lenient parameter indicates that the source array can be larger 
		 * than the pattern array and provide overflow data.
		 */
		$fn = function($a, $b, $lenient = false) {
			$_ret = [];
			
			/*
			 * First we check whether the patterns are compatible in the first place
			 * by verifying that the length of the arrays is equal.
			 */
			if ( count($a) > count($b) || (!$lenient && count($a) !== count($b)) ) {
				throw new PrivateException('Array too short', 1705212217); 
			}
			
			/**
			 * Loop over the patterns to assemble the data that we can extract.
			 * This will give us an indexed array at the end.
			 */
			for($i = 0, $c = count($a); $i < $c; $i++) {
				if ($a[$i] instanceof Pattern && $a[$i]->test($b[$i]) && $a[$i]->getName()) {
					$_ret[$a[$i]->getName()] = $b[$i];
				}
			}
			
			return $_ret;
		};
		
		/*
		 * Use the closure we just defined to work our way through the class' 
		 * internal data.
		 */
		$p = new Parameters();
		$p->addParameters($fn([$this->getApp()],        [$from->getApp()]));
		$p->addParameters($fn($this->getController(),   $from->getController()));
		$p->addParameters($fn([$this->getAction()],     [$from->getAction()]));
		$p->addParameters($fn($this->getObject(),       $from->getObject(), true));
		$p->addParameters($fn([$this->getParameters()], [$from->getParameters()]));
		$p->setUnparsed(array_slice($from->getObject(), count($this->getObject())));
		
		return $p;
	}
	
	/**
	 * Returns a collection of the patterns this path holds. Please note that some
	 * 'patterns' may be almost literals and have no other use than enforcing a 
	 * certain string somewhere.
	 * 
	 * If you do wish to check whether a pattern is used for a parameter you can
	 * use getName() on that pattern to get the variable name it'd fill in or a
	 * null value if it does not contain a parameter.
	 * 
	 * @return Collection
	 */
	public function getPatterns() {
		#Extract the patterns
		$patterns = Collection::fromArray(array_merge(
			[$this->getApp()],
			$this->getController(),
			[$this->getAction()],
			$this->getObject(),
			$this->getParameters()
		));
		
		return $patterns->filter(function ($e) { return $e instanceof Pattern; });
	}
	
	/**
	 * Recursively walks an array of data and replaces patterns with the data 
	 * provided to it. 
	 * 
	 * @todo Recursively walking arrays should not be a task that this function implemtns
	 * @param Pattern $src
	 * @param mixed $data
	 * @return Pattern
	 * @throws PrivateException
	 */
	private static function replaceIn($src, $data) {
		
		/*
		 * If we passed an array we will individually replace every src with their
		 * valid data.
		 */
		if (is_array($src)) {
			return array_map(function($e) use ($data) { return self::replaceIn($e, $data); }, $src);
		}
		
		if ($src instanceof Pattern) {
			$name = $src->getName();
			
			if (!$name) { return $src->getPattern()[0]; }
			else        { return current($src->test($data[$name]?? null)); }
		}
		
		if (is_scalar($src) || empty($src)) {
			return $src;
		}
		
		throw new PrivateException('Path contains invalid objects - ' . $src, 1705181742);
	}
	
	/**
	 * Converts an array into a patterned path. This allows the programmer to 
	 * write a slicker array markup than having to instance the patterns.
	 * 
	 * These are valid indexes in the array:
	 * <ul>
	 * <li>app</li>
	 * <li>controller</li>
	 * <li>action</li>
	 * <li>object</li>
	 * <li>parameters</li>
	 * </ul>
	 * 
	 * 
	 * @param string[][] $arr An array containing string arrays for the pattern replacement
	 * @return ParametrizedPath
	 */
	public static function fromArray($arr) {
		
		/**
		 * Convert all the elements in the array to patterns so we can feed it into
		 * the new object
		 */
		foreach($arr as &$e) {
			$e = is_array($e)? array_map(function ($e) { return new Pattern($e); }, $e) : [new Pattern($e)];
		}
		
		/**
		 * Instance the new parameterized path that contains the given elements.
		 * We use defaults to prevent the function from failing if a certain 
		 * element is not present, this makes the function more convenient but 
		 * harder to debug.
		 * 
		 * This is our first dib into testing with PHP 7 - it's the first instance
		 * of code that requires the new version to function.
		 */
		return new ParametrizedPath(
			$arr['app']?? null, 
			$arr['controller']?? null, 
			isset($arr['action'])? reset($arr['action']) : null, 
			$arr['object']?? null, 
			null,
			$arr['parameters']?? []
		);
	}
	
}
