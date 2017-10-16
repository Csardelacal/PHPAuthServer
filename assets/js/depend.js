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
	var baseURL = '';

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
		if (window.addEventListener) {
			return src.addEventListener(evt, callback, false);
		}
		if (window.attachEvent) {
			return src.attachEvent('on' + evt, callback);
		}

		//This will locate a onLoad, for example, and stack it. Should provide fallback
		//even for the most primitive of browsers.
		var attr = 'on' + evt;
		var prev = src[attr] !== undefined ? src[attr] : null;
		src[attr] = function (e) {
			return callback(e) !== false && (!prev || prev(e));
		};
	}

	function getModuleByName(name) {
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

	function DependencyLoader(dependencies, callback) {

		var total = dependencies.length;
		var progress = 0;
		var loaded = [];

		for (var i = 0; i < total; i++) {
			if (getModuleByName(dependencies[i])) {
				var module = getModuleByName(dependencies[i]);
				loaded[dependencies.indexOf(module.getName())] = module.getCallable();
				progress++;
				continue;
			}

			if (isQueued(dependencies[i])) {
				on(isQueued(dependencies[i]), 'load', function (e) {
					var module = getModuleByName(e.target.getAttribute('data-src'));
					module.onReady(function () {
						loaded[dependencies.indexOf(module.getName())] = module.getCallable();
						progress++;
						if (progress === total) {
							callback(loaded);
						}
					});
				});

				continue; //The script was already added, we don't need to do anything else
			}

			/*
			 * We create a script tag so the user gets a feeling for what he imported.
			 * This allows the browser to expose proper debugging.
			 *
			 * @type @exp;document@call;createElement
			 */
			var script = document.createElement('script');
			script.src = baseURL + dependencies[i] + '.js';
			script.async = true;
			script.type = 'text/javascript';
			script.setAttribute('data-src', dependencies[i]);

			on(script, 'load', function (e) {
				var result = last? last : getModuleByName(e.target.getAttribute('data-src'));
				//We just received the onload event for the script the browser was compiling.
				//This means we can use the script's name to address the module it just compiled
				result.setName(e.target.getAttribute('data-src'));

				//Drop the module we were loading from the list of modules we're waiting for
				pending.splice(pending.indexOf(this), 1);
				last = null;

				result.onReady(function () {
					loaded[dependencies.indexOf(result.getName())] = result.getCallable();

					progress++;
					if (progress === total) {
						callback(loaded);
					}
				});
			});

			document.head.appendChild(script);
			pending.push(script);
		}

		if (total === progress) {
			return callback(loaded);
		}
	}

	function Module(name, dependencies, definition) {

		var self = this;

		this.name = name;
		this.callable = null;
		this.listeners = [];

		this.init = function () {
			new DependencyLoader(dependencies, function (deps) {
				self.callable = definition.apply(null, deps);
				self.onReady();
			});
		};

		this.onReady = function (param) {
			if (param) {
				this.listeners.push(param);
			}

			if (this.callable) {
				for (var i = 0; i < this.listeners.length; i++) {
					this.listeners[i].call();
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
		module.init();
		
		return module;
	}

	/*
	 * Export the appropriate variable to the browser's context. This allows the
	 * developer to use the class.
	 */
	window.depend = depend;
	window.depend.setBaseURL = function(url) { baseURL = url; };
	
}());
