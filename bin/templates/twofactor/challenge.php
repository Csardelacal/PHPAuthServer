
<div class="spacer large"></div>

<div class="row l5">
	<div class="span l2">
		<h1 class="text:grey-300">Enter the two factor token you received</h1>
		<p>
			<small>
				This section requires an additional and higher authentication than the
				current one. Please select a way to verify your identity further.
			</small>
		</p>
		<p>
			<small>
				You have not been logged out. This is just to prevent changes to the
				account to be made without your consent.
			</small>
		</p>
	</div>
	<div class="span l3">
		<div>
			<div class="material soft unpadded">
				<form method="POST" action="<?= url('twofactor', 'challenge', $provider->_id, ['returnto' => $_GET['returnto']]) ?>">
					<div class="padded">
						<h2>Enter the token</h2>
						<p>
							<small>
								Please enter the token into the box below.
							</small>
						</p>
						<input type="text" name="secret" class="frm-ctrl">
					</div>
					<div class="form-footer">
						<div class="padded align-right">
							<input type="submit" class="button">
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="spacer medium"></div>
	</div>
</div>
