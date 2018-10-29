/* global HTMLElement */

(function () {

	var modules = [];
	var pending = [];

	/**
	 * The last module imported. If an onload comes around we will properly name
	 * it and push it to our list of sorted dependencies.
	 *
	 * @type Module
	 */
	var last = null;

	/**
	 * The base URL for the JS files to be located.
	 * 
	 * @todo Replace with a proper router for multiple locations and whatnot.
	 * @type String|url
	 */
	var router = function(e) { return e + '.js'; };

	/**
	 * Provides a standard behavior for attaching listeners to a HTMLElement 
	 * inside the library. This also provides fallbacks for browsers that do not
	 * support addEventListener or any listener at all.
	 *
	 * @param {Object}   src
	 * @param {string}   evt
	 * @param {Function} callback
	 * @returns {undefined}
	 */
	function on(src, evt, callback) {
		
		/*
		 * If the browser supports addeventlistener we can stop right there, since
		 * we do already have support for listeners the way we want them.
		 */
		if (window.addEventListener && src instanceof HTMLElement) {
			return src.addEventListener(evt, callback, false);
		}
		
		/*
		 * Old versions (ancient by now) do support the attachEvent alternative to
		 * addEventlistener, while it is essentially identical, it has a different
		 * syntax.
		 */
		if (window.attachEvent && src instanceof HTMLElement) {
			return src.attachEvent('on' + evt, callback);
		}

		/*
		 * This will locate a onLoad, for example, and stack it. Should provide fallback
		 * even for the most primitive of browsers.
		 * 
		 * Using this, we can also generate stacked events for any objects that are
		 * not HTMLElements and therefore do not support addEventlistener.
		 */
		var attr = 'on' + evt;
		var prev = src[attr] !== undefined ? src[attr] : null;
		src[attr] = function (e) {
			return callback(e) !== false && (!prev || prev(e));
		};
	}

	function available(name) {
		for (var i = 0; i < modules.length; i++) {
			if (modules[i].getName() === name) {
				return modules[i];
			}
		}

		return null;
	}

	function isQueued(script) {
		for (var i = 0; i < pending.length; i++) {
			if (pending[i].getAttribute('data-src') === script) {
				return pending[i];
			}
		}
	}
	
	function script(src) {
		/*
		 * We create a script tag so the user gets a feeling for what he imported.
		 * This allows the browser to expose proper debugging.
		 *
		 * @type @exp;document@call;createElement
		 */
		var script = document.createElement('script');
		script.src = router(src);
		script.async = true;
		script.type = 'text/javascript';
		script.setAttribute('data-src', src);
		
		return script;
	}
	
	function Dependency(loader, identifier) {
		
		this.callable = undefined;
		
		this.load = function () {
			var self = this;
			
			if (available(identifier)) {
				available(identifier).onReady(function() {
					self.callable = this.getCallable();
					loader.notify();
				});
				return;
			}
			
			if (isQueued(identifier)) {
				var tag = isQueued(identifier);
			}
			else {
				var tag = script(identifier);
			}
			
			on(tag, 'load', function (e) {
				/*
				 * This function is called once per module awaiting this script's end,
				 * which implies that the first listener will basically "consume" last
				 * and therefore, subsequent listeners will have to retrieve the 
				 * appropriate module.
				 */
				var module = last? last : available(e.target.getAttribute('data-src'));
				
				//We just received the onload event for the script the browser was compiling.
				//This means we can use the script's name to address the module it just compiled
				module.setName(e.target.getAttribute('data-src'));
				
				/*
				 * Drop the module we were loading from the list of modules we're waiting for
				 */
				if (pending.indexOf(this) !== -1) {
					pending.splice(pending.indexOf(this), 1);
					last = null;
				}
				
				/*
				 * Since we now have a module we can basically apply to be notified
				 * once the module is compiled and ready to be used.
				 * 
				 * When the module is ready, the dependency will retrieve it's callable
				 * (which is not required to be a function) and notifies the loader
				 * that the dependency has been resolved.
				 */
				module.onReady(function () {
					self.callable = module.getCallable();
					loader.notify();
				});
			});
			
			on (tag, 'error', function (e) {
				console.error('Error loading module ' + e.target.getAttribute('data-src'));
				console.log('Dependency loading failed. Depending modules will not be initialized.');
				
				if (pending.indexOf(this) !== -1) {
					pending.splice(pending.indexOf(this), 1);
					last = null;
				}
			});
			
			if (!tag.parentNode) {
				document.head.appendChild(tag);
				pending.push(tag);
			}
		};
	}

	function DependencyLoader(d, callback) {
		
		
		var dependencies = d.map(function (e) {
			return new Dependency(this, e);
		}, this);
		
		var total = dependencies.length;
		var progress = 0;
		
		this.notify = function () {
			progress++;
			
			if (progress === total) {
				callback(dependencies.map(function (e) { return e.callable; }));
			}
		};
		
		if (0 === total) {
			/*
			 * Since there are no dependencies, the system will need to load none and 
			 * can therefore immediately proceed to calling the provided callback.
			 */
			callback([]);
		}

		for (var i = 0; i < total; i++) {
			dependencies[i].load();
		}
	}

	function Module(name, dependencies, definition) {

		var self = this;

		this.name = name;
		this.callable = undefined;
		this.resolved = false;
		this.listeners = [];

		this.init = function () {
			new DependencyLoader(dependencies, function (deps) {
				try {
					self.callable = definition.apply(null, deps);
				} catch (e) {
					console.log('Error initializing module. Error was: ');
					console.error(e);
				}
				self.resolved = true;
				self.onReady();
			});
		};

		this.onReady = function (param) {
			if (param) {
				this.listeners.push(param);
			}
			
			if (this.resolved) {
				for (var i = 0; i < this.listeners.length; i++) {
					this.listeners[i].call(this);
				}

				this.listeners = [];
			}
		};
	}

	Module.prototype = {
		setName: function (set) {
			this.name = set;
		},

		getName: function () {
			return this.name;
		},

		getCallable: function () {
			return this.callable;
		}
	};


	function depend(name, dependencies, definition) {

		/*
		 * Check if the name is missing first. This will cause us to wait for onload
		 * to name this puppy.
		 */
		if (!definition && typeof name !== 'string') {
			definition = dependencies;
			dependencies = name;
			name = null;
		}

		/*
		 * The dependencies are also optional in JSDepend, so we're gonna keep the
		 * interface compatible.
		 */
		if (typeof dependencies === 'function') {
			definition = dependencies;
			dependencies = [];
		}
		

		/*
		 * We return a module. This object will then be named by the onload of our
		 * script when compiled.
		 */
		var module = new Module(name, dependencies, definition);
		modules.push(module);
		last = name ? null : module;
		
		/*
		 * Move the execution of the module to a clean stack trace. This prevents 
		 * the stack from becoming to unwieldy and errors from stopping the execution
		 * of properly working modules.
		 * 
		 */
		setTimeout(function() {module.init();}, 0);
		
		return module;
	}

	/*
	 * Export the appropriate variable to the browser's context. This allows the
	 * developer to use the class.
	 */
	window.depend = depend;
	window.depend.setRouter = function(r) { router = r; };
	
}());
