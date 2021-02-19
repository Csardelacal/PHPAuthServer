
<form class="regular" method="POST">
	
	<div class="row l5 s1">
		<div class="span l1"></div>
		<div class="span l3 s1">
			<div class="spacer medium"></div>
			<h1 class="text:grey-300">Change your password.</h1>
			
			<p class="text:grey-500">
				<small>
				Your password is encrypted and nobody (not even an administrator)
				can read the password. It is obviously not transferred 
				to any third party or application. Picking a strong password and changing
				it regularly is a recommended practice.
				</small>
			</p>
			
			<div class="spacer small"></div>
			
			<label for="password" class="small secondary">
				Enter your new password
			</label>
			<div>
				<input name="password" id="password" type="password" class="frm-ctrl" placeholder="Set your new password" autocomplete="new-password" >
			</div>
			
			<div class="spacer small"></div>
			
			<div id="condition-minlength">
				<input type="checkbox" class="frm-ctrl"><span class="frm-ctrl-chk"></span>
				<span class="text:grey-500">Your password must be at least 8 characters long</span>
			</div>
			
			<div class="spacer small"></div>

			<div class="align-right">
				<input type="submit" class="button" value="Store" id="submit-button">
			</div>
			

			<div class="spacer" style="height: 25px"></div>
		</div>
	</div>
</form>

<script type="text/javascript">
(function () {
	var conditions = {
		minlength : function () { return document.getElementById('password').value.length >= 8; }
	};
	
	var check = function () {
		var satisfied = true;
		
		for (var i in conditions) {
			if (!conditions.hasOwnProperty(i)) { continue; }
			var result = conditions[i]();
			var html  = document.querySelector('#condition-' + i + ' input[type="checkbox"]');
			
			if (html) {
				html.checked = result;
			}
			
			satisfied = satisfied && result;
		}
		
		if (satisfied) {
			document.getElementById('submit-button').removeAttribute('disabled');
		} 
		else {
			document.getElementById('submit-button').setAttribute('disabled', 'disabled');
		}
	};
	
	document.getElementById('password').addEventListener('input', check, false);
	check();
}());
</script>
