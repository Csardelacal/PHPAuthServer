

(function () {
	"use strict";

	/*
	 * The states that a Promise can receive are defined by the promises spec,
	 * it can either be pending, rejected or fulfilled.
	 * 
	 * @type Number
	 */
	var STATE_PENDING = 0,
		 STATE_FULFILLED = 1,
		 STATE_REJECTED = -1;

	/**
	 * Promise constructor. Creates a new promise that will be executing the task
	 * and providing the task a resolve and reject function to fulfill the promise.
	 * 
	 * @param {Function} task The task to be completed to fulfill the promise
	 * @returns {undefined}
	 */
	function Promise(task) {

		/**
		 * The state indicates whether a promise was fulfilled, rejected or is still
		 * waiting for it's task to complete.
		 */
		this._state = STATE_PENDING;
		
		/**
		 * The suitor array is the list of "then" that are waiting for the action
		 * to be finished / rejected and to be triggered with the result.
		 */
		this._suitors = [];
		
		/**
		 * The task we're waiting to be completed to fulfill our promise. This task
		 * is asynchronous, so once it is finished we will resume the promise handling.
		 */
		this._task = task;
		
		/**
		 * The result of the promise's task. This could also happen to be the reason
		 * for the rejection.
		 */
		this._result = undefined;

		/*
		 * Once everything is set up, we run the task itself. Once the function
		 * calls resolve / reject we notify all the "then".
		 */
		if (isFunction(task)) {
			task(bind(this.resolve, this), bind(this.reject, this));
		}
	}
	
	/**
	 * Registers a new then / suitor for the promise. Once the promise has been 
	 * fulfilled the functions registered with this function will be executed.
	 * 
	 * @param {Function|undefined} success
	 * @param {Function|undefined} failure
	 * @returns {promise_L3.Promise}
	 */
	Promise.prototype.then = function (success, failure) {
		var suitor = new Suitor(success, failure);
		this._suitors.push(suitor);
		
		if (this._state !== STATE_PENDING) {
			
			try {
				var method = this._state === STATE_FULFILLED? '_success' : '_failure';
				suitor[method](this._result);
			} catch (e) {
				suitor._promise.reject(e);
			}
		}
		
		return suitor._promise;
	};
	
	/**
	 * This is a convenience method for the error handling in case you'd rather not
	 * use then(undefined, function() {})
	 * 
	 * @param {Function} fn
	 * @returns {promise_L3.Promise}
	 */
	Promise.prototype['catch'] = function (fn) {
		return this.then(undefined, fn);
	};
	
	/**
	 * The reject method is called if the task the promise holds was not able to 
	 * be fulfilled, and instead returned an error code.
	 * 
	 * @param {type} message
	 * @returns {undefined}
	 */
	Promise.prototype.reject = function (message) {
		if (this._state !== STATE_PENDING) { return; }
		
		this._state = STATE_REJECTED;
		this._result = message;
		
		for (var i = 0; i < this._suitors.length; i++) {
			try {
				this._suitors[i].reject(this._result);
			} catch (e) {
				this._suitors[i]._promise.reject(e);
			}
		} 
	};
	
	Promise.prototype.resolve = function (result) {
		
		/*
		 * A promise cannot resolve itself.
		 */
		if (result === this) { 
			throw new TypeError('A promise cannot resolve itself'); 
		}
		
		/*
		 * If the promise is already fulfilled, we do not continue
		 */
		if (this._state !== STATE_PENDING) { return; }
		
		/*
		 * If result is a promise, we will wait for that promise to be completed /
		 * rejected before we resolve / reject with the exact same result.
		 * 
		 * The promise should not indefinitely return new promises as a result, but
		 * if it does this code will not detect it and probably kill the host with
		 * an inifinite loop.
		 */
		if (result instanceof Promise) {
			var ctx = this;
			result.then(function (r) { return ctx.resolve(r); }, function (r) { return ctx.reject(r); });
			return;
		}
		
		if (isObject(result) || isFunction(result)) {
			try {
				var then = result.then;
				if (isFunction(then)) { 
					return then.call(result, bind(this.resolve, this), bind(this.reject, this));
				};
			} 
			catch (e) {
				this.reject(e);
			}
		} 
		
		return this.fulfill(result);
		
	};
	
	Promise.prototype.fulfill = function(result) {
		
		this._state = STATE_FULFILLED;
		this._result = result;
		
		for (var i = 0; i < this._suitors.length; i++) { 
			
			if (!isFunction(this._suitors[i]._success)) {
				this._suitors[i]._promise.resolve(this._result);
				continue;
			}
			
			try {
				var r = this._suitors[i]._success(this._result);
				var next = this._suitors[i]._promise;
				nextTick( function() { next.resolve(r); });
			} catch (e) {
				var e = e;
				var next = this._suitors[i]._promise;
				nextTick( function() { next.reject(e); });
			}
		}
	};
	
	
	function Suitor(success, failure) {
		this._success = once(success);
		this._failure = once(failure);
		this._promise = new Promise();
	}


	/*
	 * HELPERS. This functions make some tasks a bit less tedious.
	 */
	function isFunction(fn) {
		return typeof fn === 'function';
	}

	function isObject(o) {
		return typeof o === 'object';
	}
	
	
	function bind(fn, thisParam) {
		return function () {
			return fn.apply(thisParam, arguments);
		};
	}
	
	function once(fn) {
		return function () {
			var used = false;

			if (used) { return; }
			used = true;
			return fn.apply(undefined, arguments);
		};
	}
	
	/**
	 * Executes a promise on the process' next tick. Since this is a browser based
	 * implementation, the only thing I know of in this scope is setTimeout(0) to
	 * clean the call stack.
	 * 
	 * @param {Function} fn
	 * @returns {undefined}
	 */
	function nextTick(fn) {
		setTimeout(fn, 0);
	}
	
	
	/*
	 * This is a polyfill, it's only necessary when the browser does not support
	 * the technology natively.
	 */
	if (!"Promise" in window) { 
		window.Promise = Promise;
	}
	
	/*
	 * We always locate it in the m3w vendor namespace. This way, if one wishes to
	 * use the polifill regardless to ensure that his code is portable he can do
	 * that.
	 */
	if (window.m3w === undefined) { window.m3w = {}; }
	window.m3w.Promise = Promise;

}());
