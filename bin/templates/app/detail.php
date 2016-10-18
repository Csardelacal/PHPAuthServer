
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button error" href="<?= new URL('app', 'delete', $app->_id) ?>">Delete App</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
		<h1>Application Details</h1>
		<p>View oAuth codes &amp; change the application's metadata</p>
		
		<form method="POST" enctype="multipart/form-data">
			<div class="field">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" value="<?= __($app->name) ?>">
			</div>
			<div class="field">
				<p>App Id: <?= __($app->appID) ?></p>
			</div>
			<div class="field">
				<p>App Secret: <?= __($app->appSecret) ?></p>
			</div>
			<div class="field">
				<label for="icon">Icon</label>
				<?php if (file_exists($app->icon)): ?>
				<img src="<?= \spitfire\io\DataURI::fromFile($app->icon) ?>" style="max-width: 512px">
				<?php endif; ?>
				<input type="file" name="icon" id="icon">
			</div>
			<div class="field">
				<input type="submit" value="Save">
			</div>
		</form>
	</div>
</div>
