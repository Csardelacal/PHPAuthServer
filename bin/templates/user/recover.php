

<?php if (isset($success) && $success): ?>

<div class="spacer" style="height: 30px"></div>
<div class="row1">
	<div class="span1 message success">
		<?= __($success) ?>
	</div>
</div>

<?php elseif (isset($error) && $error): ?>

<div class="spacer" style="height: 30px"></div>
<div class="row1">
	<div class="span1 message error">
		<?= __($error) ?>
	</div>
</div>

<?php endif; ?>

<div class="spacer" style="height: 30px;"></div>

<div class="row1">
	<div class="span1 login-logo">
		<img src="<?= url('image', 'hero') ?>">
	</div>
</div>

<div class="spacer" style="height: 30px;"></div>

<form method="POST" class="condensed standalone">
	<?php if ($action == 'emailform'): ?> 
	<input type="text" name="email" placeholder="Email address">
	<div class="message info">
		Enter the email address linked to your account. We will send instructions 
		on recovering your account to that address.
	</div>
	<?php elseif ($action == 'passwordform'): ?> 
	<input type="password" name="password[]" placeholder="Password">
	<input type="password" name="password[]" placeholder="Repeat">
	<div class="message info" id="password-match">
		Your email was successfully confirmed. Just enter a new password in the field
		to set a new one.
	</div>
	<div class="message error" id="password-missmatch" style="display: none">
		Passwords do not match. Maybe you made a mistake while typing it. Mind trying again?
	</div>
	<?php endif; ?> 
	<input type="submit" value="Continue">
</form>

<div class="spacer" style="height: 10px"></div>

<p style="text-align: center">
	<a href="<?= url('user', 'login', Array('returnto' => $returnto)) ?>"   >Log in</a> ·
	<a href="<?= url('user', 'register', Array('returnto' => $returnto)) ?>">Create account</a>
</p>

<script type="text/javascript">
(function () {
	var passwords = document.querySelectorAll('input[type="password"]');
	var message = document.getElementById('password-missmatch');
	var info = document.getElementById('password-match');
	var submit = document.querySelector('input[type="submit"]');
	
	for (var i = 0; i < passwords.length; i++) {
		passwords[i].addEventListener('keyup', function () {
			var match = passwords[0].value === passwords[1].value;
			message.style.display = match? 'none' : 'block';
			info.style.display = !match? 'none' : 'block';
			match? submit.removeAttribute('disabled') : submit.setAttribute('disabled', 'disabled');
		});
	}
}());
</script>

<!-- End of the template-->
