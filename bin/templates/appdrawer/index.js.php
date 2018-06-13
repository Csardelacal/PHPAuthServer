<?php

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
		'icon' => (string)url('image', 'app', $app->_id, 128)->absolute()
	];
}

$json = json_encode($payload);

?>
//<script>
(function() {
	
	"use strict";
	
	var outer  = document.getElementById('app-drawer');
	var target = outer.appendChild(document.createElement('div'));
	var els    = <?= $json ?>;
	var logout = "<?= url('user', 'logout')->absolute(); ?>";
	
	var wrapper;
	
	for (var i = 0; i < els.length; i++) {
		if (i % 3 === 0) { 
			wrapper = document.createElement('div'); 
			wrapper.className = 'row l3 m3 s3 fluid';
			target.appendChild(wrapper);
		}
		
		var span = wrapper.appendChild(document.createElement('div'));
		span.className = 'span l1 m1 s1';
		
		var a    = span.appendChild(document.createElement('a'));
		a.className = 'app-entry';
		a.href = els[i].url;
		
		var img = a.appendChild(document.createElement('img'));
		img.src = els[i].icon;
		img.className = 'app-icon-drawer';
		
		var name = a.appendChild(document.createElement('span'));
		name.className = 'app-name-drawer';
		name.appendChild(document.createTextNode(els[i].name));	
	}
	
	target.className = 'padded';
	
	var logoutlink = outer.appendChild(document.createElement('a'));
	logoutlink.className = 'footer';
	logoutlink.href = logout + '?returnto=' + encodeURIComponent(window.location);
	logoutlink.innerHTML = 'Logout';
	
}());
