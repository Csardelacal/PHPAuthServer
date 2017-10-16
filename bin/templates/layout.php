<!DOCTYPE html>
<html>
	<head>
		<title><?= isset(${'page.title'}) && ${'page.title'}? ${'page.title'} : 'Account server' ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="<?= spitfire\core\http\URL::asset('css/app.css') ?>">
		<link rel="stylesheet" type="text/css" href="<?= spitfire\core\http\URL::asset('css/ui-layout.css') ?>">
		
		<script>
			window.baseURL = <?= json_encode(strval(url())); ?>
		</script>
	</head>
	<body>
		
		<div class="navbar">
			<?php if(isset($authUser)): ?>
			<a href="<?= url('') ?>"      >Account</a>
			<a href="<?= url('token') ?>" >Sessions</a>
			<a href="<?= url('user', 'logout') ?>">Logout</a>
			<?php endif; ?>
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
				Your account has not yet been activated. <a href="<?= url('user', 'activate') ?>">Resend activation mail</a>
			</div>
		</div>
		<?php endif; ?> 
		
		<div class="spacer" style="height: 30px"></div>
		
		<div style="max-width: 960px; margin: 0 auto; min-height: 100%;">
			<div class="contains-sidebar">
				<div class="sidebar">
					<div class="topbar" style="color: #999; padding-left: 10px"> Account</div>
					<div class="spacer" style="height: 10px;"></div>
					
					<div class="menu-entry"><a href="<?= url() ?>"                  >Edit profile</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'password') ?>">Change password</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'avatar') ?>"  >Upload avatar</a></div>
					
					<?php if(isset($userIsAdmin) && $userIsAdmin): ?> 
					<div class="spacer" style="height: 30px"></div>
					<div class="menu-title">Administration</div>
					<div class="menu-entry"><a href="<?= url('group') ?>" >Groups</a></div>
					<div class="menu-entry"><a href="<?= url('admin') ?>">System settings</a></div>
					<div class="menu-entry"><a href="<?= url('app') ?>"  >App administration</a></div>
					<div class="menu-entry"><a href="<?= url('email') ?>">Email</a></div>
					<?php endif; ?> 
				</div>
			</div>
			
			<div class="content">
				<?= $content_for_layout ?>
			</div>
			
			<div style="clear: both; display: table"></div>
		</div>


		<div class="bottom">
			<div class="row1">
				<div class="span1" style="font-size: .8em; color: #444;">
					&copy; Magic3W - <?= date('Y') ?> - This software is licensed under the MIT License
				</div>
			</div>
		</div>
		
		<!--Cron scheduler-->
		<script type="text/javascript" src="<?= url('cron') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/ui-layout.js') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/dials.js') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/ui/form/styledElements.js') ?>" async="true"></script>
	</body>
</html>