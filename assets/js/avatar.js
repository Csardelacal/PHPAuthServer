(function(){
	'use strict';

	let choseBtn = document.querySelector("#imgSubmit"),
	    imgDisplay = document.querySelector("#imgDisplay"),
	    imgInp = document.querySelector("#imgInp");
	
	/*
	 * Define a listener for the input, once the image selected was changed, we 
	 * will load it into the window.
	 */
	imgInp.addEventListener('change',function(){
		 if (this.files && this.files[0]) {
			  let reader = new FileReader();

			  reader.onload = function (e) {
					let image = new Image();
					image.src = e.target.result;
					image.className = 'user-icon full-width square';
					image.style.marginTop = '10px';
					image.style.maxHeight = '300px';

					imgDisplay.appendChild(image);
			  };

				imgDisplay.innerHTML = '';
			  reader.readAsDataURL(this.files[0]);
		 }

		 choseBtn.disabled = !this.value;
	});

	imgDisplay.addEventListener('click', function () {
		imgInp.click();
	}, false);
	
	imgInp.style.display = 'none';

	choseBtn.disabled = true;
})();
