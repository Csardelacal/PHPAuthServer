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

depend(['m3/core/collection'], function(collection) {
	
	
	
	/**
	 * 
	 * @param {string} name
	 * @param {string} value
	 * @returns {lysine_L11.AttributeAdapter}
	 */
	function AttributeAdapter(element, name, value) {
		
		this.element  = element;
		this.name     = name;
		this.value    = value;
		this.adapters = this.makeAdapters();
		this.view     = undefined;
		
		this.setData  = function (data) {
			for (var i = 0; i < this.adapters.length; i++) {
				this.adapters[i].setValue(data[this.adapters[i].getName()]);
			}
		};
		
		this.replace  = function () {
			var str = '';
			
			for (var i = 0; i < this.adapters.length; i++) {
				str+= this.adapters[i].replace();
			}
			
			return str;
		};
	}
	
	AttributeAdapter.prototype = {
		hasLysine: function () { 
			return this.name.search(/^lysine/) !== -1; 
		},
		
		getAttributeName: function() {
			return this.name.replace(/^lysine/, '').toLowerCase();
		},
		
		makeAdapters: function () {
			if (!this.hasLysine()) { return []; }
			
			var exp1 = /\{\{([A-Za-z0-9]+)\}\}/g;
			var exp2 = /\{\{[A-Za-z0-9]+\}\}/g;
			
			var adapters = [];
			
			var pieces = this.value.split(exp2);
			var m      = exp1.exec(this.value);
			while (m) {
				adapters.push(new AttributeVariableAdapter(pieces.shift(), true));
				adapters.push(new AttributeVariableAdapter(m[1], false));
				//Continue the loop
				m = exp1.exec(this.value);
			}
			
			if (pieces.length > 0) { adapters.push(new AttributeVariableAdapter(pieces.shift(), true)); }
			
			return adapters;
		},
		
		for: function () {
			var ret = collection([]);
			
			collection(this.adapters).each(function(e) {
				if (!e.isReadOnly()) { ret.push(e.getName()); }
			});
			
			return ret.raw();
		},
		
		parent : function (view) {
			this.view = view;
			return this;
		},
		
		refresh : function () {
			var self = this;
			collection(this.adapters).each(function(e) { 
				if (e.isReadOnly()) { return; }
				e.setValue(self.view.get(e.getName()));
			});
			
			this.element.setAttribute(
				this.getAttributeName(), 
				this.replace()
			);
		}
	};
	
	function AttributeVariableAdapter(name, readonly) {
		var value = null;
		
		this.setValue = function (v) {
			value = v;
		};
		
		this.getValue = function () {
			return value;
		};
		
		this.getName  = function () {
			return name;
		};
		
		this.isReadOnly  = function () {
			return readonly;
		};
		
		this.replace  = function () {
			if (readonly) { return name; }
			else          { return value; }
		};
	}
	
	var findAdapters = function (element) {
		
		var dataset = element.dataset,
		    i, adapters = collection([]);
		
		for (var i in dataset) {
			if (element.dataset.hasOwnProperty(i)) {
				adapters.push(new AttributeAdapter(element, i, element.dataset[i]));
			}
		}
		
		var ret = adapters.filter(function(e) {
			return e.hasLysine();
		});
		
		return ret;

	};
	
	return {
		AttributeAdapter : AttributeAdapter,
		find : findAdapters
	};

});	
