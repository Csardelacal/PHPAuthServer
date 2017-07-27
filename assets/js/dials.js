(function () {
	var dials = document.querySelectorAll('.dials');
	var current = null;
	
	for (var i = 0; i < dials.length; i++) {
		var p = dials[i].parentNode;
		var t = p.appendChild(document.createElement('span'));
		var d = p.querySelector('.dials');
		
		p.classList.add('has-dials');
		t.classList.add('dial-toggle');
		
		t.appendChild(document.createElement('span')).className = 'dots';
		
		d.addEventListener('click', function (e) { e.stopPropagation(); }, false);
		
		t.addEventListener('click', function (p, t, d) {
			return function (e) {
				d.style.display = 'block';
				if (current) { current.style.display = 'none'; }
				current = d;
				e.stopPropagation();
			};
		} (p, t, d), false);
	}
	
	document.addEventListener('click', function () {
		if (current) { current.style.display = 'none'; }
		current = null;
	}, false);
}());