

<?php if (isset($user) && $user): ?>

<div class="spacer" style="height: 30px"></div>
<div class="row1">
	<div class="span1 message success">
		An email with the link to recover your account was sent to you.
	</div>
</div>

<?php elseif (current_context()->request->isPost()): ?>

<div class="spacer" style="height: 30px"></div>
<div class="row1">
	<div class="span1 message error">
		That email address is not attached to any account
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
	<?php endif; ?> 
	<input type="submit" value="Continue">
</form>

<div class="spacer" style="height: 10px"></div>

<p style="text-align: center">
	<a href="<?= url('user', 'login', Array('returnto' => $returnto)) ?>"   >Log in</a> ·
	<a href="<?= url('user', 'register', Array('returnto' => $returnto)) ?>">Create account</a>
</p>


<!-- End of the template-->
