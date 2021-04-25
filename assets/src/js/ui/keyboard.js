
depend('ui/keyboard', [], function () {
	
	var keys = {
		enter: 13,
		tab  :  9,
		escape: 27,
		arrowUp: 38,
		arrowDown: 40
	};
	
	var Listener = function (tgt, key, action) {
		this.key = key;
		this.trigger = function () { action(); };
	};
	
	var Keyboard = function (listenon) {
		
		var up = [], down = [], press = [];
		
		this.down = function (key, fn) {
			down.push(new Listener(listenon, key, fn));
		};
		
		this.up = function (key, fn) {
			up.push(new Listener(listenon, key, fn));
		};
		
		this.press = function (key, fn) {
			press.push(new Listener(listenon, key, fn));
		};
		
		listenon.addEventListener('keydown', function (e) {
			for (var i = 0; i < down.length; i++) {
				if (down[i].key === e.keyCode) { 
					down[i].trigger();
					e.stopPropagation();
					e.preventDefault();
				}
			}
		});
		
		listenon.addEventListener('keyup', function (e) {
			console.log('Key up');
			for (var i = 0; i < up.length; i++) {
				if (up[i].key === e.keyCode) { 
					up[i].trigger(); 
					e.stopPropagation();
					e.preventDefault();
				}
			}
		});
		
		listenon.addEventListener('keypress', function (e) {
			for (var i = 0; i < press.length; i++) {
				if (press[i].key === e.keyCode) { press[i].trigger(); }
			}
		});
	};
	
	return {
		listen: function (on) { return new Keyboard(on); },
		keys  : keys
	};
	
});
