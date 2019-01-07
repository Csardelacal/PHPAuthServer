<!DOCTYPE html>
<html>
	<head>
		<title><?= isset(${'page.title'}) && ${'page.title'}? ${'page.title'} : 'Account server' ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="_scss" content="<?= \spitfire\SpitFire::baseUrl() ?>/assets/scss/_/js/">
		<link rel="stylesheet" type="text/css" href="<?= spitfire\core\http\URL::asset('css/app.css') ?>">
		<link rel="stylesheet" type="text/css" href="<?= spitfire\core\http\URL::asset('css/ui-layout.css') ?>">
		
		<script>
			window.baseURL = <?= json_encode(strval(url())); ?>
		</script>
	</head>
	<body>
		<script>
		/*
		 * This little script prevents an annoying flickering effect when the layout
		 * is being composited. Basically, since we layout part of the page with JS,
		 * when the browser gets to the JS part it will discard everything it rendered
		 * to this point and reflow.
		 * 
		 * Since the reflow MUST happen in order to render the layout, we can tell 
		 * the browser to not render the layout at all. This will prevent the layout
		 * from shift around before the user had the opportunity to click on it.
		 * 
		 * If, for some reason the layout was unable to start up within 500ms, we 
		 * let the browser render the page. Risking that the browser may need to 
		 * reflow once the layout is ready
		 */
		(function() {
			document.body.style.display = 'none';
			document.addEventListener('DOMContentLoaded', function () { document.body.style.display = null; }, false);
			setTimeout(function () { document.body.style.display = null; }, 500);
		}());
		</script>
		
		<div class="navbar">
			<div class="left">
				<span class="toggle-button dark"></span>
				<a href="<?= url() ?>">Account</a>
			</div>
			<div class="right">
				<?php if(isset($authUser)): ?>
				<div class="has-dropdown" style="display: inline-block">
					<span class="app-switcher toggle" data-toggle="app-drawer"></span>
					<div class="dropdown right-bound unpadded" data-dropdown="app-drawer">
						<div class="app-drawer" id="app-drawer"></div>
					</div>
				</div>
				<span class="h-spacer" style="display: inline-block; width: 20px;"></span>
				<?php endif; ?>
			</div>
		</div>
		
		<div class="auto-extend">
			<div class="contains-sidebar">
				<div class="sidebar">
					<div class="spacer" style="height: 20px"></div>
					
                                        <?php if(isset($authUser)): ?>
					<div class="menu-title"> Account</div>
					<div class="menu-entry"><a href="<?= url() ?>"                  >Edit profile</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'email')    ?>">Change email address</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'password') ?>">Change password</a></div>
					<div class="menu-entry"><a href="<?= url('edit', 'avatar') ?>"  >Upload avatar</a></div>
					<div class="menu-entry"><a href="<?= url('permissions') ?>"     >Application permissions</a></div>
					
					<?php if(isset($userIsAdmin) && $userIsAdmin): ?> 
					<div class="spacer" style="height: 30px"></div>
					<div class="menu-title">Administration</div>
					<div class="menu-entry"><a href="<?= url('user')  ?>">Users</a></div>
					<div class="menu-entry"><a href="<?= url('group') ?>">Groups</a></div>
					<div class="menu-entry"><a href="<?= url('admin') ?>">System settings</a></div>
					<div class="menu-entry"><a href="<?= url('token') ?>">Active sessions</a></div>
					
					<!--APPLICATIONS-->
					<div class="menu-entry"><a href="<?= url('app') ?>"  >App administration</a></div>
					
					<!-- EMAIL -->
					<div class="menu-entry"><a href="<?= url('email') ?>">Email</a></div>
					<div class="indented">
						<div class="menu-entry"><a href="<?= url('email', ['history' => 1]) ?>">History</a></div>
						<div class="menu-entry"><a href="<?= url('email', 'domain') ?>">Domains</a></div>
					</div>
					<?php endif; ?> 
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
				<div class="row l1">
					<div class="span l1">
						<div class="message error">
							Your account has not yet been activated. <a href="<?= url('user', 'activate') ?>">Resend activation mail</a>
						</div>
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
		document.addEventListener('DOMContentLoaded', function () {
			var ae = document.querySelector('.auto-extend');
			var wh = window.innerheight || document.documentElement.clientHeight;
			var dh = document.body.clientHeight;
			
			ae.style.minHeight = Math.max(ae.clientHeight + (wh - dh), 0) + 'px';
		});
		</script>
		
		<!--Import depend.js and the router it uses to load locations -->
		<script src="<?= spitfire\core\http\URL::asset('js/depend.js') ?>" type="text/javascript"></script>
		<script src="<?= spitfire\core\http\URL::asset('js/m3/depend/router.js') ?>" type="text/javascript"></script>
		<script type="text/javascript">
		(function () {
			depend(['m3/depend/router'], function(router) {
				router.all().to(function(e) { return '<?= \spitfire\SpitFire::baseUrl() . '/assets/js/' ?>' + e + '.js'; });
				router.equals('phpas/app/drawer').to( function() { return '<?= url('appdrawer')->setExtension('js') ?>'; });
				router.equals('_scss').to( function() { return '<?= \spitfire\SpitFire::baseUrl() ?>/assets/scss/_/js/_.scss.js'; });
			});
			
			depend(['ui/dropdown'], function (dropdown) {
				dropdown('.app-switcher');
			});
			
			depend(['phpas/app/drawer'], function (drawer) {
				console.log(drawer);
			});
			
			depend(['_scss'], function() {
				//Loaded
			});
			
		}());
		</script>
		
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/dials.js') ?>" async="true"></script>
		<script type="text/javascript" src="<?= spitfire\core\http\URL::asset('js/ui/form/styledElements.js') ?>" async="true"></script>
		
		
		<script type="text/javascript">
			depend(['sticky'], function (sticky) {
				
				/*
				 * Create elements for all the elements defined via HTML
				 */
				var els = document.querySelectorAll('*[data-sticky]');

				for (var i = 0; i < els.length; i++) {
					sticky.stick(els[i], sticky.context(els[i]), els[i].getAttribute('data-sticky'));
				}
			});
		</script>
	</body>
</html>