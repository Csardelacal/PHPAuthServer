
/**
 * Allows for dropdown style autocompletion of inputs, providing select style
 * validation of the result. This means that the system assumes that the content
 * of the field must stem from an enumerated set of options.
 * 
 * The application can control the autocomplete by providing a callable that will
 * be executed whenever the user enters text and that is supposed to return an
 * object with entry objects that will be used to store the value and display to 
 * the user respectively.
 * 
 * For example, the list:
 * <code>
 * [
 *   new Entry('US', 'United states', {}),
 *   new Entry('DE', 'Germany', {})
 * ]
 * </code>
 * 
 * Would be used to show the user two options (United states and Germany), but when
 * the user clicks on 'United states' the value 'US' is recorded as the value of 
 * the field.
 * 
 * The third parameter to Entry can be used to provide a dataset that should be 
 * recorded with the entry. For example, for a country we can pass the coordinates,
 * and for stuff like tags we could pass the color the user assigned to them.
 * 
 * @todo should support empty states.
 * @todo should support adding non-entry type views to the list.
 */
depend(['m3/core/collection', 'ui/dropdownlist', 'ui/shadowinput', 'ui/keyboard'], function (collect, dropdown, Shadow, Keyboard) {
	
	const KEY_ENTER = 13;
	const KEY_TAB   =  9;
	const KEY_ESC   = 27;
	const KEY_UP    = 38;
	const KEY_DOWN  = 40;
	
	
	var extend = function (obj, withObj) {
		for (var i in withObj) {
			if (!withObj.hasOwnProperty(i)) { continue; }
			obj[i] = withObj[i];
		}
	};
	
	/*
	 * Main autocomplete constructor.
	 * 
	 * Creates the autocomplete box and registers all of the event listeners needed
	 * to interact with the user.
	 * 
	 * The element it receives as parameter should be a hidden input
	 * 
	 * @todo If the input is not hidden by default, the system should hide it
	 * @todo This function is massive, and should be broken apart to ensure that
	 * it's performing properly.
	 * 
	 * My suggestion here would be to have something like:
	 * - List (with the entries)
	 * - Input (where the user inputs data and the system presents suggestions / select)
	 * - Output (where the system places the selected value)
	 * 
	 */
	var Autocomplete = function (element, callable) {
		//Register listeners
		//Provide a callback
		//Provide mechanism to unregister the listeners
		var self = this;

		this.element = element;
		this.anchor  = document.createElement('div');
		this.allowUndefined = false;

		this.dummy = new Shadow(this.element.getAttribute('data-placeholder'), function (input, output) {
			callable(input, function (result) {
				//Result is expected to be an object of keys and strings
				//Or an array
				self.draw(result);
				console.log(result[0]);
				var shadow;
				
				self.drop.show()
				
				if (!result[0]) { output(''); return; }
				else if (result[0].value.substr(0, input.length).toLowerCase() === input.toLowerCase()) { shadow = result[0].value.substr(input.length); }
				else { shadow = '(' + result[0].value + ')'; }
				
				output(shadow);
			}, function (v, k, m) { return dropdown.entry(k, v, m); });
			
			if (self.element.value !== '') {
				self.element.value = '';
				self.element.dispatchEvent(new CustomEvent('change', {'bubbles': false, 'cancelable': true}));
			}
		});
		
		this.drop = dropdown.list(this.anchor, function (entry) {
			//This only will work for the clickable ones, the system needs to handle keyboard input separately
			self.dummy.set(entry.value);
			self.dummy.suggest('');
			
			self.element.value = entry.key;
			extend(self.element.dataset, entry.meta);
			
			try {
				var evt = new Event('change', {'bubbles': false, 'cancelable': true});
			}
			catch (ex) {
				//IE Specific fix
				var evt = document.createEvent("Event");
				evt.initEvent("change", false, true);
			}
			
			self.element.dispatchEvent(evt);
			self.drop.clear();
		});
		
		this.callable = callable;
		this.active = undefined;
		
		this.dummy.onapply = function () {
			var entry = self.drop.get(0).entry;
			console.log(entry);
			self.element.value = entry.key;
			extend(self.element.dataset, entry.meta);
			self.element.dispatchEvent(new Event('change', {'bubbles': false, 'cancelable': true}));
			self.drop.clear();
			
			this.suggest('');
			this.set(entry.value);
		};
		
		this.keyboard = Keyboard.listen(this.dummy.inner);
		
		this.keyboard.up(Keyboard.keys.arrowDown, function () {});
		this.keyboard.down(Keyboard.keys.arrowDown, function () {
			console.log('Keyboard down');
			self.drop.next();
		});
		
		this.keyboard.up(Keyboard.keys.arrowUp, function () {});
		this.keyboard.down(Keyboard.keys.arrowUp, function () {
			console.log('Keyboard up');
			self.drop.previous();
		});
		
		this.keyboard.down(Keyboard.keys.enter, function () {
			if (self.element.value) {
				return;
			}
			
			if (!self.drop.getHighlighted()) {
				var entry = self.drop.get(0).entry;
			} 
			else {
				var entry = self.drop.getHighlighted().entry;
			}
			
			console.log(entry);
			self.element.value = entry.key;
			extend(self.element.dataset, entry.meta);
			self.element.dispatchEvent(new Event('change', {'bubbles': false, 'cancelable': true}));
			self.drop.clear();
			
			self.dummy.suggest('');
			self.dummy.set(entry.value);
		});
		
		this.anchor.className = 'autocomplete-results-anchor';

		element.parentNode.insertBefore(this.dummy.outer,element);
		element.parentNode.insertBefore(this.anchor,element);
		
		document.addEventListener('click', function (e) {
			if (self.allowUndefined) { self.drop.hide(); return; }
			if (self.drop.get(0) === undefined) { return; }
			var entry = self.drop.get(0).entry;
			
			self.element.value = entry.key;
			extend(self.element.dataset, entry.meta);
			self.element.dispatchEvent(new Event('change', {'bubbles': false, 'cancelable': true}));
			self.drop.hide();
			
			self.dummy.set(entry.value);
			self.dummy.suggest('');
			self.dummy.set(entry.value);
		});

		this.dummy.inner.addEventListener('click', function (e) {
			callable(this.value, function (result) {
				//Result is expected to be an object of keys and strings
				//Or an array
				self.draw(result);
			}, function (v, k, m) { return dropdown.entry(k, v, m); });

			e.stopPropagation();
		}, false);

		document.addEventListener('click', function () {
			self.drop.clear();
		});
	};
	
	Autocomplete.prototype = {
		
		/*
		 * Resets the input and clears the result list. We generally use this in a
		 * scenario where a user can add multiple values, once the user confirms 
		 * their input, we store the data and reset the input.
		 */
		empty: function () {
			this.element.value = '';
			this.dummy.suggest('');
			this.dummy.set('');
			this.element.dispatchEvent(new Event('change', {'bubbles': false, 'cancelable': true}));
			
			this.drop.clear();
		},
		
		/*
		 * Renders the list.
		 */
		draw: function (result) {
			var self = this;
			this.drop.clear();
			this.drop.show();
			
			collect(result).each(function (e) {
				self.drop.put(typeof(e) === 'object'? e : dropdown.entry(e, e, {}));
			});
			
		}
	};

	return function (e, cb) { return new Autocomplete(e, cb); };

});
