/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


depend(function () {
	
	var extend = function (obj, withObj) {
		for (var i in withObj) {
			if (!withObj.hasOwnProperty(i)) { continue; }
			obj[i] = withObj[i];
		}
	};
	
	var cssPrefix = 'dropdownlist-';
	
	/*
	 * The entry corresponds to one item in the list. The callable function can
	 * return an arbitrary amount of entries to an input.
	 * 
	 * Duplicate keys are not being checked or filtered. They are allowed, but may
	 * lead to user confusion when the same option appears twice.
	 */
	var Entry = function (key, value, meta) {
		this.key = key;
		this.value = value;
		this.meta = meta;
	};
	
	
	/*
	 * An option is the presentation of an entry. The autocomplete will construct
	 * it with itself and an entry, and will then use it to render the list to the
	 * user.
	 */
	var Option = function (ctx, entry) {
		/*
		 * The "<option" that this handles. Please note that, due to the limited
		 * styling and customization that actual select and option tags do have in
		 * most browsers, we will be reimplementing their behavior with divs.
		 */
		this.html = undefined;
		
		/*
		 * The entry this option is rendering, it will be the source of the key, value
		 * and metadata for the html.
		 */
		this.entry = entry;
		
		/*
		 * The context this entry exists in (this is only necessary to retrieve the
		 * html within the appropriate context when multiple dropdowns exist)
		 */
		this.ctx = ctx;
	};
	
	Option.prototype = {
		
		
		/*
		 * Renders the HTML of the option to the browser. It also registers the 
		 * appropriate listener, so the user's click is fed back into the system.
		 * 
		 * @todo It'd be an interesting approach to delegate the click listening
		 * to the parent, reducing the amount of listeners the system has active,
		 * this would be especially relevant when the system manages a very long
		 * list.
		 */
		render : function (wrapper) {
			/*
			 * Create the option's html to render at a later stage. Here we also create
			 * a scoped version of ctx, and entry.
			 */
			var opt = this.html = wrapper.appendChild(document.createElement('div'));
			var ctx = this.ctx, entry = this.entry;
			
			/*
			 * Feed the new DOM element with information from the entry. Here you
			 * can already target the metadata to apply styles, since the metadata
			 * is also attached to the entries.
			 */
			opt.className = cssPrefix + 'entry'; // `${cssPrefix}entry`;
			opt.innerHTML = entry.value;
			extend(opt.dataset, entry.meta);
			
			/*
			 * Create a listener to register whether the user clicked on the option 
			 * and therefore selected it.
			 * 
			 * @todo Here we should remove the micro logic, and instead create a method
			 * in the autocomplete itself to register the user's option.
			 */
			opt.addEventListener('click', function (e) {
				console.log('click');
				console.log(ctx);
				ctx.onchange(entry);
				e.stopPropagation();
				return;
			}, false);
		},
		
		/**
		 * Removes the node from the DOM, this is used whenever the system refreshes
		 * the list of elements or if the system collapses the list.
		 * 
		 * @returns {undefined}
		 */
		remove: function () {
			this.html.parentNode.removeChild(this.html);
		},
		
		/**
		 * Marks the element as active.
		 * 
		 * @todo This should be called whenever the element is hovered.
		 * @returns {undefined}
		 */
		focus: function () {
			this.html.classList.add('active');
		},
		
		/**
		 * Removes the active flag from the element. 
		 * 
		 * @returns {undefined}
		 */
		blur: function () {
			this.html.classList.remove('active');
		}
	};
	
	var List = function (parent, onchange) {
		this.container  = document.createElement('div');
		this.container.className = cssPrefix + 'list ' + cssPrefix + 'hidden'; // ${cssPrefix}list ${cssPrefix}hidden`;
		
		this.options = [];
		
		/*
		 * The highlighted option is the one that is currently selected by the user,
		 * this can be because the user is hovering the list, moving through it
		 * with a keyboard or similar.
		 * 
		 * If the value is set to undefined, it is implied that there is currently
		 * no element selected.
		 */
		this.highlighted  = undefined;
		this.onchange = onchange;
		
		/*
		 * Append the container to it's parent, this will allow the application to 
		 * position it appropriately
		 */
		parent.appendChild(this.container);
	};
	
	List.prototype = {
		hide: function () { this.container.classList.add(cssPrefix + 'hidden'); }, //`${cssPrefix}hidden`
		show: function () { this.container.classList.remove(cssPrefix + 'hidden'); },//`${cssPrefix}hidden`
		
		clear : function () {
			for (var i = 0; i < this.options.length; i++ ) { 
				this.options[i].remove(); 
			}
			this.options = [];
			this.highlighted = undefined;
		},
		
		put : function (entry) {
			var opt = new Option(this, entry);
			opt.render(this.container);
			this.options.push(opt);
		},
		
		get : function (idx) {
			return this.options[idx];
		},
		
		getHighlighted : function () {
			return this.highlighted;
		},
		
		setHighlighted : function (highlight) {
			this.highlighted = highlight;
			return this;
		},
		
		previous : function () {
			this.highlighted && this.highlighted.blur();
			var idx = this.options.indexOf(this.highlighted);
			this.highlighted = idx > 0? this.options[idx - 1] : undefined;
			this.highlighted.focus();
		},
		
		next : function () {
			console.log('next');
			//Needs default logic, if no element is active, the last one should be selected
			this.highlighted && this.highlighted.blur();
			var idx = this.options.indexOf(this.highlighted) + 1;
			this.highlighted = idx < this.options.length? this.options[idx] : undefined;
			this.highlighted.focus();
		}
	};
	
	return {
		list: function (parent, onchange) { return new List(parent, onchange); },
		entry: function (key, value, meta) { return new Entry(key, value, meta); }
	};
});
