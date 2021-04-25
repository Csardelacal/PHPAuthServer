(function(){
	'use strict';
	const
		byid = id => document.getElementById(id),
		$iconin = byid('icon-input'),
		$iuwrap = byid('icon-upload-wrap');

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
