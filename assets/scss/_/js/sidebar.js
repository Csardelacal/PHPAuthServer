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


depend(['m3/ui/sticky', 'm3/animation/animation'], function(sticky, transition) {
	
	
	
	var listener = function (element, listeners) {
		for (var i in listeners) {
			if (!listeners.hasOwnProperty(i)) { continue; }
			element.addEventListener(i, listeners[i], false);
		}
	};
	
	var sidebar = function(element) {
		var mobile = window.innerWidth < 1160;
		
		var touch  = {
			startX: undefined,
			startY: undefined,
			endX: undefined,
			endY: undefined,
			last: undefined,
			started: undefined,
			
			movementRequired : 100,
			timeout          : 350
		};
		
		/*
		 * Set the sidebar to be the entire height of the document. This expects an
		 * auto-extending parent to be properly functional
		 */
		var container = element.parentNode;
		var content = container.parentNode.querySelector('.content');
		
		container.style.height = container.parentNode.clientHeight + 'px';
		container.style.display = 'inline-block';
		
		container.parentNode.style.width = '100%';
		container.parentNode.style.overflowX = 'hidden';
		container.parentNode.style.whiteSpace = 'nowrap';
		
		element.style.display = 'block';
		
		if (!mobile && !container.classList.contains('collapsed')) {
			element.style.left = '0px';
			container.style.width ='200px';
			content.style.width = 'calc(100% - 200px)';
		} else {
			element.style.left = '-200px';
			container.style.width = '0px';
			content.style.width = '100%';
		}
		
		container.classList.remove('collapsed');
		
		/*
		 * Create listeners that allow the application to react to events happening 
		 * in the browser.
		 */

		listener(document, {
			click: function(e) { 
				if (!e.target.classList.contains('toggle-button') && window.innerWidth > 1160) { return; }
				if (container.clientWidth === 0 && !e.target.classList.contains('toggle-button')) { return; }
				
				
				var hidden = container.clientWidth === 0;
				
				transition(function (progress) {
					var width = 1 + (hidden? progress * 200 : 200 - (progress * 200));
					
					if (window.innerWidth > 1160) {
						element.style.left = (width - 200) + 'px';
						container.style.width = width + 'px';
						container.parentNode.querySelector('.content').style.width = 'calc(100% - ' + width + 'px)';
					} else {
						var width = 1 + (hidden? progress * 200 : 200 - (progress * 200));
						element.style.left = (width - 200) + 'px';
						container.style.width = width + 'px';
						container.parentNode.querySelector('.content').style.width = '100%';
						container.parentNode.querySelector('.content').style.opacity = 1 -  width / 300;
					}
				}, 300, 'easeInEaseOut');
			},
			
			touchstart: function (e) {
				var finger = e.touches[0];
				touch.startX = finger.screenX;
				touch.startY = finger.screenY;
				touch.started = +new Date();
			},
			
			touchmove : function (e) {
				var finger = e.touches[0];
				touch.endX = finger.screenX;
				touch.endY = finger.screenY;
			},
			
			touchend: function (e) {
				if ((+new Date()) - touch.started > touch.timeout) {
					return;
				}
				
				if (Math.abs(touch.endX - touch.startX) > Math.abs(touch.endY - touch.startY) && Math.abs(touch.endX - touch.startX) > touch.movementRequired) {
					//Horizontal swipe
					if (touch.endX - touch.startX > 0) {
						//Left to right swipe
						if (window.innerWidth < 1160 && container.clientWidth === 0) {
							transition(function (progress) {
								var width = 1 + progress * 200;
								element.style.left = (width - 200) + 'px';
								container.style.width = width + 'px';
								content.style.width = '100%';
								content.style.opacity = 1 -  width / 300;
							}, 300, 'easeInEaseOut');
						}
					}
					else {
						//Right to left swipe
						if (window.innerWidth < 1160 && container.clientWidth !== 0) {
							transition(function (progress) {
								var width = 1 + (200 - (progress * 200));
								element.style.left = (width - 200) + 'px';
								container.style.width = width + 'px';
								content.style.width = '100%';
								content.style.opacity = 1 -  width / 300;
							}, 300, 'easeInEaseOut');
							
							/*
							 * If the swipe was registered, we prevent the browser from
							 * reacting to it.
							 */
							e.preventDefault();
							e.stopPropagation();
						}
					}
				}
			}
		});

		listener(container, {
			click: sidebar.hide
		});

		listener(element, {
			click: function(e) { e.stopPropagation(); }
		});
		
		sticky.stick(element, container.parentNode, 'top');
	};
	
	return sidebar;
});