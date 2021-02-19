
<div class="spacer large"></div>

<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<h1>Set your password</h1>
		<div class="spacer medium"></div>
		
		<?php if (isset($error) && $error): ?>
		<div class="message error">
			<p>Sorry, that didn't work.</p>
		</div>
		<?php else :?>
		<p>
			In the field below you can enter a backup code that you have not yet 
			used.
		</p>
		<?php endif; ?>
		
		<div class="spacer large"></div>
		
		<form method="POST" action="" id="password-form">
			<div class="frm-ctrl-grp">
				<div class="frm-ctrl-outer">
					<input class="frm-ctrl" placeholder="" type="password" name="password" id="password" autocomplete="current-password" required aria-describedby="password-constraints" minlength="8" style="height: 3.275rem">
					<label for="password">Password</label>
				</div>
				<div class="frm-ctrl-outer fixed-width align-center" style=" width: 3.3rem">
					<span class="frm-ctrl-ro">
						<button type="button" id="toggle-password" aria-label="Show password. Others might be able to see your password." style="margin: 0; padding: 0; border: none; background: none">
							<span id="show-password" >
								<svg style="height: 1.5rem; color: #444" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
								</svg>
							</span>
							<span id="hide-password" style="display: none">
								<svg style="height: 1.5rem; color: #444" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
								</svg>
							</span>
						</button>
					</span>
				</div>
			</div>
			<div class="spacer medium"></div>
			<input type="submit" style="width: 100%" class="button" value="Set password" id="password-submit" />
		</form>
		
		<div class="spacer medium"></div>
	</div>
</div>


<script>
(function () {
	/*
	 * Whenever the user submits the form, we're disabling the input so it can be
	 * pressed again. Please note that, if we're introducing validation here that
	 * needs to be performed before the form is submitted, we need to re-enable the
	 * input if the validation failed.
	 */
	var submit = document.getElementById('password-submit');
	var form   = document.getElementById('password-form');
	
	form.addEventListener('submit', function (e) { submit.disabled = 'disabled'; })
	
	/*
	 * Toggle the password. Source for this is: https://www.youtube.com/watch?v=alGcULGtiv8&app=desktop
	 */
	var toggle   = document.getElementById('toggle-password');
	var password = document.getElementById('password');
	
	toggle.addEventListener('click', function (e) {
		if (password.type === 'password') {
			password.type = 'text';
			toggle.querySelector('#hide-password').style.display = 'block';
			toggle.querySelector('#show-password').style.display = 'none';
			toggle.setAttribute('aria-label', 'Hide password');
		}
		else {
			password.type = 'password';
			toggle.querySelector('#hide-password').style.display = 'none';
			toggle.querySelector('#show-password').style.display = 'block';
			toggle.setAttribute('aria-label', 'Show password. Others might be able to see your password.');
		}
		
		e.preventDefault();
	});
}());
</script>
