

<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1">
		<img class="app-icon">
		<h1>Use your account with <?= $token->app->name ?>?</h1>
		
		<div class="material">
			<p>
				If you wish to proceed and log into the application using your account,
				click "Continue". If you do not trust the application, just cancel the
				log in process - your account data won't be shared
			</p>
			
			<p style="text-align: center">
				<a href="<?= $cancelURL ?>">Cancel</a>
				<a href="<?= $continue?>" class="button">Continue</a>
			</p>
		</div>
	</div>
</div>