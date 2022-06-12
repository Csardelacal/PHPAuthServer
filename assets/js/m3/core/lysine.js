/*jslint browser:true */
/*global HTMLElement*/

/*
 * First thing first. If we do not have access to any HTMLElement class it implies
 * that lysine can't work properly since it manipulates these elements.
 */
if (HTMLElement === undefined) { throw 'Lysine requires a browser to work. HTMLElement class was not found'; }
if (window      === undefined) { throw 'Lysine requires a browser to work. Window variable was not found'; }

depend([
	'm3/core/collection',
	'm3/core/lysine/inputAdapter',
	'm3/core/lysine/selectAdapter',
	'm3/core/lysine/htmlAdapter',
	'm3/core/lysine/attributeAdapter'
], 
function (collection, input, select, htmlAdapter, attributeAdapter) {
	"use strict";
	
	function ArrayAdapter(view) {
		this.views = [];
		this.base  = view;
		this.parentView = undefined;

		this.getValue = function () {
			var ret = [],
				 i;

			for (i = 0; i < this.views.length; i+=1) {
				ret.push(this.views[i].getValue());
			}
			return ret;
		};

		this.setValue = function (val) {

			var i, v;

			if (val === undefined) {
				return;
			}
			
			/*
			 * In this scenario, we have more views than necessary and need to get 
			 * rid of some. We first loop over the array to remove them from the 
			 * HTML (destroy them). Then we slice the array with them in it.
			 */
			for (i = val.length; i < this.views.length; i+=1) {
				this.views[i].destroy();
			}
			
			this.views = this.views.slice(0, val.length);
			
			/*
			 * In the event of the views not being enough to hold the data, we will
			 * add new views.
			 */
			for (i = this.views.length; i < val.length; i+=1) {
				v = new lysine(this.base);
				this.views.push(v);
				
				//Create a gettter so we can read the data
				this.makeGetter(i);
			}
			
			for (i = 0; i < val.length; i++) {
				this.views[i].setValue(val[i]);
			}
			
		};
		
		this.makeGetter = function (idx) {
			var ctx = this;
			
			Object.defineProperty(this, idx, {
				get: function () { return ctx.views[idx]; },
				configurable: true
			});
			
		};
		
		this.for = function() {
			return [this.base.getAttribute('data-for')];
		};
		
		this.parent = function(v) {
			this.parentView = v;
			return this;
		};
		
		this.refresh = function () {
			this.setValue(this.parentView.get(this.for()[0]));
		};
	}
	
	function Condition(expression, element, adapters) {
		var exp = /([a-zA-Z_0-9]+)\(([a-zA-Z_0-9\-]+)\)\s?(\=\=|\!\=)\s?(.+)/g;
		var res = exp.exec(expression);
		
		var fn = res[1];
		var id = res[2];
		var comp = res[3];
		var tgt = res[4];
		
		var view = undefined;
		
		var parent = element.parentNode;
		var nextSib = element.nextSibling;
		
		this.isVisible = function () {
			var val = undefined;
			
			switch(fn) {
				case 'null':
					val = view.get(id) === null? 'true' : 'false';
					break;
				case 'count':
					val = !view.get(id)? 0 : view.get(id).length;
					console.log(val);
					break;
				case 'value':
					val = view.get(id);
					break;
			}
			
			return comp === '=='? val == tgt : val != tgt;
		};
		
		this.test = function () {
			var visible = this.isVisible();
			
			if (visible === (element.parentNode === parent)) {
				return;
			}
			
			if (visible) {
				parent.insertBefore(element, nextSib);
			}
			else {
				parent.removeChild(element);
			}
		};
		
		this.for = function() {
			var c = collection([]);
			adapters.each(function (e) { c.merge(e.for()); });
			
			return c.raw();
		};
		
		this.parent = function(v) {
			view = v;
			adapters.each(function(e) { e.parent(v); });
			return this;
		};
		
		this.refresh = function () {
			this.test();
			
			if (this.isVisible()) {
				console.log(res);
				adapters.each(function(e) { e.refresh(); });
			}
		};
	}

	/**
	 * Creates a new Lysine view that handles the user's HTML and accepts objects as
	 * data to fill in said HTML. 
	 * 
	 * Beware of the following: IDs will potentially not properly work inside Lysine.
	 * Lysine maintains several copies of the original node and will potentially 
	 * create issues. You should dinamically generate ID to use with your objects.
	 * 
	 * @param {HTMLElement|String} id
	 * @returns {lysine_L11.lysine}
	 */
	function lysine(id) {
		
		var view, 
			 html,
			 data = {};
		
		
		/*
		 * First we receive the id and check whether it is a string or a HTMLElement
		 * this way we can handle several types of arguments received there.
		 */
		if (id instanceof HTMLElement) { view = id; } 
		else { view = document.querySelector('*[data-lysine-view="'+ id +'"]'); }
		
		/*
		 * Make a deep copy of the node. This allows Lysine to create as many copies
		 * of the original without causing trouble among the copies.
		 */
		html = view.cloneNode(true);
		
		this.set = function (k, v) {
			data[k] = v;
			
			this.adapters.each(function(e) {
				if (e.for().indexOf(k) === -1) { return; }
				e.refresh();
			});
		};
		
		this.get = function (k) {
			var ret = data;
			var pieces = k.split('.');
			
			for (var i = 0; i < pieces.length; i++) { ret = ret[pieces[i]]; }
			return ret;
		};

		/**
		 * Defines the data that we're gonna be using for the view. This way the 
		 * application can quickly pass a big amount of data to the view.
		 *
		 * @todo Remove the data variable that is not currently needed.
		 * @param {Object} newData
		 * @returns {undefined}
		 */
		this.setData = function (newData) {
			data = newData;
			
			this.adapters.each(function(e) {
				e.refresh();
			});
		};
		
		this.getData = function () {
			return data;
		};

		this.getValue = this.getData;
		this.setValue = this.setData;

		this.fetchAdapters = function (parent) {
			//Argument validation
			parent = (parent !== undefined)? parent : html;

			var adapters = collection([]), self = this;
			
			collection(parent.childNodes).each(function (e) {
				var extracted = collection([]);
				
				if (e.nodeType === 3) {
					return;
				}
				
				if (e.getAttribute && e.getAttribute('data-for')) {
					
					/*
					 * Array adapters may not be overridden in multiple places, it just
					 * makes little to no sense to have that feature.
					 */
					if (e.hasAttribute('data-lysine-view')) {
						extracted.merge(collection([(new ArrayAdapter(e)).parent(self)]));
					}
					else {
						/*
						 * This needs some fixing. The issue is that the system returns
						 * an array of adapters for a given value, which is okay, but
						 * the system cannot handle having multiple adapters for one 
						 * property.
						 */
						var adapter = collection([]).merge(input.find(e)).merge(select.find(e)).merge(htmlAdapter.find(e));
						extracted.merge(adapter.each(function (e) { return e.parent(self); }));
					}
				}
				else {
					extracted.merge(self.fetchAdapters(e));
				}
				
				/*
				 * Get the adapters for the attributes, then informt them that the parent
				 * for them is this view and attach them to the attributes.
				 */
				extracted.merge(attributeAdapter.find(e).each(function (e) { return e.parent(self); }));
				
				if (e.getAttribute && e.getAttribute('data-condition')) {
					var c = new Condition(e.getAttribute('data-condition'), e, extracted);
					adapters.push(c.parent(self));
				}
				else {
					adapters.merge(extracted);
				}
			});
			
			return adapters;
		};

		this.getHTML = function () {
			return html;
		};

		this.getElement = this.getHTML;

		this.destroy = function () {
			html.parentNode.removeChild(html);
			return this;
		};

		//Constructor tasks
		html.removeAttribute('data-lysine-view');
		this.adapters = this.fetchAdapters();
		view.parentNode.insertBefore(html, view);
	}
	
	/*
	 * Return the entry point so other pieces of the application may be able to 
	 * use Lysine.
	 */
	return {
		view : lysine
	};
});

/*
 * We do not wish any of the view templates to be displayed by the browser. Furthermore,
 * the application should attempt to cache and strip the elements from the DOM so
 * no stray data gets into other code that accesses the DOM.
 */
(function() {
	//Hide the unneeded view prototypes
	var style = document.createElement('style');
	style.type = "text/css";
	style.innerHTML = "*[data-lysine-view] { display: none !important;}";
	document.head.appendChild(style);
}());