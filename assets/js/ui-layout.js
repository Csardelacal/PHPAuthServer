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
	
	var animations = false;
	
	/*
	 * Collect the constraints from the parent element to consider where the 
	 * application is required to redraw the child.
	 * 
	 * @type type
	 */
	var constraints;
	 
	/*
	 * This function quickly allows the application to check whether it should 
	 * consider the browser it is running in as a mobile viewport.
	 * 
	 * @returns {Boolean}
	 */
	var mobile = function () {
		return ww < 1160;
	};
	
	
	var floating = function () { 
		return mobile();// || containerHTML.classList.contains('always-float'); 
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
		if (animations === set) { return; }
		
		animations = set;
		
		/*
		 * During startup of our animation, we do want the browser to not animate the
		 * components... This would just cause unnecessary load and the elements to be
		 * shifted around like crazy.
		 */
		if (set === false) {
			contentHTML.style.transition = null;
			containerHTML.style.transition = null;
			sidebarHTML.style.transition = null;
		}
		else {
			contentHTML.style.transition = '.2s width .3s ease-in'; 
			containerHTML.style.transition = '.2s opacity .3s ease-in, .2s width .3s ease-in';
			sidebarHTML.style.transition = 'left .2s ease-in, .2s opacity .3s ease-in, .2s width .3s ease-in';
		}
	};
	
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
		 * @todo There's a minus 1 "magic number" in there. For some reason, the code
		 *       seems to be misscalculating the amount of pixels it has between the
		 *       top and the bottom of the page. The issue is that I cannot currently
		 *       pinpoint the source of the issue, and the issue is minor enough that
		 *       it doesn't warrant investing the time to properly address it for now.
		 * @type Number|Window.innerHeight
		 */
		var height = floating()? wh : Math.min(wh, maxY - pageY - constraints.bottom) - Math.max(constraints.top - pageY, 0) - 1;
		
		/*
		 * This flag determines whether the scrolled element is past the viewport
		 * and therefore we need to "detach" the sidebar so it will follow along
		 * with the scrolling user.
		 * 
		 * @type Boolean
		 */
		var detached = constraints.top < 0;
		var collapsed = containerHTML.classList.contains('collapsed');
		
		sidebarHTML.style.height   = height + 'px';
		sidebarHTML.style.width    = floating()? (collapsed? 0 : '240px') : '200px';
		sidebarHTML.style.top      = detached || floating() || constraints.top < pageY?   '0px' : Math.max(0, constraints.top - pageY ) + 'px';
		sidebarHTML.style.position = detached || floating() || constraints.top < pageY?   'fixed' : 'static';
		
		contentHTML.style.width    = floating() || collapsed? '100%' : (constraints.width - 200) + 'px';

		containerHTML.style.top    = detached || floating()?   '0px' : null;
		
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
		if (floating()) {
			containerHTML.classList.contains('floating') || containerHTML.classList.add('collapsed');
			containerHTML.classList.add('floating');
			containerHTML.classList.remove('persistent');
		} 
		else {
			containerHTML.classList.add('persistent');
			containerHTML.classList.remove('floating');
			containerHTML.classList.remove('collapsed');
		}
		
		containerHTML.parentNode.style.whiteSpace = 'nowrap';
	 };
	
	

	window.addEventListener('resize', debounce(resizeListener));
	window.addEventListener('load', resizeListener);
	document.addEventListener('scroll', debounce(scrollListener, 25));

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
