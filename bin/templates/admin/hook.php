
<?php if (!$valid): ?>
<div class="row">
	<div class="span">
		<div class="message error">
			<p class="unpadded">
				Check the configuration, CptnH00k is not properly working. No webhooks can
				be configured or sent. Please recheck the selected application and it's
				public URL, which must be configured to CptnH00k's base URL.
			</p>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="row">
	<div class="span">
		<h2>Select webhook manager.</h2>
		
		<div class="spacer medium"></div>
		<p>
			<small>
				Select the application to send webhooks to. This application should be
				able to distribute the webhooks to the applications in your network that
				need them.
				
				Webhooks notify applications in your network of updates that occurred
				to user accounts. These include user account updates, creation, deletion,
				suspensions; token expiration and revocation; and application creation,
				updates and deletions.
			</small>
		</p>
		
		<div class="spacer medium"></div>
		
		<form method="POST" action="">
			<div class="row l4">
				<div class="span l1"></div>
				<div class="span l2">
					<select class="frm-ctrl" name="app">
						<option value="">---</option>
						<?php foreach($apps as $app): ?>
						<option value="<?= $app->_id ?>" <?= $selected === $app->_id? 'selected' : '' ?>><?= $app->name ?></option>
						<?php endforeach; ?>
					</select>
					
					<div class="spacer small"></div>
					
					<div class="align-right">
						<input type="submit" class="button" value="Store">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
