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


depend([], function() {
	
	
	/*
	 * This helper allows the application to define listeners that will prevent
	 * the application from hogging system resources when a lot of events are 
	 * fired.
	 * 
	 * @param {type} fn
	 * @returns {Function}
	 */
	var debounce = function (fn, interval) {
		/*
		 * The timeout variable is used to determine whether the debounce has blocked
		 * the execution of further listeners.
		 */
		var timeout = undefined;
		
		/*
		 * We cache the return value of the debounced function. For some applications,
		 * like listeners, this is rather irrelevant.
		 * 
		 * This has more application when you intend to execute a function that is 
		 * costly to the browser but it's return value is predictable enough that
		 * being run once should yield consistent results.
		 * 
		 * For example, determining the bounding box of an element is extremely taxing
		 * on the browser. But, our elements rarely move around enough to justify
		 * the calculation regularly.
		 */
		var returnv = undefined;
	  
	  return function () {
		  if (returnv === undefined) { return returnv = fn.apply(window, arguments); }
		  if (timeout) { return returnv; }
		  
		  var args = arguments;
		  var callback = function () {
			  returnv = fn.apply(window, args) || null;
			  timeout = undefined;
		  };
		  
		  if (window.requestAnimationFrame && !interval) { timeout = window.requestAnimationFrame(callback); }
		  else { timeout = setTimeout(callback, interval || 50); }
		  
		  return returnv;
	  };
	};
	
	if (window.m3w      === undefined) { window.m3w = {}; }
	if (window.m3w.core === undefined) { window.m3w.core = {}; }
	
	window.m3w.core.debounce = debounce;
	
	return debounce;
	
});