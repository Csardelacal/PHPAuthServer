
<div class="row l1 m1 s1">
	<div class="span l1 m1 s1">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">Apps connected to this server</h1>
		</div>
		
		<p class="small">
			Below is a list of all the applications authorized to access user profiles
			on this server. By default, applications only have access to your public 
			information, your username and any data an administrator has granted it
			access to. You can find the exact data an application has access to by
			clicking on "Permissions" next to the app you wish to check, on that page
			you can also revoke any permissions that may have been granted.
		</p>
		
		<div class="spacer" style="height: 50px"></div>

		<?php foreach ($apps->records() as $app): ?>
		<?php $granted = db()->table('user\authorizedapp')->get('app', $app)->where('user', $authUser)->where('revoked', null)->first(); ?> 
		<div class="row l9 m4 s3 has-dials">
			<div class="span l1 m1 s1">
				<div style="text-align: center;">
					<img src="<?= url('image', 'app', $app->_id, 64) ?>" width="64" height="64" class="app-icon">
				</div>
			</div>
			
			<div class="span l6 m3 s2">
				<div><a href="<?= url('permissions', 'on', $app->_id) ?>" class="app-name"><?= __($app->name) ?></a></div>
				<div>
					<p class="small unpadded">
						<?php if ($granted): ?> 
						This application is connected to your account. You will not be prompted to authorize the application when logging into it.
						<?php else: ?> 
						Not connected to your account, we will ask you to proceed when it attempts to log you in.
						<?php endif; ?> 
					</p>
				</div>
			</div>
			
			<div class="span l2 dials">
				<ul>
					<li><a href="<?= url('permissions', 'on', $app->_id) ?>">Permissions</a></li>
					<?php if ($app->url): ?> 
					<li><a href="<?=$app->url ?>">Open application</a></li>
					<?php endif; ?> 
					<?php if ($granted): ?> 
					<li><a href="<?= url('permissions', 'deauthorize', $app->_id) ?>">Revoke connection</a>
					<?php endif; ?> 
				</ul>
			</div>
		</div>
		
		<div class="spacer" style="height: 20px"></div>
		<?php endforeach; ?>

		<?= $apps ?>
		
		<div class="spacer" style="height: 20px"></div>
		
		<p class="small secondary">
			System applications are not listed on this page. These applications are 
			not user manageable and only can be used by system administrators, these
			applications may have access to private data. If you wish to know what 
			system applications are being executed on this instance (if any) please
			contact administration.
		</p>
	</div>
</div>