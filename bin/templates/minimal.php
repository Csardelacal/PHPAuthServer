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
	<body class="bg-slate-100">
		
		<div class="auto-extend">
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
				<?= $this->content() ?>

				<div style="clear: both; display: table"></div>
			</div>
		</div>
		
		<footer class="py-3 text-xs text-gray-600 text-center">
			&copy; <?= date('Y') ?> Magic3W - This software is licensed under MIT License
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
	</body>
</html>