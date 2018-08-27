(function () {
	var containerHTML = document.querySelector('.contains-sidebar');
	var sidebarHTML   = containerHTML.querySelector('.sidebar');
	var contentHTML   = document.querySelector('.content');

	/*
	 * Scroll listener for the sidebar______________________________________
	 *
	 * This listener is in charge of making the scroll bar both stick to the
	 * top of the viewport and the bottom of the viewport / container
	 */
	var wh  = window.innerHeight;
	var ww  = window.innerWidth;
	
	/*
	 * Collect the constraints from the parent element to consider where the 
	 * application is required to redraw the child.
	 * 
	 * @type type
	 */
	var constraints;
	
	var sidebar = {
		toggle : function () {
			containerHTML.classList.toggle('collapsed');
			scrollListener();
		},
		
		hide: function () {
			containerHTML.classList.add('collapsed');
		},
		
		show : function () {
			containerHTML.classList.remove('collapsed');
		},
		
		float : function () {
			containerHTML.classList.contains('floating') || containerHTML.classList.add('collapsed');
			containerHTML.classList.add('floating');
			containerHTML.classList.remove('persistent');
		},
		
		persistent : function () {
			containerHTML.classList.add('persistent');
			containerHTML.classList.remove('floating');
			containerHTML.classList.remove('collapsed');
		}
	};
	 
	/*
	 * This function quickly allows the application to check whether it should 
	 * consider the browser it is running in as a viewport to small to handle the
	 * sidebar and the content simultaneously.
	 * 
	 * @returns {Boolean}
	 */
	var floating = function () { 
		return ww < 1160;
	};
	
	var listener = function (element, listeners) {
		for (var i in listeners) {
			if (!listeners.hasOwnProperty(i)) { continue; }
			element.addEventListener(i, listeners[i], false);
		}
	};

	/*
	 * This helper allows the application to define listeners that will prevent
	 * the application from hogging system resources when a lot of events are 
	 * fired.
	 * 
	 * @param {type} fn
	 * @returns {Function}
	 */
	var debounce = function (fn, interval) {
	  var timeout = undefined;

	  return function () {
		  if (timeout) { return; }
		  var args = arguments;

		  timeout = setTimeout(function () {
			  timeout = undefined;
			  fn.apply(window, args);
		  }, interval || 50);
	  };
	};
	
	var enableAnimation = function (set) {
		/*
		 * During startup of our animation, we do want the browser to not animate the
		 * components... This would just cause unnecessary load and the elements to be
		 * shifted around like crazy.
		 * 
		 * @todo I don't like how this is managed. A bunch of CSS code has nothing 
		 * to do with our JS and should be moved outside of it. My suggestion to 
		 * solve the issue would be to add a "no-animation" class to the CSS of the
		 * root element. Allowing JS to toggle the animations by setting a single
		 * class on the root element.
		 */
		if (set === false) { document.body.classList.add('sb-no-animation'); }
		else               { document.body.classList.remove('sb-no-animation'); }
	};
	
	var pixels = function (n) {
		return n + 'px';
	};
	
	/**
	 * This function returns the constraints that an element fits into. This allows
	 * an application to determine whether an item is onscreen, or whether two items
	 * intersect.
	 * 
	 * Note: this function provides only the vertical offset, which is most often
	 * needed since web pages tend to grow into the vertical space more than the 
	 * horizontal.
	 * 
	 * @param {type} el
	 * @returns {ui-layoutL#1.getConstraints.ui-layoutAnonym$0}
	 */
	var getConstraints = function (el) {
		var t = 0;
		var w = el.clientWidth;
		var h = el.clientHeight;
		
		do {
			t = t + el.offsetTop;
		} while (null !== (el = el.offsetParent));
		
		return {top : t, bottom : document.body.clientHeight - t - h, width: w, height: h};
	};
	 
	/**
	 * On Scroll, our sidebar is resized automatically to fill the screen within
	 * the boundaries of the container.
	 * 
	 * @returns {undefined}
	 */
	var scrollListener  = function () { 
		
		var pageY  = window.pageYOffset;
		var maxY   = document.body.clientHeight;
		
		/**
		 * 
		 * @type Number|Window.innerHeight
		 */
		var height = floating()? wh : Math.min(wh, maxY - Math.max(pageY, constraints.top) - constraints.bottom);
		
		/*
		 * This flag determines whether the scrolled element is past the viewport
		 * and therefore we need to "detach" the sidebar so it will follow along
		 * with the scrolling user.
		 * 
		 * @type Boolean
		 */
		var detached = constraints.top < pageY;
		var collapsed = containerHTML.classList.contains('collapsed');
		
		sidebarHTML.style.height   = pixels(height);
		sidebarHTML.style.width    = floating() && collapsed? 0 : pixels(200);
		sidebarHTML.style.top      = floating()? 0 : pixels(Math.max(0, constraints.top - pageY ));
		sidebarHTML.style.position = detached || floating()?   'fixed' : 'static';
		
		contentHTML.style.width    = floating() || collapsed? '100%' : pixels(constraints.width - 200);

		containerHTML.style.top    = floating()? pixels(0) : null;
		
	};

	var resizeListener  = function () {
		
		/*
		 * During startup of our animation, we do want the browser to not animate the
		 * components... This would just cause unnecessary load and the elements to be
		 * shifted around like crazy.
		 */
		enableAnimation(false);
		setTimeout(function () { enableAnimation(true); }, 100);
		
		//Reset the size for window width and height that we collected
		wh  = window.innerHeight;
		ww  = window.innerWidth;
		
		
		/**
		 * We ping the scroll listener to redraw the the UI for it too.
		 */
		constraints = getConstraints(containerHTML.parentNode);
		scrollListener();
		
		//For mobile devices we toggle to collapsable mode
		if (floating()) { sidebar.float(); } 
		else            { sidebar.persistent(); }
		
		containerHTML.parentNode.style.whiteSpace = 'nowrap';
	 };
	
	/*
	 * Create listeners that allow the application to react to events happening 
	 * in the browser.
	 */
	listener(window, {
		resize: debounce(resizeListener),
		load: resizeListener
	});
	
	listener(document, {
		scroll: debounce(scrollListener, 25),
		click: function(e) { 
			if (!e.target.classList.contains('toggle-button')) { return; }
			sidebar.toggle();
		}
	});
	
	listener(containerHTML, {
		click: sidebar.hide
	});
	
	listener(sidebarHTML, {
		click: function(e) { e.stopPropagation(); }
	});
	
}());
