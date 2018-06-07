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
			<div class="left">
				<span class="toggle-button dark"></span>
				<a href="<?= url() ?>">Account</a>
			</div>
			<div class="right">
				<?php if(isset($authUser)): ?>
				<a href="<?= url('user', 'logout') ?>">Logout</a>
				<?php endif; ?>
			</div>
		</div>
		
		<div class="auto-extend">
			<div class="contains-sidebar">
				<div class="sidebar">
					<div class="spacer" style="height: 20px"></div>
					
					<div class="menu-title"> Account</div>
					<div class="menu-entry"><a href="<?= url() ?>"                  >Edit profile</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'email')    ?>">Change email address</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'password') ?>">Change password</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'avatar') ?>"  >Upload avatar</a></div>
					<div class="menu-entry"><a href="<?= url('permissions') ?>"     >Application permissions</a></div>
					
					<?php if(isset($userIsAdmin) && $userIsAdmin): ?> 
					<div class="spacer" style="height: 30px"></div>
					<div class="menu-title">Administration</div>
					<div class="menu-entry"><a href="<?= url('group') ?>">Groups</a></div>
					<div class="menu-entry"><a href="<?= url('admin') ?>">System settings</a></div>
					<div class="menu-entry"><a href="<?= url('token') ?>">Active sessions</a></div>
					
					<!--APPLICATIONS-->
					<div class="menu-entry"><a href="<?= url('app') ?>"  >App administration</a></div>
					<div class="indented">
						<div class="menu-entry"><a href="<?= url('context') ?>">Contexts</a></div>
						<div class="menu-entry"><a href="<?= url('connect') ?>">Connections</a></div>
						<div class="menu-entry"><a href="<?= url('grant')   ?>">Grants</a></div>
					</div>
					
					<!-- EMAIL -->
					<div class="menu-entry"><a href="<?= url('email') ?>">Email</a></div>
					<div class="indented">
						<div class="menu-entry"><a href="<?= url('email', 'domain') ?>">Domains</a></div>
					</div>
					<?php endif; ?> 
					
				</div>
			</div><!--
			--><div class="content">
		
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
				
				<div  data-sticky-context>
					<?= $this->content() ?>
				</div>
			</div>
			
		</div>
		
		<footer>
			<div class="row1">
				<div class="span1">
					<span style="font-size: .8em; color: #777">
						&copy; <?= date('Y') ?> Magic3W - This software is licensed under MIT License
					</span>
				</div>
			</div>
		</footer>
		
		<script type="text/javascript">
		(function () {
			var ae = document.querySelector('.auto-extend');
			var wh = window.innerheight || document.documentElement.clientHeight;
			var dh = document.body.clientHeight;
			
			ae.style.minHeight = Math.max(ae.clientHeight + (wh - dh), 0) + 'px';
		}());
		</script>
		
		<!--Cron scheduler-->
		<script type="text/javascript" src="<?= url('cron') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/ui-layout.js') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/sticky.js') ?>"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/dials.js') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/ui/form/styledElements.js') ?>" async="true"></script>
	</body>
</html>