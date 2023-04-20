<?php namespace spitfire\validation\parser;

/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
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
 * Within a parser, the options are the parameters provided to a validator's
 * parameters. Making them, kind of, second level parameters.
 * 
 * For example, when a validator is defined as a GET#input(string length[10]),
 * the options provided are that the string must be, at least, 10 characters
 * long.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class Options
{
	
	private $items;
	
	public function __construct(array $items) {
		$this->items = collect($items)
			->each(function ($e) { return $e instanceof Token? $e->getContent() : preg_split('/\,|\s/', $e); })
			->flatten()
			->filter();
	}
	
	public function getItems() {
		return $this->items->toArray();
	}
	
	public function __toString() {
		return sprintf('[%s]', $this->items->join(', '));
	}

}
