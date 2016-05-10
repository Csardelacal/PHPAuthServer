<!DOCTYPE html>
<html>
	<head>
		<title><?= isset(${'page.title'}) && ${'page.title'}? ${'page.title'} : 'Account server' ?></title>
		<link rel="stylesheet" type="text/css" href="<?= URL::asset('css/app.css') ?>">
	</head>
	<body>
		
		<div class="admin-navigation">
			<div class="row7">
				<div class="span1"><a href="<?= new URL('') ?>"      class="menu-item">Account</a></div>
				<div class="span1"><a href="<?= new URL('token') ?>" class="menu-item">Sessions</a></div>
				<div class="span1"><a href="<?= new URL('user') ?>"  class="menu-item">Users</a></div>
				<div class="span1"><a href="<?= new URL('group') ?>" class="menu-item">Groups</a></div>
				<?php if(isset($userIsAdmin) && $userIsAdmin): ?> 
				<div class="span1"><a href="<?= new URL('admin') ?>" class="menu-item">Admin</a></div>
				<div class="span1"><a href="<?= new URL('app') ?>"   class="menu-item">Apps</a></div>
				<div class="span1"><a href="<?= new URL('email') ?>" class="menu-item">Email</a></div>
				<?php endif; ?> 
			</div>
		</div>
		
		<?php if (isset($authUser) && $authUser && !$authUser->verified): ?> 
		<!-- 
			You haven't verified your account yet, that is quite a big deal for some
			applications, which may rely on you activating your account to make sure 
			you didn't make up your address.
		-->
		<div class="spacer" style="height: 30px;"></div>
		<div class="row1">
			<div class="span1 message error">
				Your account has not yet been activated. <a href="<?= new URL('user', 'activate') ?>">Resend activation mail</a>
			</div>
		</div>
		<?php endif; ?> 
		
		<?= $content_for_layout ?>
		
		<!--Cron scheduler-->
		<script type="text/javascript" src="<?= new URL('cron') ?>" async="true"></script>
	</body>
</html>
