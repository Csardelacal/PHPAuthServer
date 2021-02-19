
<div class="spacer" style="height: 30px;"></div>

<div class="row l2">
	<div class="span l1 login-logo">
		<img src="<?= url('image', 'hero') ?>">
	</div>
</div>

<div class="spacer" style="height: 30px;"></div>

<div class="row l2">
	<div class="span l1 login-logo">
		<form method="post" action="" enctype="multipart/form-data" id="login-form">

			<input type="hidden"   name="device[js]" id="device-js" value="false">
			<input type="hidden"   name="device[platform]" id="device-platform" value="unknown">
			<input type="hidden"   name="device[touch]" id="device-touch" value="false">
			<input type="hidden"   name="device[wide]" id="device-wide" value="false">

			<?php if (isset($message) && $message): ?> 
			<div class="message error"><?= $message ?></div>
			<?php else: ?>
			<div class="message info">
				Authenticating you requires our application to provide your browser with a 
				cookie and to record your IP. This is required to secure your account.
			</div>
			<?php endif; ?> 

			<div class="spacer medium"></div>

			<div class="frm-ctrl-outer"><!-- Soon to be frm-ctrl-outer-->
				<input class="frm-ctrl" type="text" name="username" id="username" autofocus="true" autocomplete="off" spellcheck="false" required minlength="3" placeholder="">
				<label for="username">Username</label>
			</div>

			<div class="spacer small"></div>
			
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
			
			<div class="spacer small"></div>
			
			<div id="password-constraints">Your password must be at least 8 characters long.</div>

			<div class="spacer small"></div>
			
			<input type="submit" class="button button-color-purple-700" style="width: 100%" id="login-submit" value="Log in">
		</form>

		<div class="spacer" style="height: 10px;"></div>

		<p style="text-align: center">
			<a href="<?= url('user', 'recover') ?>"                                 >Forgot password?</a> ·
			<a href="<?= url('user', 'register', Array('returnto' => $returnto)) ?>">Create account</a>
		</p>
	</div>
</div>

<script type="text/javascript">
(function () {
	/*
	 * This script allows PHPAS to perform feature detection for your device.
	 * Your information is not stored past the lifetime of your sesstion, this 
	 * information is not processed, only used to allow you to identify your own
	 * devices if you so desire.
	 * 
	 * Your devices specific data is never sent to the server and only processed
	 * localy, here's a list of features we record about it:
	 * 
	 * - Whether it is touch enabled
	 * - Whether is has a big screen
	 * - The operating system it runs on (Windows / Linux / Mac OS / Android / iOS)
	 * - Whether it has javascript enabled
	 * 
	 * This allows us to print a little device icon telling you whether your account
	 * was accessed from your phone, your computer or a bot your may have created.
	 */
	
	function width() {
		return Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	}
	
	document.getElementById('device-js').value = 'true';
	document.getElementById('device-wide').value = width() > 1440? 'true' : 'false';
	document.getElementById('device-platform').value = window.navigator.platform;
	
	var touch = function () {
		//Source: http://www.stucox.com/blog/you-cant-detect-a-touchscreen/
		
		document.getElementById('device-wide').value = width() > 750? 'true' : 'false';
		document.getElementById('device-touch').value = 'true';
		// Remove event listener once fired, otherwise it'll kill scrolling
		// performance
		window.removeEventListener('touchstart', touch);
	};
	
	window.addEventListener('touchstart', touch, false);
}());
</script>

<script>
(function () {
	/*
	 * Whenever the user submits the form, we're disabling the input so it can be
	 * pressed again. Please note that, if we're introducing validation here that
	 * needs to be performed before the form is submitted, we need to re-enable the
	 * input if the validation failed.
	 */
	var submit = document.getElementById('login-submit');
	var form   = document.getElementById('login-form');
	
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
