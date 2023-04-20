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

class ExpressionValidator extends \spitfire\validation\Validator
{
	private $src = 'GET';
	
	private $parameter;
	
	private $caption;
	
	public function __construct($parameter) {
		$p = explode('#', $parameter);
		
		if     (isset($p[2])) { list($this->src, $this->parameter, $this->caption) = $p; }
		elseif (isset($p[1])) { list($this->src, $this->parameter) = $p; }
		else              { $this->parameter = $p[0]; }
		
	}
	
	public function setValue($value) {
		if (!isset($value[$this->src]) || !isset($value[$this->src][$this->parameter])) { 
			parent::setValue(null);
		}
		else {
			parent::setValue($value[$this->src][$this->parameter]);
		}
		
		return $this;
	}
	
	public function getMessages() {
		$msgs = parent::getMessages();
		
		foreach ($msgs as $msg) {
			if ($msg instanceof \spitfire\validation\ValidationError) {
				$msg->setMessage(ucfirst($this->caption?: $this->parameter) . ': ' . $msg->getMessage());
			}
		}
		
		return $msgs;
	}

}
