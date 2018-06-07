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
	 * This function quickly allows the application to check whether it should 
	 * consider the browser it is running in as a mobile viewport.
	 * 
	 * @returns {Boolean}
	 */
	var mobile = function () {
		return ww < 960;
	};
	
	
	var floating = function () { 
		return mobile() || containerHTML.classList.contains('always-float'); 
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
			  fn.apply(window, args);
			  timeout = undefined;
		  }, interval || 50);
	  };
	};
	
	var enableAnimation = function (set) {
		/*
		 * During startup of our animation, we do want the browser to not animate the
		 * components... This would just cause unnecessary load and the elements to be
		 * shifted around like crazy.
		 */
		if (set === false) {
			contentHTML.style.transition = 'none';
			containerHTML.style.transition = 'none';
			sidebarHTML.style.transition = 'none';
			containerHTML.parentNode.style.whiteSpace = 'nowrap';
		}
		else {
			contentHTML.style.transition = null; 
			containerHTML.style.transition = null;
			sidebarHTML.style.transition = null;
		}
	}
	 
	 /**
	  * On Scroll, our sidebar is resized automatically to fill the screen within
	  * the boundaries of the container.
	  * 
	  * @returns {undefined}
	  */
	var scrollListener  = function () { 
		
	
		/*
		 * Collect the constraints from the parent element to consider where the 
		 * application is required to redraw the child.
		 * 
		 * @type type
		 */
		var constraints = containerHTML.parentNode.getBoundingClientRect();
		var height = floating()? wh : Math.min(wh, constraints.bottom) - Math.max(constraints.top, 0);
		
		/*
		 * This flag determines whether the scrolled element is past the viewport
		 * and therefore we need to "detach" the sidebar so it will follow along
		 * with the scrolling user.
		 * 
		 * @type Boolean
		 */
		var detached = constraints.top < 0;
		var collapsed = containerHTML.classList.contains('collapsed');
		
		containerHTML.style.height = floating()? height + 'px' : constraints.height + 'px';
		sidebarHTML.style.height   = height + 'px';
		sidebarHTML.style.width    = floating()? (collapsed? 0 : '240px') : '200px';
		contentHTML.style.width    = floating() || collapsed? '100%' : (constraints.width - 200) + 'px';
		
		containerHTML.style.top    = detached || floating()?   '0px' : Math.max(0, 0 - constraints.top) + 'px';
		sidebarHTML.style.position = detached || floating()? 'fixed' : 'static';
		
	};

	var resizeListener  = function () {
		//Reset the size for window width and height that we collected
		wh  = window.innerHeight;
		ww  = window.innerWidth;
		
		/*
		 * During startup of our animation, we do want the browser to not animate the
		 * components... This would just cause unnecessary load and the elements to be
		 * shifted around like crazy.
		 */
		enableAnimation(false);
		window.requestAnimationFrame? window.requestAnimationFrame(function() { enableAnimation(true); }) : setTimeout(function () { enableAnimation(true); }, 50);
		
		/**
		 * We ping the scroll listener to redraw the the UI for it too.
		 */
		scrollListener();
		
		//For mobile devices we toggle to collapsable mode
		if (ww < 960 + 200 || floating()) {
			containerHTML.classList.contains('floating') || containerHTML.classList.add('collapsed');
			containerHTML.classList.add('floating');
			containerHTML.classList.remove('persistent');
		} 
		else {
			containerHTML.classList.add('persistent');
			containerHTML.classList.remove('floating');
			containerHTML.classList.remove('collapsed');
		}
	 };
	

	document.addEventListener('scroll', debounce(scrollListener, 25), false);
	window.addEventListener('resize', debounce(resizeListener), false);
	window.addEventListener('load', resizeListener, false);
		
	if (!containerHTML.classList.contains('floating')) {
		containerHTML.classList.add('persistent');
	}
	else {
		containerHTML.classList.add('collapsed'); 
	}

	/*
	 * Defer the listener for the toggles to the document.
	 */
	document.addEventListener('click', function(e) { 
		if (!e.target.classList.contains('toggle-button')) { return; }
		containerHTML.classList.toggle('collapsed');
		scrollListener();
	}, false);

	containerHTML.addEventListener('click', function() { 
		containerHTML.classList.add('collapsed'); 
	}, false);
	
	sidebarHTML.addEventListener('click', function(e) { e.stopPropagation(); }, false);
}());
