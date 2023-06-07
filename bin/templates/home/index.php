<?php /** @var $user UserModel */ ?>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="row l1">
	<div class="span l1">
		<div class="message success">
			Request successful, an email has been sent to your email address. <strong>To log into applications you must verify your email address</strong>
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row l1 s1">
	<div class="span l1 s1">
		<div data-sticky-context>
			<div class="heading topbar" data-sticky>
				Edit your profile
			</div>

			<div class="spacer" style="height: 30px"></div>
			
			<div class="row l4 m3 s1 fluid">
				<div class="span l1 m1 s1" style="text-align: center">
					<!--Avatar-->
					<a href="<?= url('edit', 'avatar') ?>">
						<img src="<?= url('image', 'user', $user->_id, 256) ?>" class="user-icon square full-width not-mobile">
						<img src="<?= url('image', 'user', $user->_id, 256) ?>" class="user-icon round big mobile-only">
					</a>
					
					<div class="spacer" style="height: 20px"></div>
					
					<h1>
						<a href="<?= url('edit', 'username') ?>"><?= ucfirst($user->usernames->getQuery()->where('expires', null)->first()->name) ?></a>
					</h1>

					<div>
						<?php if ($user->verified): ?> 
						Email verified
						<?php else: ?> 
						<a href="<?= url('user', 'activate') ?>">Verify your account</a>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="spacer mobile-only" style="height: 30px;"></div>
				
				<div class="span l3 m2 s1">
					
					<div class="material">
						<?php $attributes = db()->table('attribute')->get('writable', Array('me', 'public'))->fetchAll(); ?>
						<?php foreach ($attributes as $attribute): ?>
						<div class="row l6 s3 fluid has-dials">
							<div class="span l1 s1">
								<div style="font-size: .75em; color: #555">
									<?= $attribute->name ?>
								</div>
							</div>
							<div class="span l4 s2">

								<?php $attrValue = $user->attributes->getQuery()->addRestriction('attr', $attribute)->fetch() ?>
								<div style="font-size: .85em; color: #333;">
									<div>
										<?php if ($attribute->datatype === 'file'): ?>
										<div style="text-align: center">
											<img src="<?= url('image', 'attribute', $attribute->_id, $user->_id, ['nonce' => time()]) ?>" style="max-width: 100%; max-height: 200px">
										</div>
										<?php elseif ($attribute->datatype === 'text'): ?>
										<div style="white-space: pre-wrap;"><?= $attrValue? __($attrValue->value, 200) : '<em>Undefined</em>' ?></div>
										<?php elseif ($attribute->datatype === 'boolean'): ?>
										<div><?= $attrValue->value? 'Yes' : 'No' ?></div>
										<?php else: ?>
										<div><?= $attrValue? __($attrValue->value, 45) : '<em>Undefined</em>' ?></div>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<div class="span l1 dials">
								<ul>
									<li><a href="<?= url('edit', 'attribute', $attribute->_id) ?>">Edit</a></li>
								</ul>
							</div>
						</div>
						<div class="spacer" style="height: 25px"></div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="spacer" style="height: 30px;"></div>

			<!-- 
				List the user's authorized apps. This way they can see what applications they
				can log into with their account without confirmation.
			-->
			<?php $authorized = db()->table(user\AuthorizedappModel::class)->get('user', $user)->fetchAll(); ?>

			<div class="heading topbar" data-sticky>
				Authorized apps
			</div>

			<?php foreach ($authorized as $auth): ?>
			<?php $app = $auth->app ?>
			<div class="spacer" style="height: 15px"></div>
			<div class="row l6 fluid has-dials">
				<div class="span l5">
					<img src="<?= url('image', 'app', $app->_id, 32) ?>" style="vertical-align: middle;  height: 18px;">

					<?php if ($app->url): ?><a href="<?= $app->url ?>"><?= $app->name ?></a>
					<?php else: ?><span><?= $app->name ?></span><?php endif; ?>
				</div>
				<div class="span l1 dials">
					<ul>
						<li><a href="<?= url('permissions', 'deauthorize', $app->_id) ?>">Remove</a></li>
						<li><a href="<?= url('permissions', 'on', $app->_id) ?>">Permissions</a></li>
					</ul>
				</div>
			</div>
			<?php endforeach; ?>

			<div class="spacer" style="height: 30px"></div>


			<?php $sessions = db()->table('session')->get('user', $user)->where('expires', '>', time())->all(); ?>
			<div class="heading topbar" data-sticky>
				Active sessions
			</div>

			<?php foreach ($sessions as $session): ?>
			<div class="h-6"></div>
			<div class="container mx-auto p-4 bg-white border border-gray-100 rounded box-shadow flex justify-between">
				<div>
					<div class="text-sm text-gray-600">Started <?= date('M d Y', $session->created) ?></div>
					<div class="h-2"></div>
					<div class="flex items-center gap-2">
						<img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/1x1/<?= strtolower($session->country?: 'DE') ?>.svg" class="w-5 h-5 rounded-full">
						<span><?= $session->city?: 'Unknown city' ?></span>
					</div>
				</div>
				<div>
					<a href="<?= url('session', 'end', $session->_id) ?>" class="gap-1 text-gray-600 hover:text-gray-800 flex items-center">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
						<span class="text-sm">End session</span>

					</a>
				</div>
			</div>
			<?php endforeach; ?>

			<div class="spacer" style="height: 50px"></div>
		</div>
	</div>
</div>	
	
