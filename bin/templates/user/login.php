
<div class="spacer" style="height: 300px;"></div>
<form method="post" class="condensed standalone" action="<?= new URL('user', 'login') ?>" enctype="multipart/form-data">
	<input type="email"    name="username" placeholder="Username">
	<input type="password" name="password" placeholder="Password">
	<input type="submit" value="Log in">
</form>