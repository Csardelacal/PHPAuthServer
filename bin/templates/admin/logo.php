
<div class="spacer large"></div>

<div class="row l1">
	<div class="span l1 align-center">
		<img src="<?= url('image', 'hero') ?>" id="preview">
	</div>
</div>

<div class="spacer huge"></div>

<div class="row l1">
	<div class="span l1">
		<h2>Upload a new logo</h2>
	</div>
</div>

<div class="row l1">
	<div class="span l1">
		<p>
			The server hero image is displayed whenever the user needs to input credentials
			and during registration. It allows you to customize the branding of your 
			PHPAuthServer instance.
		</p>
		
		<div class="spacer large"></div>
		
		<form method="POST" enctype="multipart/form-data" class="align-center" id="form">
			<input type="file" name="file" id="file" style="display: none">
			<a class="button outline" id="image-pick">Select an image</a>
			<input class="button solid button-color-green-600" id="submit-button" type="submit" value="Store changes" disabled>
		</form>
		
		<div class="spacer huge"></div>
	</div>
</div>

<script type="text/javascript">
(function () {
	var btn = document.getElementById('image-pick');
	var input = document.getElementById('file');
	var prvw = document.getElementById('preview');
	var form = document.getElementById('form');
	var submit = document.getElementById('submit-button');
	
	btn.addEventListener('click', function () {
		input.click();
	});
	
	input.addEventListener('change', function () {
		
		var files = this.files;
		for (var i = 0; i < files.length; i++) {
			
			//Generate a preview
			var reader = new FileReader();
			reader.onload = function (e) {
				prvw.src = e.target.result;
				prvw.style.display = 'inline-block';
				submit.removeAttribute('disabled');
			};
			
			reader.readAsDataURL(files[i]);
			
			
		}
	});
	
	form.addEventListener('submit', function () {
		submit.setAttribute('disabled', 'disabled');
	})
}());
</script>
