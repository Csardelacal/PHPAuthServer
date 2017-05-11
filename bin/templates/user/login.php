
<div class="spacer" style="height: 30px;"></div>

<div class="row1">
	<div class="span1 login-logo">
		<img src="<?= url('image', 'hero') ?>">
	</div>
</div>

<div class="spacer" style="height: 30px;"></div>

<form method="post" class="condensed standalone" action="" enctype="multipart/form-data">
	<input type="text"     name="username" placeholder="Username" autocomplete="off" spellcheck="false">
	<input type="password" name="password" placeholder="Password">
	<?php if (isset($message) && $message): ?> 
	<div class="message error"><?= $message ?></div>
	<?php endif; ?> 
	<input type="submit" value="Log in">
</form>

<div class="spacer" style="height: 10px;"></div>

<p style="text-align: center">
	<a href="<?= new URL('user', 'recover') ?>"                                 >Forgot password?</a> ·
	<a href="<?= new URL('user', 'register', Array('returnto' => $returnto)) ?>">Create account</a>
</p>
