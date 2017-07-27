
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

<form method="POST" enctype="multipart/form-data">
	<!-- App Name -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				Name
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 15px; font-size: .8em; color: #333;">
				<div class="property-value" id="name-container">
					<span id="name-display" class="fake-field-ph"><?= __($app->name) ?></span>
					<div class="edit-field hidden"><input type="text" name="name" id="name-input" disabled></div>
				</div>
			</div>
		</div>
		<div class="span1 dials">
			<ul>
				<li>
					<a  href="#change" id="change-name">Change</a>
					<a href="#change" id="cancel-name" class="hidden cancel">Cancel</a>
				</li>
			</ul>
		</div>
	</div>
	
	<div class="spacer" style="height: 20px"></div>
	
	<!-- App ID -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				App ID
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 15px; font-size: .8em; color: #333;">
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
	
	<div class="spacer" style="height: 20px"></div>
	
	<!-- App Secret -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				App Secret
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 15px; font-size: .8em; color: #333;">
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
	
	<div class="spacer" style="height: 20px"></div>
	
	<!-- Icon -->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .85em; color: #555">
				Icon
			</div>
			
			<div style="border-left: solid 2px #2a912e; padding: 15px; font-size: .8em; color: #333;">
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

<script src="<?= spitfire\core\http\URL::asset('js/app-detail.min.js')?>"></script>
