
<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<h1 class="text:grey-300">Connect your authenticator</h1>
	</div>
</div>

<div class="row l1">
	<div class="span l1">
		<p class="text:grey-500">
			<small>
				Use your mobile device to scan the code below, or copy the secret into 
				a compatible authenticator application. <strong>This code is only displayed
				once, please make sure to successfully pair your device before you refresh or
				leave this page.</strong>
			</small>
		</p>
		
		<div class="spacer large"></div>
		
		<div class="row l2">
			<div class="span l1">
				<img src="about:blank" class="totp-secret" style="box-shadow: 0 0 7px #CCC" data-secret="<?= $secret ?>">
				<script src="/Auth/public/assets/js/totp.js"></script>
			</div>
			<div class="span l1">
				<div class="spacer large"></div>
				
				<p class="text:grey-500">
					If you're using a device without a camera, enter the following secret
					into the device.
				</p>
				
				<div class="spacer medium"></div>
				
				<p class="text:grey-900 align-center"><strong><?= __($secret) ?></strong></p>
				
				<div class="spacer medium"></div>
				
				<p class="text:grey-500">
					In case your device supports multiple authentication mechanisms,
					select RFC6832 as the authentication standard.
				</p>
				
				<div class="spacer medium"></div>
				
				<p class="text:grey-700">
					Once you're ready, click <strong>verify</strong> to check that the pairing
					was successful.
				</p>
				
				<div class="spacer medium"></div>
				
				<div class="align-center">
					<a class="button" href="<?= url(['mfa', 'totp'], 'challenge') ?>">Verify</a>
				</div>
			</div>
		</div>
	</div>
</div>
