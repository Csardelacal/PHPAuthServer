(function () {
	var dials = document.querySelectorAll('.dials');
	var curtain = document.body.appendChild(document.createElement('div'));
	var current = null;
	
	curtain.className = 'dials-curtain dnd';
	curtain.style.display = 'none';
	
	var hideCurtain = function () {
		curtain.classList.add('dnd');
		setTimeout(function () { curtain.style.display = 'none'; }, 300);
	};
	
	var showCurtain = function (d) {
		setTimeout(function () { 
			curtain.classList.remove('dnd'); 
		}, 50);
		
		d.style.display = 'block';
		curtain.style.display = 'block'; 
	};
	
	for (var i = 0; i < dials.length; i++) {
		var p = dials[i].parentNode;
		var t = p.appendChild(document.createElement('span'));
		var d = p.querySelector('.dials');
		
		p.classList.add('has-dials');
		t.classList.add('dial-toggle', 'medium', 'narrow');
		
		d.addEventListener('click', function (e) { e.stopPropagation(); }, false);
		
		t.addEventListener('click', function (p, t, d) {
			return function (e) {
				showCurtain(d);
				current = d;
				e.stopPropagation();
			};
		} (p, t, d), false);
	}
	
	document.addEventListener('click', function () {
		if (current) { current.style.display = 'none'; }
		hideCurtain();
		current = null;
	}, false);
}());