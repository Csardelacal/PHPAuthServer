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
	
	"use strict";
	
	var Collection = function(elements) {
		if (elements instanceof NodeList) {
			elements = Array.prototype.slice.call(elements, 0);
		}
		
		this.elements = elements;
	};
	
	Collection.prototype = {
		each: function(fn) {
			var ret = (typeof(this.elements) === 'object')? {} : [];
			
			if (typeof(this.elements) === 'object') {
				for (var i in this.elements) {
					if (!this.elements.hasOwnProperty(i)) { continue; }
					ret[i] = fn(this.elements[i], i);
				}
			}
			else {
				for (var i = 0; i < this.elements.length; i++) {
					ret[i] = fn(this.elements[i], i);
				}
			}
			
			return new Collection(ret);
		},
		
		filter: function(fn) {
			var result = new Collection([]);
			
			this.each(function(e) {
				if (fn(e)) {
					result.push(e);
				}
			});
			
			return result;
		},
		
		merge: function (elements) {
			var self = this;
			
			if (!(elements instanceof Collection)) {
				elements = new Collection(elements);
			}
			
			elements.each(function (value, idx) {
				if (!(self.elements instanceof Array)) {
					self.elements[idx] = value;
				}
				else {
					self.elements.push(value);
				}
			});
			
			return this;
		},
		
		push: function (v) {
			this.elements.push(v);
		},
		
		set: function (idx, v) {
			this.elements[idx] = v;
		},
		
		get: function (idx) {
			return this.elements[idx];
		},
		
		raw : function () {
			return this.elements;
		}
	};
	
	return function (e) { return new Collection(e); } ;
});