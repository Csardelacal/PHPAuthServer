<?php namespace spitfire\validation\parser;

use spitfire\validation\parser\ExpressionValidator;
use spitfire\validation\ValidatorGroup;
use spitfire\exceptions\PrivateException;

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

class GroupComponent
{
	
	private $items;
	
	private $type;
	
	public function __construct($items, $type = null) {
		$this->items = $items;
		$this->type  = $type;
	}
	
	public function tokenize() {
		$set = [];
		
		foreach ($this->items as $e) {
			if ($e instanceof GroupComponent) { $e->tokenize(); $set[] = $e; }
			elseif (is_string($e)) { $set = array_merge($set, array_map(function ($e) { return new Token($e); }, array_filter(explode(' ', $e)))); }
			else { $set[] = $e; }
		}
		
		$this->items = $set;
		
		return $this;
	}
	
	public function getItems() {
		return $this->items;
	}
	
	public function setItems($items) {
		$this->items = $items;
		return $this;
	}
	
	public function push($item) {
		$this->items[] = $item;
	}
	
	public function make($ctx) {
		$items = array_values($this->items);
		
		if (count($items) === 2 && $items[0] instanceof Token && $items[1] instanceof GroupComponent) {
			$params = $ctx->makeRules($items[1]->getItems());
			$fn = new ExpressionValidator($items[0]->getContent());
			
			foreach ($params as $p) { $fn->addRule($p); }
			return $fn;
		}
		else {
			foreach ($items as &$item) { 
				if (!$item instanceof GroupComponent) { 
					throw new PrivateException('Invalid expression, received ' . get_class($item), 1805211230); 
				}
				$item = $item->make($ctx); 
			}
			return new ValidatorGroup($items, $this->type? : ValidatorGroup::TYPE_AND);
		}
	}
		
	public function __toString() {
		return sprintf('group(%s)', implode(',', $this->items));
	}

}
