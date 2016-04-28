

<?php if (isset($user) && $user): ?>

<div class="spacer" style="height: 30px"></div>
<div class="row1">
	<div class="span1 message success">
		An email with the link to recover your account was sent to you.
	</div>
</div>

<?php elseif ($this->request->isPost()): ?>

<div class="spacer" style="height: 30px"></div>
<div class="row1">
	<div class="span1 message error">
		That email address is not attached to any account
	</div>
</div>

<?php endif; ?>

<div class="spacer" style="height: 30px"></div>

<form method="POST" class="condensed standalone">
	<?php if ($action == 'emailform'): ?> 
	<input type="text" name="email" placeholder="Email address">
	<?php elseif ($action == 'passwordform'): ?> 
	<input type="password" name="password[]" placeholder="Password">
	<input type="password" name="password[]" placeholder="Repeat">
	<?php endif; ?> 
	<input type="submit" value="Continue">
</form>
<!-- End of the template-->
