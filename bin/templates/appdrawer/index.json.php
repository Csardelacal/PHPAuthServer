<?php

current_context()->response->getHeaders()->set('Access-Control-Allow-Origin', '*');

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

$payload = [];

foreach ($apps as $app) {
	$payload[] = [
		'id' => $app->appID,
		'name' => $app->name,
		'url'  => $app->url,
		'icon' => [
			's'  => (string)url('image', 'app', $app->_id,  32)->absolute(),
			'm'  => (string)url('image', 'app', $app->_id,  64)->absolute(),
			'l'  => (string)url('image', 'app', $app->_id, 128)->absolute(),
			'xl' => (string)url('image', 'app', $app->_id, 256)->absolute(),
		]
	];
}

$json = json_encode($payload);

echo isset($_GET['p'])? sprintf('%s(%s)', $_GET['p'], $json) : $json;
