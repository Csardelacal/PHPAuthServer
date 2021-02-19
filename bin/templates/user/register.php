
<div class="spacer" style="height: 30px;"></div>

<div class="row l1">
	<div class="span l1 align-center">
		<img src="<?= url('image', 'hero') ?>" style="max-width: 100%">
	</div>
</div>


<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<?php if (isset($messages)): foreach($messages as $message): ?>
		<div class="spacer small"></div>

		<div class="message error">
			<?= __($message->getMessage()) ?>
		</div>
		<?php endforeach; endif; ?>

		<div class="spacer" style="height: 30px;"></div>

		<form method="post" action="" enctype="multipart/form-data" id="register-form">
			<input type="text"     name="username" placeholder="Username" class="frm-ctrl">
			<div class="spacer small"></div>
			<input type="email"    name="email"    placeholder="Email"    class="frm-ctrl">
			<div class="spacer small"></div>
			<!-- See https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/password  -->
			<!-- For details on how the autocompletion works -->
			<input type="password" name="password" placeholder="Password" class="frm-ctrl" autocomplete="new-password" required >
			<div class="spacer medium"></div>
			<input type="submit" value="Register" class="button full-width disabled">
		</form>

		<div class="spacer" style="height: 10px;"></div>

		<p style="text-align: center">
			Already have an account? 
			<a class="text:blue-500" href="<?= url('user', 'login', ['returnto' => $returnto]) ?>">Sign in.</a>
		</p>
	</div>
</div>

<script type="text/javascript">
(function () {
	var form = document.getElementById('register-form');
	var validators = [
		function () { return form.querySelector('[name="username"]').value.length >= 3; },
		function () { return form.querySelector('[name="password"]').value.length >= 8; },
		function () { return form.querySelector('[name="email"]').value.length >= 3; }
	];
	
	var validate =  function () {
		var result = validators.reduce(function (carry, e) { return carry && e(); }, true);

		if (result) { form.querySelector('input[type="submit"]').classList.remove('disabled'); }
		else { form.querySelector('input[type="submit"]').classList.add('disabled'); }
	};
	
	form.querySelectorAll('input').forEach(function (input) {
		input.addEventListener('input', validate);
	});
	
	validate();
}());
</script>