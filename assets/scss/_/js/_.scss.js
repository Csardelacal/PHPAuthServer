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

/*
 * Some of the features that _SCSS provides cannot be provided without Javascript.
 * This includes, but is not limited to some forms of dialogs, form elements, etc.
 * 
 * When using these elements the system should fallback to a basic CSS / SCSS 
 * solution until the javascript required to make it work is either loaded or 
 * available.
 * 
 * My intention for this script is to reduce it to the bare minimum, doing as much 
 * of the heavy lifting as possible in CSS / SCSS.
 */
depend(['m3/depend/router'], function(router) {
	
	var location = document.querySelector('meta[name="_scss"]').getAttribute('content') || '/assets/scss/_/js/';
	
	router.startsWith('_scss/').to(function(str) {
		return location + str.substring(6) + '.js';
	});
	
	depend([/*'_scss/ui/checkbox',/**/ '_scss/sidebar'], function(sidebar) {
		
		sidebar(document.querySelector('.sidebar'));
		
		/*
		 * Let the CSS know that the javascript required to make some of _SCSS's features
		 * work has been loaded.
		 */
		var body = document.body;
		body.classList.add('_scss-js-loaded');
	});
	
	
});
