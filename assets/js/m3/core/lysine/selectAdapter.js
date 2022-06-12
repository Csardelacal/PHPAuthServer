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


depend(function() {
	

	/**
	 * An input adapter defines data inside a &lt;input> tag. To do so, it changes
	 * or reads it's value when the user requests data.
	 * 
	 * @param {type} element
	 * @returns {lysine_L11.InputAdapter}
	 */
	function SelectAdapter(element) {
		this.view = undefined;
		
		/**
		 * Gets the value of the input being managed. It will therefore just read 
		 * the object's value property.
		 * 
		 * @todo Add a special case for the event of a textarea
		 * @returns {String}
		 */
		this.getValue = function () {
			if (element.selectedIndex === -1) { return null; }
			return element.options[this.getElement().selectedIndex].value;
		};
		
		/**
		 * Defines the value for the element. This way we can change it on the 
		 * browser to 'output' it to the user
		 * 
		 * @param {String} val
		 * @returns {undefined}
		 */
		this.setValue = function (val) {
			var options = Array.prototype.slice.call(element.options, 0);
			element.selectedIndex = options.indexOf(element.querySelector('[value="' + val + '"]'));
		};
		
		this.for = function() {
			return [element.getAttribute('data-for')];
		};
		
		this.parent = function(view) {
			this.view = view;
			return this;
		};
		
		this.refresh = function () {
			var options = Array.prototype.slice.call(element.options, 0);
			element.selectedIndex = options.indexOf(element.querySelector('[value="' + this.view.get(this.for()[0]) + '"]'));
		};
	};
	
	var findAdapters = function (element) {
		
		if (element.tagName.toLowerCase() === "select") {
			return [new SelectAdapter(element)];
		}
		
		return [];
	};
	
	return {
		SelectAdapter : SelectAdapter,
		find : findAdapters
	};
	
});