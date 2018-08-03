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


depend(function () {
	
	var listening = [];
	var onscreen  = undefined;
	
	/**
	 * 
	 * @param {type} element
	 * @returns {undefined}
	 */
	var dropdown = function (element, target) {
		
		var visible = false;
		var showing = undefined;
		
		this.getElement = function () {
			return element;
		};
		
		this.find = function(s) {
			var e = typeof(element) === 'string'? Array.prototype.slice.call(document.querySelectorAll(element)) : [element];
			var p = s;
			
			do {
				if (e.indexOf(p) !== -1) { return p.getAttribute('data-toggle')? p.getAttribute('data-toggle') : true; }
				if (p === document.body) { return false; }
			}
			while (p = p.parentNode);
		}
		
		this.toggle = function (t) {
			if (!target) {
				t = document.querySelector('*[data-dropdown="' + t + '"]');
			}
			else {
				t = target;
			}
			
			visible = !visible;
			t.style.display = visible? 'block' : 'none';
			
			if (visible) { 
				onscreen = this; 
				showing = t;
			}
		};
		
		this.hide = function () {
			var t;
			
			if (!target) {
				t = showing;
			}
			else {
				t = target;
			}
			
			if (!t) { return; }
			
			visible = false;
			onscreen = null;
			t.style.display = 'none';
		};
		
		this.isShowing = function (el) {
			do {
				if (el === showing) { return true; }
				el = el.parentNode;
			} while (el !== document.body);
			
			return false;
		};
		
		listening.push(this);
		this.hide();
	};
	
	var listener = function (e) {
		var found = undefined;
		var id    = undefined;
		
		for (var i = 0; i < listening.length; i++) {
			if (id = listening[i].find(e.target)) {
				found = listening[i];
				break;
			}
		}
		
		if (found) {
			
			found.toggle(id);
			
			e.stopPropagation();
			e.preventDefault();
			return;
		}
		
		if (onscreen && !onscreen.isShowing(e.target)) {
			onscreen.hide();
		}
		
	};
	
	document.body.addEventListener('click', listener, false);
	
	return dropdown;
});
