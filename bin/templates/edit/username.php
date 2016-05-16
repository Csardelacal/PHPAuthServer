
<div class="spacer" style="height: 50px"></div>

<form class="condensed standalone" method="POST">
	<div class="description">
		Enter your new username below to change it. Your old username will be kept
		as an alias for 3 months before it expires.
	</div>
	<input type="text" name="username" placeholder="Your new username" value="<?= __(_def($_POST['username'], '')) ?>">
	<?php if (isset($messages) && is_array($messages)): foreach ($messages as $message): ?>
	<div class="error message"><?= $message ?></div>
	<?php endforeach; endif; ?>
	<input type="submit" value="Store">
</form>