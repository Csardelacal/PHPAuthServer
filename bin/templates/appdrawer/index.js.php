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
	
	/*
	 * This script provides self contained code to attach the app-drawer to your
	 * application. Therefore it should have no external dependencies and have as
	 * little side effects as possible.
	 * 
	 * Therefore, this function allows us to "dump" CSS properties into an object,
	 * removing the need for third party CSS
	 */
	var merge = function (b) {
		/*
		 * Loop over the elements in b and overwrite the ones on the local object 
		 * that are overshadowed.
		 */
		for (var i in b) {
			if (!b.hasOwnProperty(i)) { continue; }
			
			if (typeof(b[i]) === 'object') { 
				/*
				 * When recursing into objects, we need to be careful to check whether
				 * the source already has the property defined or not
				 */
				if (this[i]) { merge.call(this[i], b[i]); }
				else         { this[i] = b[i]; }
			}
			else { this[i] = b[i]; }
		}
	};
	
	/*
	 * This function is a helper to quickly create elements with static properties
	 */
	var make = function (parent, tag, properties) {
		var e = document.createElement(tag);
		merge.call(e, properties || {});
		
		parent && parent.appendChild(e);
		
		return e;
	};
	
	var outer = document.getElementById('app-drawer');
	var target = make(null, 'div', {className: 'padded'});
	var els = <?= $json ?>;
	var logout = "<?= url('user', 'logout')->absolute(); ?>";
	
	var wrapper;
	var span;
	
	make(document.head, 'style', <?= json_encode(['innerHTML' => file_get_contents(spitfire()->getCWD() . '/assets/css/drawer.css')]) ?>);
	
	for (var i = 0; i < els.length; i++) {
		if (i % 3 === 0) { 
			wrapper = make(target, 'div', {
				style: {
					width: '100%',
					padding: '5px 20px'
				}
			});
		}
		
		span = make(wrapper, 'div', {style : {
				width: '30%',
				margin: '0 1.5%',
				display: 'inline-block'
		}});
		
		var a = make(span, 'a', {
			//TODO: Remove references to PHPAS specific CSS
			className: 'app-entry',
			href: els[i].url
		});
		
		make(a, 'img', {
			src: els[i].icon,
			//TODO: Remove references to PHPAS specific CSS
			className: 'app-icon-drawer'
		});
		
		make(a, 'span', {
			//TODO: Remove references to PHPAS specific CSS
			className: 'app-name-drawer',
			innerHTML: els[i].name
		});
		
	}
	
	outer.appendChild(target);
	
	make(outer, 'a', {
		className: 'footer',
		href: logout + '?returnto=' + encodeURIComponent(window.location),
		innerHTML: 'Logout'
	});
	
	depend('phpas/app/drawer', function () {
		return 'drawer loaded';
	});
	
}());
