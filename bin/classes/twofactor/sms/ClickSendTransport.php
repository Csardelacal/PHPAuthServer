<?php namespace twofactor\sms;

use function request;

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

class ClickSendTransport implements TransportInterface
{
	
	private $settings;
	
	/**
	 * 
	 * @param string $settings With the following information "username:API-secret"
	 */
	public function __construct(string $settings) {
		$this->settings = $settings;
	}
	
	/**
	 * Use click-send to deliver an SMS
	 * 
	 * @param Message $message
	 * @return bool
	 */
	public function deliver(Message $message): bool {
		
		$request = request('https://rest.clicksend.com/v3/sms/send');
		$request->header('Authorization', 'Basic ' . base64_encode($this->settings));
		$request->header('Content-type', 'application/json');
		$request->post(json_encode([
			'messages' => [
				[
					'source' => 'PHPAS',
					'body'   => $message->getBody(),
					'to'     => $message->getNumber()
				]
			]
		]));

		$json = $request->send()->expect(200)->json();
		
		return $json->data->queued_count > 0;
	}

}
