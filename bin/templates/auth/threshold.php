
<div class="spacer large"></div>

<div class="row l5">
	<div class="span l2">
		<h1 class="text:grey-300">Additional authentication required</h1>
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
		
		<?php foreach ($providers as $provider) : ?>
		<div>
			<?php if ($provider->type == 'password') : ?>
			<div class="material soft unpadded">
				<form method="POST" action="<?= url('password', 'verify', $provider->_id, ['returnto' => strval(spitfire\core\http\URL::current())]) ?>">
					<div class="padded">
						<h2>Confirm your password</h2>
						<p>
							<small>
								Re-enter your password to verify your identity.
							</small>
						</p>
						<input type="password" name="password" class="frm-ctrl">
					</div>
					<div class="form-footer">
						<div class="padded align-right">
							<input type="submit" class="button">
						</div>
					</div>
				</form>
			</div>
			<?php endif; ?>
			<?php if ($provider->type == 'email') : ?>
			<div class="material soft unpadded">
				<form method="POST" action="<?= url('email', 'verify', $provider->_id, ['returnto' => strval(spitfire\core\http\URL::current())]) ?>">
					<div class="padded">
						<?php list($id, $domain) = explode('@', $provider->content); ?>
						<h2>Send a verification code to <?= substr($id, 0, 2) ?><?= str_repeat('*', mb_strlen($id) - 2) ?>@<?= $domain ?></h2>
						<p>
							<small>
								Have a token sent to your email address.
							</small>
						</p>
					</div>
					<div class="form-footer">
						<div class="padded align-right">
							<a class="button" href="<?= url('email', 'twofactor', $provider->_id, ['returnto' => strval(spitfire\core\http\URL::current())]) ?>">Verify using email now</a>
						</div>
					</div>
				</form>
				
				<div class="spacer small"></div>
			</div>
			<?php endif; ?>
			<?php if ($provider->type == 'phone') : ?>
			<div class="material soft unpadded">
				<form method="POST" action="<?= url('email', 'verify', $provider->_id, ['returnto' => strval(spitfire\core\http\URL::current())]) ?>">
					<div class="padded">
						<?php $id = $provider->content; ?>
						<h2>Send a verification code to <?= substr($id, 0, 5) ?><?= str_repeat('*', mb_strlen($id) - 7) ?><?= substr($id, -2) ?></h2>
						<p>
							<small>
								Have a token sent to your phone number.
							</small>
						</p>
					</div>
					<div class="form-footer">
						<div class="padded align-right">
							<a class="button" href="<?= url('phone', 'twofactor', $provider->_id, ['returnto' => strval(spitfire\core\http\URL::current())]) ?>">Verify with an SMS message</a>
						</div>
					</div>
				</form>
				
				<div class="spacer small"></div>
			</div>
			<?php endif; ?>
		</div>
		<div class="spacer medium"></div>
		<?php endforeach; ?>
	</div>
</div>
