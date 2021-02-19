<?php namespace twofactor\sms;

/* 
 * The MIT License
 *
 * Copyright 2020 César de la Cal Bretschneider <cesar@magic3w.com>.
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
 * This class represents a short text message to be delivered by the system to
 * a user so they can verify their identity. SMS are not used by the system for
 * notifications, only 2FA.
 * 
 * SMS messages are extremely simple in nature, they contain a recipient and a 
 * body. And that's it.
 * 
 * @author César de la Cal Bretschneider <cesar@magic3w.com>
 */
class Message
{
	
	/**
	 * The internationalized phone number to send the SMS to. 
	 *
	 * @var string 
	 */
	private $number;
	
	/**
	 * The content of the SMS to be delivered.
	 * 
	 * @var string
	 */
	private $body;
	
	/**
	 * Create a new SMS message.
	 * 
	 * @param string $number
	 * @param string $body
	 */
	public function __construct($number, $body) {
		$this->number = $number;
		$this->body = $body;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getNumber() {
		return $this->number;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

}
