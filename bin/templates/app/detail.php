
<div class="topbar sticky">
	<div class="row5 fluid">
		<div class="span4">
			<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
			Application Details
		</div>
		<div class="span1 desktop-only" style="text-align: right">
			<a class="button error" style="font-size:.7em; line-height: 1.6em; display: inline-block" href="<?= url('app', 'delete', $app->_id) ?>">Delete App</a>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1 fluid">
	<div class="span1">
		<p style="font-size: .8em; color: #555;">
			These are the oAuth codes and settings for your application. You can use
			them to generate secure tokens for your app and authenticate users.
		</p>
	</div>
</div>

<div class="spacer" style="height: 40px"></div>

<form class="regular" method="POST" enctype="multipart/form-data">
	<!-- App Name -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .75em; color: #555">
				Name
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="property-value" id="name-container">
					<span id="name-display" class="fake-field-ph"><?= __($app->name) ?></span>
					<div class="edit-field hidden"><input type="text" name="name" id="name-input" disabled></div>
				</div>
			</div>
		</div>
		<div class="span1 dials">
			<ul>
				<li>
					<a href="#change" id="change-name">Change</a>
					<a href="#change" id="cancel-name" class="hidden cancel">Cancel</a>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- App URL -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .75em; color: #555">
				Public URL (users can click this to be directed to the app)
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="field">
					<input type="text" name="url" value="<?= __($app->url) ?>">
				</div>
			</div>
		</div>
		<div class="span1 dials">
			<ul>
				<li>
					<!-- No dials yet-->
				</li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- App ID -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				App ID
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="property-value" id="id-container"><span class="fake-field"><?= __($app->appID) ?></span></div>
			</div>
		</div>
		<div class="span1 dials">
			<ul>
				<li>
					<a id="id-copy" href="#copy">Copy</a>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- App Secret -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				App Secret
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="property-value" id="secret-container" data-actual="<?= __($app->appSecret) ?>"><em class="fake-field-ph">Hidden</em></div>
			</div>
		</div>
		<div class="span1 dials">
			<ul>
				<li><a id="secret-copy" href="#copy">Copy</a></li>
				<li><a id="secret-hidden" href="#reveal">Reveal</a><a id="secret-visible" class="hidden" href="#hide">Hide</a></li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- Icon -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				Icon
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="property-value">
					<img src="<?= url('image','app', $app->_id, 128, ['nonce' => time()]) ?>" class="user-icon square big" id="icon-display">
					<div class="edit-field hidden" id="icon-upload-wrap">
						<input type="file" name="icon" id="icon-input" accept="image/png,image/jpeg,image/gif" disabled>
					</div>
				</div>
			</div>
		</div>
		<div class="span1 dials">
			<ul>
				<li><a href="#change" id="change-icon">Change</a><a href="#change" id="cancel-icon" class="hidden cancel">Cancel</a></li>
			</ul>
		</div>
	</div>
	
	<div class="separator"></div>
	
	<div class="row1 fluid">
		<div class="span1" style="text-align: right">
			<input type="submit" class="button success" value="Save changes">
		</div>
	</div>
</form>

<div class="spacer" style="height: 100px"></div>


<div class="topbar sticky">
	<div class="row5 fluid">
		<div class="span4">
			<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
			Webhooks
		</div>
		<div class="span1 desktop-only" style="text-align: right">
			<a class="button" style="font-size:.7em; line-height: 1.6em; display: inline-block" href="<?= url('webhook', 'attach', $app->_id) ?>">Add webhook</a>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<?php $webhooks = db()->table('webhook\hook')->get('app', $app)->fetchAll(); ?>
<?php foreach ($webhooks as $webhook): ?>
<div class="row5 fluid has-dials">
	<div class="span1">
		<div style="font-size: .85em; color: #555"><?= implode('::', $webhook->mask2Array()) ?></div>
	</div>
	<div class="span3">
		<div style="font-size: .85em; color: #555"><?= $webhook->name ?> (<?= $webhook->url ?>)</div>
	</div>
	<div class="span1 dials">
		<ul>
			<li><a href="<?= url('webhook', 'edit', $webhook->_id) ?>">Edit</a></li>
			<li><a href="<?= url('webhook', 'history', $webhook->_id) ?>">Queue</a></li>
			<li><a href="<?= url('webhook', 'delete', $webhook->_id) ?>">Remove</a></li>
		</ul>
	</div>
</div>
	
<div class="separator large light"></div>
<?php endforeach; ?>

<?php if ($webhooks->isEmpty()): ?>
<div style="padding: 50px; text-align: center; font-style: italic; color: #666">
	No webhooks defined
</div>
<?php endif; ?>


<!-- Go on about the contexts defined by this application-->
<div class="spacer" style="height: 50px"></div>

<div class="topbar sticky">
	<div class="row5 fluid">
		<div class="span5">
			<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
			Contexts
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<?php $contexts = db()->table('connection\context')->get('app', $app)->addRestriction('expires', time(), '>')->fetchAll(); ?>
<?php foreach ($contexts as $context): ?>
<div class="row5 fluid has-dials">
	<div class="span4">
		<div style="font-size: .85em; color: #000"><?= __($context->title) ?></div>
		<div style="font-size: .75em; color: #555"><?= __($context->descr, 200) ?></div>
	</div>
	<div class="span1 dials">
		<ul>
			<li><a href="<?= url('context', 'edit', $app->_id, $context->ctx) ?>">Edit</a></li>
			<li><a href="<?= url('context', 'revoke', $app->_id, $context->ctx) ?>">Revoke</a></li>
			<li><a href="<?= url('context', 'granted', $app->_id, $context->ctx) ?>">Applications</a></li>
		</ul>
	</div>
</div>
	
<div class="separator large light"></div>
<?php endforeach; ?>

<?php if ($contexts->isEmpty()): ?>
<div style="padding: 50px; text-align: center; font-style: italic; color: #666">
	This application has defined no contexts
</div>
<?php endif; ?>


<div class="spacer" style="height: 50px"></div>

<script src="<?= spitfire\core\http\URL::asset('js/app-detail.min.js')?>"></script>
