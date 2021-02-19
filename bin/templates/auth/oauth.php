

<div class="spacer" style="height: 30px"></div>

<div class="row5">
	<div class="span1">
		
	</div>
	<div class="span3">
		<form method="POST" action="">
		<h1>Access <?= $client->name ?>?</h1>
		
		<div class="material">
			<p style="text-align: center">
				<img src="<?= url('image', 'app', $client->_id, 128) ?>" width="128" style="border-radius: 3px; border: solid 1px #777;">
				<img src="<?= \spitfire\core\http\URL::asset('img/right-arrow.png') ?>" style="margin: 4px 20px;">
				<img src="<?= url('image', 'user', $authUser->_id, 128) ?>" width="128"  style="border-radius: 3px; border: solid 1px #777;">
			</p>
			
			<p>
				If you wish to proceed and log into the application using your account,
				click "Continue". If you do not trust the application, just cancel the
				log in process - your account details won't be shared
			</p>
			
			<p style="text-align: center">
				<a href="<?= $cancel ?>">Cancel</a>
				<span style="display: inline-block; width: 20px"></span>
				<input type="hidden" name="grant" value="grant">
				<!-- TODO: Add XSRF -->
				<input type="submit" value="Grant access to your account" class="button">
			</p>
		</div>
	</div>
</div>
