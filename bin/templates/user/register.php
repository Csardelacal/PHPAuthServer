
<div class="spacer" style="height: 30px;"></div>

<div class="row1">
	<div class="span1 login-logo">
		<img src="<?= url('image', 'hero') ?>">
	</div>
</div>

<div class="spacer" style="height: 30px;"></div>

<form method="post" action="" enctype="multipart/form-data" class="condensed standalone">
	<input type="text"     name="username" placeholder="Username">
	<input type="email"    name="email"    placeholder="Email">
	<input type="password" name="password" placeholder="Password">
	<?php foreach ($attributes as $attribute): ?> 
	<input type="text" 
			 name="<?= $attribute->_id ?>" 
			 placeholder="<?= $attribute->name ?>" 
			 value="<?= isset($POST[$attribute->_id])? __($_POST[$attribute->_id]) : $attribute->default ?>">
	<?php endforeach; ?> 
	<?php if (isset($messages)): foreach($messages as $message): ?>
	<div class="message error">
		<?= __($message) ?>
	</div>
	<?php endforeach; endif; ?>
	<input type="submit">
</form>

<div class="spacer" style="height: 10px;"></div>

<p style="text-align: center">
	Already have an account? 
	<a href="<?= url('user', 'login', ['returnto' => $returnto]) ?>">Sign in.</a>
</p>
