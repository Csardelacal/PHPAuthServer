

<div class="spacer" style="height: 30px"></div>

<div class="row5">
	<div class="span1">
		
	</div>
	<div class="span3">
		<h1>Access <?= $token->app->name ?>?</h1>
		
		<div class="material">
			<p style="text-align: center">
				<img src="<?= new URL('image', 'user', $authUser->_id, 128) ?>" width="128"  style="border-radius: 3px; border: solid 1px #777;">
				<img src="<?= URL::asset('img/right-arrow.png') ?>" style="margin: 4px 20px;">
				<img src="<?= new URL('image', 'app', $token->app->_id, 128) ?>" width="128" style="border-radius: 3px; border: solid 1px #777;">
			</p>
			
			<p>
				If you wish to proceed and log into the application using your account,
				click "Continue". If you do not trust the application, just cancel the
				log in process - your account details won't be shared
			</p>
			
			<p style="text-align: center">
				<a href="<?= $cancelURL ?>">Cancel</a>
				<span style="display: inline-block; width: 20px"></span>
				<a href="<?= $continue?>" class="button">Continue</a>
			</p>
		</div>
	</div>
</div>
