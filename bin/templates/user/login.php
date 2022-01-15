
<div class="spacer" style="height: 30px;"></div>

<div class="row1">
	<div class="span1 login-logo">
		<img src="<?= url('image', 'hero') ?>">
	</div>
</div>

<div class="spacer" style="height: 30px;"></div>

<form method="post" class="condensed standalone" action="" enctype="multipart/form-data">
	<input type="hidden"   name="_xsrf_"   value="<?= $xsrf ?>" />
	<input type="text"     name="username" placeholder="Username" autocomplete="off" spellcheck="false">
	<input type="password" name="password" placeholder="Password">
	<?php if (isset($message) && $message): ?> 
	<div class="message error"><?= $message ?></div>
        <?php elseif($locked): ?>
        <div class="message info" id="capacity">
                Server is currently experiencing capacity issues. Please wait <span id="capacity-timer"></span> seconds... We apologize for the inconvenience.
        </div>
	<script>
		setTimeout(function () {
			document.getElementById('capacity').style.display = 'none';
			document.querySelector('input[type="submit"]').removeAttribute('disabled');
		}, 60000);
		
		setTimeout(function () {
			document.querySelector('input[type="submit"]').setAttribute('disabled', 'disabled');
		}, 0);

		var seconds = 60;
		setInterval(function () {
			document.getElementById('capacity-timer').innerHTML = seconds;
			seconds--;
		}, 1000);
	</script>
	<?php else: ?>
	<div class="message info">
		Authenticating you requires our application to provide your browser with a 
		cookie and to record your IP. This is required to secure your account.
	</div>
	<?php endif; ?> 
	<input type="submit" value="Log in">
</form>

<div class="spacer" style="height: 10px;"></div>

<p style="text-align: center">
	<a href="<?= url('user', 'recover') ?>"                                 >Forgot password?</a> ·
	<a href="<?= url('user', 'register', Array('returnto' => $returnto)) ?>">Create account</a>
</p>
