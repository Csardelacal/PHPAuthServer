(function () {
	
	var fallbackToggle = function () {
		var f = document.body.appendChild(document.createElement('span'))
		f.style.position   = 'fixed';
		f.style.top        = '0';
		f.style.left       = '0';
		f.style.padding    = '10px 15px';
		f.style.background = '#2a912e';
		return f;
	}
	
	var sbc = document.querySelector('.contains-sidebar');
	var sb  = sbc.querySelector('.sidebar');
	var p   = sbc.parentNode;
	var tb  = document.querySelectorAll('.toggle-button-target').length? document.querySelectorAll('.toggle-button-target') : [fallbackToggle()]; //Toggle button

	/*
	 * Scroll listener for the sidebar______________________________________
	 *
	 * This listener is in charge of making the scroll bar both stick to the
	 * top of the viewport and the bottom of the viewport / container
	 */
	 var wh  = window.innerHeight;
	 var ww  = window.innerWidth;

	 var sl  = function () { 
		var square        = sbc.getBoundingClientRect();
		var parent        = sbc.parentNode.getBoundingClientRect();
		sb.style.height   = ww < 960? wh + 'px' : Math.min(wh, parent.bottom) - (square.top > 0? square.top : 0) + 'px';
		sbc.style.height  = ww < 960? wh + 'px' : Math.min(wh, parent.bottom) - (square.top > 0? square.top : 0) + 'px';
		sb.style.position = square.top > 0 || ww < 960? 'absolute' : 'fixed';
		sb.style.width    = ww > 960? square.width + 'px' : '75%';
	 };

	 document.addEventListener('scroll', sl, false);
	 sl();

	 /*
	  * Customize the toggle button
	  */
	for (var i = 0; i < tb.length; i++) {
		var button = tb[i].appendChild(document.createElement('span'));
		button.classList.add('toggle-button');
	}

	 var rl  = function () {
		//Reset the size for window width and height that we collected
		wh  = window.innerHeight;
		ww  = window.innerWidth;

		//For mobile devices we toggle to collapsable mode
		if (ww < 960) {
			sbc.classList.add('collapsable', 'collapsed');
			for (var i = 0; i < tb.length; i++) { tb[i].firstChild.classList.remove('hidden'); }
			//Show the toggle button
		} else {
			sbc.classList.remove('collapsable', 'collapsed');
			for (var i = 0; i < tb.length; i++) { tb[i].firstChild.classList.add('hidden'); }
		}

		sl();
	 };

	 window.addEventListener('resize', rl, false);
	 rl();

	/*
	 * Defer the listener for the toggles to the document.
	 */
	document.addEventListener('click', function(e) { e.target.classList.contains('toggle-button') && sbc.classList.toggle('collapsed'); }, false);

	sbc.addEventListener('click', function() { sbc.classList.add('collapsed'); }, false);
	sb.addEventListener('click', function(e) { e.stopPropagation(); }, false);

}());

(function () {
	var stickies = Array.prototype.slice.call(document.querySelectorAll('.sticky'));
	var current  = null;
	var clone    = null;
	var invAt    = [0, 0];


	var listener = function () {
		var candidate = null;
		var next      = null;

		if (window.pageYOffset >= invAt[0] && window.pageYOffset <= invAt[1]) {
			return;
		}

		if (current) {
			clone.parentNode.removeChild(clone);
			current = clone = null;
			invAt = [0, 0];
		}

		for (var i = 0; i < stickies.length; i++) {
			var sticky = stickies[i];
			var rect   = sticky.getBoundingClientRect();

			if (rect.top < 0) {
				candidate = sticky;
				next      = stickies[i+1];
			}
		}

		if (candidate) {
			if (current !== null) {
				clone.parentNode.removeChild(clone);
				clone = current = null;
				invAt = [0, 0];
			}

			var parent = candidate.parentNode.getBoundingClientRect();
			var rect   = candidate.getBoundingClientRect();
			var nxtrect= next? next.getBoundingClientRect() : null;
			var top    = Math.min(parent.top + parent.height - rect.height, next? nxtrect.top - rect.height : 0, 0);

			invAt[0] = top? window.pageYOffset : window.pageYOffset + rect.top;
			invAt[1] = next? window.pageYOffset + nxtrect.top - rect.height : window.pageYOffset + parent.top + parent.height - rect.height;

			current  = candidate;
			clone    = candidate.cloneNode(true);
			clone.style.position = 'fixed';
			clone.style.left     = rect.left + 'px';
			clone.style.top      = top + 'px';
			clone.style.width    = rect.width + 'px';

			document.body.appendChild(clone);
		}


	};

	var debounce = null;
	document.addEventListener('scroll', function () {
		if (debounce) { return; }
		debounce = setTimeout(function () { debounce = null; listener(); }, 10);
	}, false);
}());
