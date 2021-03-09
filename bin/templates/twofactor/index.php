
<div class="spacer huge"></div>

<div class="row l1">
	<div class="span l1">
		<h1 class="text:grey-700">Authentication settings</h1>
		
		<div class="spacer medium"></div>
		
		<p>
			Multi factor authentication increases your account's security by requiring
			you to provide more than just a password. We allow you to use either 
			SMS authentication, backup codes, a TOTP device, or an Authenticator app.
		</p>
		
		<div class="spacer medium"></div>
		
		<?php if ($enabled): ?>
		<div class="material rounded less-padded">
			<div class="row s2">
				<div class="span s1"><strong>Two factor authentication is enabled</strong></div>
				<div class="span s1 align-right"><a class="button outline button-color-red-800 small" href="<?= url('twofactor', 'disable') ?>">Disable</a></div>
			</div>
		</div>
		<?php else: ?>
		<div class="material rounded less-padded">
			<div class="row s2">
				<div class="span s1"><strong>Two factor authentication is disabled</strong></div>
				<div class="span s1 align-right"><a class="button" href="<?= url('twofactor', 'enable') ?>">Enable</a></div>
			</div>
		</div>
		<?php endif ?>
	</div>
</div>

<div class="spacer huge"></div>

<div class="row l1">
	<div class="span l1">
		<div class="material unpadded">
			<div class="padded">
				<div class="row l9">
					<div class="span l1 desktop-only text:grey-700 align-center">
						<div class="spacer small"></div>
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
					</div>
					<div class="span l8">
						<h2 class="text:grey-700">Email addresses</h2>
						<p>
							If you lose access to your account, or prefer to log in using a log in link
							you can use your email address to authenticate. Please note that one email
							is required to use as your main account email.
						</p>

						<div class="spacer medium"></div>

						<?php foreach ($emails as $email): ?>
						<div>
							<strong><?= $email->content ?></strong> - <a href="">Remove</a>
						</div>
						<div>
							<span class="text:grey-700"><small>Added <?= date('M Y', $email->created) ?></small></span>
						</div>
						<?php endforeach ?>
						
						
					</div>
				</div>
				<div class="spacer medium"></div>
				
				<div class="align-right">
					<a class="button" href="<?= url(mfa\EmailController::class, 'create') ?>">Add email address</a>
				</div>
				
				<div class="spacer small"></div>
			</div>
			
			<div class="separator"></div>
			
			<div class="padded">
				<div class="row l9">
					<div class="span l1 desktop-only text:grey-700 align-center">
						<div class="spacer medium"></div>
						<svg style="width: 80%" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
					</div>
					<div class="span l8">
						<h2 class="text:grey-400">SMS Verification</h2>
						<p>
							Use your phone to prevent people who may have guessed your account's password
							from accessing your account. When attempting to log into your account, we will
							send you an SMS with a code that you have to type into the log in.
						</p>

						<div class="spacer medium"></div>

						<?php foreach ($phones as $phone): ?>
						<div>
							<strong><?= $phone->content ?></strong> - <a href="">Remove</a>
						</div>
						<div>
							<span class="text:grey-700"><small>Added <?= date('M Y', $phone->created) ?></small></span>
						</div>
						<?php endforeach ?>
						
						
					</div>
				</div>
				<div class="spacer medium"></div>
				
				<div class="align-right">
					<a class="button" href="<?= url('phone', 'create') ?>">Add phone</a>
				</div>
				
				<div class="spacer small"></div>
			</div>
			
			<div class="separator"></div>
			
			<div class="padded">
				<div class="row l9">
					<div class="span l1 desktop-only text:grey-700 align-center">
						<div class="spacer medium"></div>
						<svg style="width: 80%" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
					</div>
					<div class="span l8">
						<h2 class="text:grey-400">Backup codes</h2>
						<p>
							Click below to generate a set of random one time passwords to access 
							your account. Store these passwords in a safe location to ensure that,
							if your phone is lost you can access the account anyway.
						</p>

						<div class="spacer medium"></div>
						
						<span class="text:grey-700">
							<strong class="text:grey-300"><?= (string)$codes->count() ?></strong> codes available.
						</span>

						<div class="spacer medium"></div>

						<div class="align-right">
							<a class="button">Generate backup codes</a>
						</div>

						<div class="spacer small"></div>
					</div>
				</div>
				
			</div>
			
			<div class="separator"></div>
			
			<div class="padded">
				<div class="row l9">
					<div class="span l1 desktop-only text:grey-700 align-center">
						<div class="spacer medium"></div>
						<svg style="width: 80%" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
					</div>
					<div class="span l8">
						<h2 class="text:grey-400">TOTP / Authenticator</h2>
						<p>
							TOTP stands for Timed One Time Password. This protocol allows you 
							to use an app like Google Authenticator or your password manager
							of choice to generate codes that can be used to log in.
						</p>

						<div class="spacer medium"></div>
						

						<?php foreach ($totp as $device): ?>
						<div>
							<strong><?= $phone->content ?></strong> - <a href="">Remove</a>
						</div>
						<div>
							<span class="text:grey-700"><small>Added <?= date('M Y', $phone->created) ?></small></span>
						</div>
						<?php endforeach ?>

						<div class="spacer medium"></div>

						<div class="align-right">
							<a class="button">Pair TOTP device</a>
						</div>

						<div class="spacer small"></div>
					</div>
				</div>
				

			</div>
		</div>
	</div>
</div>

<div class="spacer huge"></div>
