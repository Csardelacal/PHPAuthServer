
<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">
				<img class="app-icon small" src="<?= url('image', 'app', $app->_id); ?>">
				Grant <?= $app->name ?> access to <?= $attribute->name ?>?
			</h1>
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>

<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<div class="material unpadded">
			<div class="padded">
				<p class="small secondary">
					You are seeing this page because you were asked to grant the app
					<strong><?= $app->name ?></strong> additional permissions.
				</p>
				<?php if ($grant === attribute\AppGrantModel::GRANT_DENIED): ?>
				<p class="small secondary">
					Clicking accept changes will <strong>revoke all permissions</strong>
					from the application. The application will therefore not longer be
					able to read <?= $attribute->name ?> from your profile.
				</p>
				<?php elseif ($grant === attribute\AppGrantModel::GRANT_READ): ?>
				<p class="small secondary">
					Clicking accept changes will <strong>grant read-only access</strong>
					to the application. The application will be able to read the information,
					but will not be able to modify it.
				</p>
				<?php elseif ($grant === attribute\AppGrantModel::GRANT_WRITE): ?>
				<p class="small secondary">
					Clicking accept changes will <strong>grant write-only access</strong>
					to the application. The application can then write data to your 
					profile but won't be able to check whether it was modified or retrieve the value later.
				</p>
				<?php elseif ($grant === attribute\AppGrantModel::GRANT_RW): ?>
				<p class="small secondary">
					Clicking accept changes will <strong>grant full access</strong>
					to the application. The application will be able to retrieve the
					information and write to the field if necessary.
				</p>
				<?php endif; ?>
				
				<div class="spacer" style="height: 30px"></div>
				
				<form action="" method="GET" style="text-align: right">
					<input type="hidden" name="grant" value="<?= $grant ?>">
					<input type="hidden" name="_XSRF" value="<?= $xsrf ?>">
					<input type="hidden" name="returnto" value="<?= $returnto ?>">
					
					<a href="<?= filter_var($returnto, FILTER_VALIDATE_URL) ?>" style="margin-right: 10px">
						<small>Reject changes</small>
					</a>
					<input type="submit" class="button" value="Accept changes">
				</form>
			</div>
		</div>
	</div>
</div>