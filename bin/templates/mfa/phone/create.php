
<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<h1 class="text:grey-300">Add a phone to your account</h1>
	</div>
</div>

<div class="row l1">
	<div class="span l1">
		<p class="text:grey-500">
			<small>
				Adding a phone allows us to send you SMS messages to verify your log-ins
				with two factor authentication. Please remember to include the country code
				to your cellphone number. <strong>We do not charge for the SMS. 
				Depending on your provider, receiving SMS messages may incur additional costs.</strong>
			</small>
		</p>
		
	</div>
</div>

<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<div class="spacer medium"></div>
		
		<form method="POST" action="">
			<div class="frm-ctrl-grp">
				<div class="frm-ctrl-outer fixed-width" style="width: 60px;">
					<div class="frm-ctrl-ro align-center">
						<img id="phone-flag" src="https://lipis.github.io/flag-icon-css/flags/1x1/de.svg" style="height: 100%; border-radius: 100%;">
					</div>
				</div>
				<div class="frm-ctrl-outer">
					<input type="text" id="phone-input" name="phone" class="frm-ctrl" placeholder="" value="+49">
					<label for="phone-input">Phone number</label>
				</div>
			</div>
			<div class="spacer small"></div>
			<input type="submit" class="button button-color-blue-300 full-width" style="width: 100%" value="Add phone">
		</form>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/phon@latest/dist/phon.js"></script>
<script>
(function () {
	var phon = new Phon.Phon();
	var input = document.getElementById('phone-input');
	var flag  = document.getElementById('phone-flag');
	
	input.addEventListener('blur', function () {
		input.value = phon.format(input.value);
	});
	
	input.addEventListener('input', function () {
		let iso = phon.country(input.value);
		flag.src = `https://lipis.github.io/flag-icon-css/flags/1x1/${iso.toLowerCase()}.svg`;
	});
}());
</script>
