
<div class="row l1">
	<div class="span">
		<div class="heading" data-sticky="top">
			<div class="row l5">
				<div class="span l4">
					<h1 class="unpadded">Application Details</h1>
				</div>
				<div class="span l1" style="text-align: right">
					<a class="button error" style="font-size:.6em; display: inline-block" href="<?= url('app', 'delete', $app->_id) ?>">Delete App</a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row l1">
	<div class="span l1">
		<p class="secondary small">
			These are the oAuth codes and settings for your application. You can use
			them to generate secure tokens for your app and authenticate users.
		</p>
	</div>
</div>

<div class="spacer" style="height: 40px"></div>

<form class="regular" method="POST" enctype="multipart/form-data">
	<!-- App Name -->
	<div class="row l5 has-dials">
		<div class="span l4">
			<div style="font-size: .75em; color: #555">
				Name
			</div>
			
			<div class="property-value" id="name-container">
				<span id="name-display" class="fake-field-ph"><?= __($app->name) ?></span>
				<div class="edit-field hidden"><input type="text" name="name" id="name-input" disabled></div>
			</div>
		</div>
		<div class="span l1 dials">
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
	<div class="row l5 has-dials">
		<div class="span l4">
			<div style="font-size: .75em; color: #555">
				Public URL (users can click this to be directed to the app)
			</div>
			
			<div class="field">
				<input type="text" name="url" value="<?= __($app->url) ?>">
			</div>
		</div>
		<div class="span l1 dials">
			<ul>
				<li>
					<!-- No dials yet-->
				</li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- App ID -->
	<div class="row l5 has-dials">
		<div class="span l4">
			<div style="font-size: .85em; color: #555">
				App ID
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="property-value" id="id-container"><span class="fake-field"><?= __($app->appID) ?></span></div>
			</div>
		</div>
		<div class="span l1 dials">
			<ul>
				<li>
					<a id="id-copy" href="#copy">Copy</a>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- App Secret -->
	<div class="row l5 has-dials">
		<div class="span l4">
			<div style="font-size: .85em; color: #555">
				App Secret
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="property-value" id="secret-container" data-actual="<?= __($app->appSecret) ?>"><em class="fake-field-ph">Hidden</em></div>
			</div>
		</div>
		<div class="span l1 dials">
			<ul>
				<li><a id="secret-copy" href="#copy">Copy</a></li>
				<li><a id="secret-hidden" href="#reveal">Reveal</a><a id="secret-visible" class="hidden" href="#hide">Hide</a></li>
			</ul>
		</div>
	</div>
	
	<div class="separator large light"></div>
	
	<!-- Icon -->
	<div class="row l5 has-dials">
		<div class="span l4">
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
		<div class="span l1 dials">
			<ul>
				<li><a href="#change" id="change-icon">Change</a><a href="#change" id="cancel-icon" class="hidden cancel">Cancel</a></li>
			</ul>
		</div>
	</div>
	
	<div class="separator"></div>
	
	
	<!-- System app-->
	<div class="row l5 has-dials">
		<div class="span l4">
			<input type="checkbox" name="system" id="chk_system" <?= $app->system? 'checked' : '' ?>>
			<label for="chk_system">System application</label>
		</div>
		<div class="span l1 dials">
		</div>
	</div>
	
	<div class="row l1">
		<div class="span l1" style="text-align: right">
			<input type="submit" class="button success" value="Save changes">
		</div>
	</div>
</form>

<div class="spacer" style="height: 100px"></div>


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

<!-- 
	Show the permissions the app has to read the data from the server. These are
	generic settings that a user can override if wanted. 
-->
<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">Attributes</h1>
		</div>
		
		<div class="spacer" style="height: 30px"></div>
		
		<?php $attributes = db()->table('attribute')->getAll()->all(); ?>
		<?php foreach ($attributes as $a): ?>
		<?php $lock  = new app\AttributeLock($a, null); ?>
		<?php $read  = $lock->unlock($app); ?>
		<?php $write = $lock->unlock($app, app\AttributeLock::MODE_W); ?>
		
		<div class="row l3">
			<div class="span l2">
				<a href="<?= url('edit', 'attribute', $a->_id) ?>"><?= $a->name ?></a>
			</div>
			<div class="span l1">
				<div class="styled-select">
					<form action="<?= url('permissions', 'set', $a->_id, $app->appID, ['all' => 'yes']) ?>" method="GET">
						<input type="hidden" name="_XSRF"    value="<?= new spitfire\io\XSSToken() ?>">
						<input type="hidden" name="returnto" value="<?=url('app', 'detail', $app->_id) ?>">
						<select name="grant" id="attr-<?= $a->_id ?>" onchange="this.form.submit()">
							<option value="<?= app\AttributeLock::MODE_N ?>" <?= $read === false && $write === false? 'selected' : '' ?>>No access</option>
							<option value="<?= app\AttributeLock::MODE_R ?>" <?= $read === true  && $write === false? 'selected' : '' ?>>Read-only access</option>
							<option value="<?= app\AttributeLock::MODE_W ?>" <?= $read === false && $write === true ? 'selected' : '' ?>>Write-only access</option>
							<option value="<?= app\AttributeLock::MODE_RW ?>" <?= $read === true  && $write === true ? 'selected' : '' ?>>Full access</option>
						</select>
					</form>
				</div>
			</div>
		</div>
		<div class="spacer" style="height: 30px"></div>
		<?php endforeach; ?>
	</div>
</div>


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

<?php $contexts = db()->table('connection\context')->get('app', $app)->group()->where('expires', '>', time())->where('expires', null)->endGroup()->fetchAll(); ?>
<?php foreach ($contexts as $context): ?>
<div class="row5 fluid has-dials">
	<div class="span4">
		<div style="font-size: .85em; color: #000"><?= __($context->title) ?></div>
		<div style="font-size: .75em; color: #555"><?= __($context->descr, 200) ?></div>
	</div>
	<div class="span1 dials">
		<ul>
			<li><a href="<?= url('context', 'edit', $context->_id) ?>">Edit</a></li>
			<li><a href="<?= url('context', 'destroy', $context->_id) ?>">Destroy</a></li>
			<li><a href="<?= url('context', 'granted', $context->_id) ?>">Applications</a></li>
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
