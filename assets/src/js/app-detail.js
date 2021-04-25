(function () {
	'use strict';
	const
		byid = id => document.getElementById(id),
		anmiationSupported = typeof document.createElement('div').animate === 'function',
		$idcopy = byid('id-copy'),
		$idcont = byid('id-container'),
		$scopy = byid('secret-copy'),
		$scont = byid('secret-container'),
		$shidden = byid('secret-hidden'),
		$svisible = byid('secret-visible'),
		$namedisp = byid('name-display'),
		$namein = byid('name-input'),
		$namecont = byid('name-container'),
		$icondisp = byid('icon-display'),
		$iconin = byid('icon-input'),
		iconUploadButton = byid('app-icon-button');

	// Copy any text to clipboard
	// Must be called from within an event handler
	const copy = (text, el) => {
		if (!document.queryCommandSupported('copy')) {
			prompt('Copy with Ctrl+C, close with Enter', text);
			return true;
		}

		let $helper = document.createElement('textarea'),
			success = false;
		$helper.style.opacity = 0;
		$helper.style.width = 0;
		$helper.style.height = 0;
		$helper.style.position = 'fixed';
		$helper.style.left = '-10px';
		$helper.style.top = '50%';
		$helper.style.display = 'block';
		$helper.innerText = text;
		document.getElementsByTagName('body')[0].appendChild($helper);
		$helper.focus();
		$helper.select();

		try {
			success = document.execCommand('copy');
		} catch (err) { }

		$helper.parentNode.removeChild($helper);

		if (!success) {
			prompt('Copy with Ctrl+C, close with Enter', text);
			return;
		}

		if (el instanceof HTMLElement && anmiationSupported) {
			const currColor = window.getComputedStyle(el).getPropertyValue('color');
			el.animate({
				color: [currColor, '#000', currColor],
			}, {
				duration: 500,
			});
		}
	};
	const toggleContents = (el, between, animdur = 200) => {
		if (anmiationSupported) {
			el.animate({
				opacity: [1, 0]
			}, {
				duration: animdur
			}).onfinish = function () {
				between();

				el.animate({
					opacity: [0, 1]
				}, {
					duration: animdur
				});
			};
		}
		else between();
	};

	let splacehold = false;
	const setPlacehold = () => {
		splacehold = $scont.innerHTML;
		$scont.innerHTML = `<span class='fake-field'>${$scont.getAttribute('data-actual')}</span>`;
		$shidden.classList.add('hidden');
	};
	const resetPlacehold = () => {
		$scont.innerHTML = splacehold;
		splacehold = false;
		$shidden.classList.remove('hidden');
	};

	$iconin.addEventListener('change', function () {
		if (this.files && this.files[0]) {
			/*
			 * Generate a preview of the icon. This allows us to make the application
			 * appear to immediately accept the state the user provided.
			 */
			let reader = new FileReader();
			reader.onload = function (e) { $icondisp.src = e.target.result; };
			reader.readAsDataURL(this.files[0]);
			
			/*
			 * Start a job to replace the icon in the background. This will put the 
			 * data to the server right away.
			 */
			fetch(this.dataset.submitUrl, {method: 'PUT', body: this.files[0]});
		}
	});
	
	iconUploadButton.addEventListener('click', function () {
		$iconin.click();
	});
})();
