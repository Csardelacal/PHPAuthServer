
(function () {
	
	var checkboxes = document.querySelectorAll('input[type="checkbox"]');
	
	for (var i = 0; i < checkboxes.length; i++) {
		var child = document.createElement('span');
		child.className = 'toggle';
		
		checkboxes[i].parentNode.insertBefore(child, checkboxes[i].nextSibling);
		checkboxes[i].classList.add('styled');
		
		child.addEventListener('click', function (e) { return function () { e.click(); }}(checkboxes[i]), false);
	}
	
}());