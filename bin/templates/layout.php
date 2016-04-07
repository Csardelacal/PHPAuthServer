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
			</div>
		</div>
		<?php endif; ?>
		
		<?= $content_for_layout ?>
	</body>
</html>