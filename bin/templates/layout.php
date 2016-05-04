<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?= URL::asset('css/app.css') ?>">
	</head>
	<body>
		
		<?php if(isset($userIsAdmin) && $userIsAdmin): ?>
		<div class="admin-navigation">
			<div class="row7">
				<div class="span1"><a href="<?= new URL('admin') ?>" class="menu-item">Admin</a></div>
				<div class="span1"><a href="<?= new URL('app') ?>" class="menu-item">Apps</a></div>
				<div class="span1"><a href="<?= new URL('token') ?>" class="menu-item">Sessions</a></div>
				<div class="span1"><a href="<?= new URL('user') ?>" class="menu-item">Users</a></div>
				<div class="span1"><a href="<?= new URL('group') ?>" class="menu-item">Groups</a></div>
				<div class="span1"><a href="<?= new URL('email') ?>" class="menu-item">Email</a></div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if (isset($authUser) && $authUser && $authUser->verified !== true): ?> 
		<div class="spacer" style="height: 30px;"></div>
		<div class="row1">
			<div class="span1 message error">
				Your account has not yet been activated. <a href="<?= new URL() ?>">Resend activation mail</a>
			</div>
		</div>
		<?php endif; ?> 
		
		<?= $content_for_layout ?>
		
		<!--Cron scheduler-->
		<script type="text/javascript" src="<?= new URL('cron') ?>" async="true"></script>
	</body>
</html>