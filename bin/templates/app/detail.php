
<div class="row l1">
	<div class="span">
		<div class="row l5">
			<div class="span l4">
				<h1>
					<img src="<?= url('image', 'icon', $app->icon->_id, ['nonce' => time()]) ?>" class="user-icon square medium">
					<?= __($app->name) ?>
				</h1>
			</div>
			<div class="span l1" style="text-align: right">
				<a class="button small solid button-color-purple-600" href="<?= url('app', 'delete', $app->_id) ?>">Delete App</a>
			</div>
		</div>
	</div>
</div>

<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<p class="text:grey-600">
			These are the oAuth codes and settings for your application. You can use
			them to generate secure tokens for your app and authenticate users.
		</p>
	</div>
</div>

<div class="spacer medium"></div>

<!-- App ID -->
<div class="row l1">
	<div class="span l1">
		<div class="box padded box-soft box-drop-shadow bg-white" style="background: #FFF">
			<div class="spacer small"></div>
			<div class="text:grey-400">
				<small>ID</small>
			</div>

			<div>
				<span style="font-size: 3.1rem"><strong><?= __($app->appID) ?></strong></span>
				<span title="Copy to clipboard" data-clipboard="<?= __($app->appID) ?>" class="text:grey-500" style=" cursor: pointer; white-space: nowrap">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="height: 1.1rem; vertical-align: -.15rem">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
					</svg>
					<span data-caption>Copy</span>
				</span>
			</div>

			<div class="spacer medium"></div>

			<div class="text:grey-400">
				<small>Secrets</small>
			</div>

			<?php foreach ($app->credentials as $credential): ?>
			<div>
				<div class="spacer small"></div>
				<div>
					<span class="toggle-blur blurred"><?= __($app->appSecret) ?></span>
					<div class="horizontal-spacer"></div>
					<span title="Copy to clipboard" data-clipboard="<?= __($app->appSecret) ?>" class="text:grey-500" style=" cursor: pointer; white-space: nowrap">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="height: 1.1rem; vertical-align: -.15rem">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
						</svg>
						<span data-caption>Copy</span>
					</span>
				</div>
			</div>
			<?php endforeach; ?>
			<div class="spacer small"></div>
		</div>
		<div class="align-right">
			<div class="spacer small"></div>
			<a href="" class="button small borderless button-color-grey-600">Expire a secret</a>
			<div class="horizontal-spacer"></div>
			<a href="" class="button small outline" style="font-weight: bold">Add another secret</a>
		</div>
	</div>
</div>

<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<p class="text:grey-500" style="font-size: .8rem">
			You must never share your secret with any other person. If you suspect somebody 
			received access to your secret, plase use the expire secret button to remove 
			the secret. If you suspect that your secret has been leaked, please proceed to 
			evict all tokens generated by your application.
		</p>
	</div>
</div>

<div class="spacer large"></div>
	
<!-- Icon -->
<div class="row l1">
	<div class="span l1">
		<div class="box padded box-soft">
			<div class="row l5">
				<div class="span l1">
					<img src="<?= url('image','icon', $app->icon->_id, 128, ['nonce' => time()]) ?>" class="user-icon square big" id="icon-display">
					<div style="display: none"><input type="file" name="icon" id="icon-input" accept="image/png,image/jpeg,image/gif" data-submit-url="<?= url('app', 'putIcon', $app->_id) ?>"></div>
				</div>
				
				<div class="span l4">
					<!-- TODO: We could put the options here to change the application's name-->
					<a class="button small" id="app-icon-button">Change the application icon</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="separator"></div>

<div class="spacer large"></div>


<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<div class="row l5 m4 s3 fluid">
				<div class="span l4 m3 s2">
					<h1 class="unpadded">Webhooks</h1>
				</div>
				<div class="span l1 m1 s1" style="text-align: right">
					<a class="button" style="font-size:.6em;" href="<?= url('webhook', 'attach', $app->_id) ?>">Add webhook</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="span">
		<p class="secondary small">
			Lists the webhooks this application is listening for. This only lists
			webhooks sent from PHPAS, to see all webhooks this application is listening
			to please consult the application's manual. Webhooks are provided by a
			external application called <strong>Cptn.H00k</strong>, if the application
			is not configured correctly it will not work properly.
		</p>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row">
	<div class="span">
		<?php foreach ($webhooks as $webhook): ?>
		<div class="row l5 has-dials">
			<div class="span l1">
				<div><?= __($webhook->on->event) ?></div>
			</div>
			<div class="span l3">
				<div style="font-size: .85em; color: #555"><?= $webhook->id ?> (<?= $webhook->url ?>)</div>
			</div>
			<div class="span l1 dials">
				<ul>
					<!--<li><a href="<?= url('webhook', 'edit', $webhook->_id) ?>">Edit</a></li>
					<li><a href="<?= url('webhook', 'history', $webhook->_id) ?>">Queue</a></li>
					<li><a href="<?= url('webhook', 'delete', $webhook->_id) ?>">Remove</a></li>-->
				</ul>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>

<?php if (empty($webhooks)): ?>
<div style="padding: 50px; text-align: center; font-style: italic; color: #666">
	No webhooks defined
</div>
<?php endif; ?>

<div class="spacer" style="height: 30px"></div>

<!-- Go on about the contexts defined by this application-->
<div class="spacer" style="height: 50px"></div>

<div class="row l1">
	<div class="span l1">
		<h1>Scopes</h1>
	</div>
</div>

<div class="spacer small"></div>

<div class="row l1">
	<div class="span l1">
		<p class="text:grey-600">
			<small>
			This is the list of scopes that your application has defined. Scopes fence off
			some components of your application in order to let users decide with more
			granularity wether they wish to grant access to other applications to the 
			data your application contains and provides through it's API.
			
			You can't edit the scopes from the user interface, please refer to the 
			SDK or API documentation to see how this data is managed.
			</small>
		</p>
	</div>
</div>

<div class="spacer medium"></div>

<?php $scopes = db()->table('client\scope')->getAll()->where('identifier', 'LIKE', $app->appID . '.%')->all(); ?>
<?php foreach ($scopes as $scope): ?>
<div class="row l1">
	<div class="span l1">
		<div class="box padded box-soft">
			<div class="row s6 l9">
				<div class="span s1 l1">
					<img src="<?= url('image', 'icon', $scope->icon->_id) ?>" height="128" width="128">
				</div>
				<div class="span s5 l6">
					<div><span class="text:grey-500"><?= __($scope->identifier, 200) ?></span></div>
					<div class="spacer minuscule"></div>
					<div class="text:grey-700"><strong><?= __($scope->caption) ?></strong></div>
					<div class="text:grey-500"><?= __($scope->description, 200) ?></div>
				</div>
				<div class="span l2 align-right not-mobile">
					<span title="Copy to clipboard" data-clipboard="<?= __($scope->identifier) ?>" class="text:grey-500" style=" cursor: pointer; white-space: nowrap">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="height: 1.1rem; vertical-align: -.15rem">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
						</svg>
						<span data-caption>Copy</span>
					</span>
				</div>
			</div>
		</div>
	</div>
</div>
	
<div class="spacer medium"></div>
<?php endforeach; ?>

<?php if ($scopes->isEmpty()): ?>
<div class="align-center text:grey-500" style="padding: 50px; font-style: italic;">
	This application has defined no scopes
</div>
<?php endif; ?>

<div class="spacer" style="height: 50px"></div>

<script src="<?= spitfire\core\http\URL::asset('js/app-detail.js')?>"></script>

<script>
(function () {
	document.querySelectorAll('.toggle-blur').forEach(function (e) {
		e.addEventListener('click', function () { this.classList.toggle('blurred'); });
	});
	
	document.querySelectorAll('[data-clipboard]').forEach(function (e) {
		e.addEventListener('click', async function () { 
			var self = this;
			if (!navigator.clipboard) { throw "Clipboard unavailable"; }
			await navigator.clipboard.writeText(this.dataset.clipboard);
			self.classList.add('text:green-600');
			self.querySelector('[data-caption]').innerHTML = 'Copied!'
			setTimeout(function () { 
				self.classList.remove('text:green-600'); 
				self.querySelector('[data-caption]').innerHTML = 'Copy'
			}, 800);
		});
	});
}());
</script>