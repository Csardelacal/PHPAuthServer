
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1">
		<h1>
			<a class="button error" style="float:right;font-size:.7em" href="<?= new URL('app', 'delete', $app->_id) ?>">Delete App</a>
			<span>Application Details</span>
		</h1>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material unpadded">
		<div class="padded">
			<p>
				View oAuth codes &amp; change the application's metadata
			</p>
		</div>

		<div class="separator"></div>

		<form method="POST" enctype="multipart/form-data" class="material-form">

			<div class="padded" style="padding-top: 0; padding-bottom: 0;">
				<div class="editable-property mid-aligned"><!--
					--><div class="property-name">Name</div><!--
					--><div class="property-value" id="name-container">
						<span id="name-display" class="fake-field-ph"><?= __($app->name) ?></span>
						<div class="edit-field hidden"><input type="text" name="name" id="name-input" disabled></div>
					</div><!--
					--><div class="edit-link"><a  href="#change" id="change-name">Change</a><a href="#change" id="cancel-name" class="hidden cancel">Cancel</a></div><!--
				--></div>
			</div>

			<div class="separator"></div>

			<div class="padded" style="padding-top: 0; padding-bottom: 0;">
				<div class="editable-property mid-aligned"><!--
					--><div class="property-name no-selection">App ID</div><!--
					--><div class="property-value" id="id-container"><span class="fake-field"><?= __($app->appID) ?></span></div><!--
					--><div class="edit-link no-selection"><a id="id-copy" href="#copy">Copy</a></div><!--
				--></div>
			</div>

			<div class="separator"></div>

			<div class="padded" style="padding-top: 0; padding-bottom: 0;">
				<div class="editable-property mid-aligned"><!--
					--><div class="property-name no-selection">App Secret</div><!--
					--><div class="property-value" id="secret-container" data-actual="<?= __($app->appSecret) ?>"><em class="fake-field-ph">Hidden</em></div><!--
					--><div class="edit-link no-selection"><a id="secret-copy" href="#copy">Copy</a> &bull; <a id="secret-hidden" href="#reveal">Reveal</a><a id="secret-visible" class="hidden" href="#hide">Hide</a></div><!--
				--></div>
			</div>

			<div class="separator"></div>

			<div class="padded" style="padding-top: 0; padding-bottom: 0;">
				<div class="editable-property"><!--
					--><div class="property-name">Icon</div><!--
					--><div class="property-value">
						<img src="<?= new URL('image','app', $app->_id, 128) ?>" class="user-icon square big" id="icon-display">
						<div class="edit-field hidden" id="icon-upload-wrap">
							<input type="file" name="icon" id="icon-input" accept="image/png,image/jpeg,image/gif" disabled>
						</div><!--
					--></div><!--
					--><div class="edit-link"><a href="#change" id="change-icon">Change</a><a href="#change" id="cancel-icon" class="hidden cancel">Cancel</a></div><!--
				--></div>
			</div>

			<div class="spacer" style="height: 10px;"></div>

			<div class="padded">
				<input type="submit" class="button success" value="Save changes">
			</div>

		</form>
	</div>
</div>

<script src="<?= URL::asset('js/app-detail.min.js')?>"></script>
