(function(){
	'use strict';
	const
		byid = id => document.getElementById(id),
		show = el => el.classList.remove('hidden'),
		hide = el => el.classList.add('hidden'),
		enable = el => el.removeAttribute('disabled'),
		disable = el => el.setAttribute('disabled',''),
		anmiationSupported = typeof document.createElement('div').animate === 'function',
		$idcopy = byid('id-copy'),
		$idcont = byid('id-container'),
		$scopy = byid('secret-copy'),
		$scont = byid('secret-container'),
		$shidden = byid('secret-hidden'),
		$svisible = byid('secret-visible'),
		$namedisp = byid('name-display'),
		$namein = byid('name-input'),
		$changename = byid('change-name'),
		$cancelname = byid('cancel-name'),
		$namecont = byid('name-container'),
		$icondisp = byid('icon-display'),
		$iconin = byid('icon-input'),
		$iuwrap = byid('icon-upload-wrap'),
		$changeicon = byid('change-icon'),
		$cancelicon = byid('cancel-icon');

	// Copy any text to clipboard
	// Must be called from within an event handler
	const copy = (text, el) => {
		if (!document.queryCommandSupported('copy')){
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
		} catch(err){}

		$helper.parentNode.removeChild($helper);

		if (!success){
			prompt('Copy with Ctrl+C, close with Enter', text);
			return;
		}

		if (el instanceof HTMLElement && anmiationSupported){
			const currColor = window.getComputedStyle(el).getPropertyValue('color');
			el.animate({
		        color: [currColor, '#000', currColor],
		    }, {
		        duration: 500,
		    });
		}
	};
	const toggleContents = (el, between, animdur = 200) => {
		if (anmiationSupported){
			el.animate({
				opacity: [1,0]
			}, {
				duration: animdur
			}).onfinish = function(){
				between();

				el.animate({
					opacity: [0,1]
				}, {
					duration: animdur
				});
			};
		}
		else between();
	};

	$scopy.addEventListener('click',function(e){
		e.preventDefault();

		copy($scont.getAttribute('data-actual'), $scopy);
	});
	$idcopy.addEventListener('click',function(e){
		e.preventDefault();

		copy($idcont.children[0].innerHTML, $idcopy);
	});

	let splacehold = false;
	const setPlacehold = () => {
		splacehold = $scont.innerHTML;
		$scont.innerHTML = `<span class='fake-field'>${$scont.getAttribute('data-actual')}</span>`;
		$shidden.classList.add('hidden');
		$svisible.classList.remove('hidden');
	};
	$shidden.addEventListener('click',function(e){
		if (!splacehold){
			e.preventDefault();
			toggleContents($scont, setPlacehold);
		}
		else return true;
	});
	const resetPlacehold = () => {
		$scont.innerHTML = splacehold;
		splacehold = false;
		$svisible.classList.add('hidden');
		$shidden.classList.remove('hidden');
	};
	$svisible.addEventListener('click',function(e){
		if (splacehold){
			e.preventDefault();
			toggleContents($scont, resetPlacehold);
		}
		else return true;
	});
	$changename.addEventListener('click',function(e){
		e.preventDefault();

		toggleContents($namecont, function(){
			hide($namedisp);
			hide($changename);
			$namein.value = $namedisp.innerHTML;
			enable($namein);
			show($namein.parentNode);
			show($cancelname);
		});
	});
	$cancelname.addEventListener('click',function(e){
		e.preventDefault();

		toggleContents($namecont, function(){
			hide($namein.parentNode);
			disable($namein);
			hide($cancelname);
			show($namedisp);
			show($changename);
		});
	});

	$changeicon.addEventListener('click',function(e){
		e.preventDefault();

		enable($iconin);
		show($iuwrap);
		show($cancelicon);
		hide($icondisp);
		hide($changeicon);
	});
	$cancelicon.addEventListener('click',function(e){
		e.preventDefault();

		hide($iuwrap);
		hide($cancelicon);
		disable($iconin);
		$iconin.value = '';
        $iuwrap.style.backgroundImage = '';
        $iuwrap.classList.remove('uploading');
        $iuwrap.classList.remove('uploaded');
		show($icondisp);
		show($changeicon);
	});
	$iconin.addEventListener('change',function(){
	    if (this.files && this.files[0]) {
	        $iuwrap.style.backgroundImage = '';
	        $iuwrap.classList.add('uploading');
	        let reader = new FileReader();

	        reader.onload = function(e){
	            $iuwrap.classList.remove('uploading');
	            $iuwrap.classList.add('uploaded');
	            $iuwrap.style.backgroundImage = `url("${e.target.result}")`;
	        };

	        reader.readAsDataURL(this.files[0]);
	    }
	});
})();
