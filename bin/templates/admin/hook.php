
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
		<div class="heading"><h1>Select webhook manager.</h1></div>
		
		<div class="spacer" style="height: 10px"></div>
		
		<form method="POST" action="">
			<div class="row l3">
				<div class="span l1">
					Application
				</div>
				<div class="span l2">
					<div class="styled-select">
						<select name="app">
							<option value="">---</option>
							<?php foreach($apps as $app): ?>
							<option value="<?= $app->_id ?>" <?= $selected === $app->_id? 'selected' : '' ?>><?= $app->name ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			
			<div class="spacer" style="height: 20px"></div>
			
			<div class="row">
				<div class="span" style="text-align: right">
					<input type="submit" class="button" value="Store">
				</div>
			</div>
		</form>
	</div>
</div>
