
<div class="spacer" style="height: 20px"></div>

<?php if (isset($_GET['message']) && in_array($_GET['message'], array('success','deleted'))): ?>
<div class="message success">
	<p>Application <?= $_GET['message'] === 'success' ? 'created' : 'deleted' ?> successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row l2">
	<div class="span l1">
		<h1>Your applications</h1>
	</div>
	<div class="span l1 align-right">
		<a class="button small solid" href="<?= url('app', 'create') ?>">+ Create App</a>
	</div>
</div>

<div class="spacer large"></div>

<div class="container">
	<?php foreach ($pagination->records() as $app): ?>
	<div class="row s5">
		<div class="span s1 align-right">
			<a href="<?= url('app', 'detail', $app->_id) ?>">
				<img src="<?= url('image', 'app', $app->_id, 128) ?>" width="128" height="128" class="app-icon medium">
			</a>
		</div>
		<div class="span s4">
			<div>
				<a href="<?= url('app', 'detail', $app->_id) ?>"><strong class="text:grey-900"><?=  $app->name ?></strong></a>
				<small class="text:grey-600"><?= $app->appID ?></small>
			</div>
			<div class="spacer small"></div>
			<div>
				<?php if ($app->twofactor): ?><span class="badge bg-green-100 text:green-800">requires mfa</span><?php endif; ?>
			</div>
		</div>
	</div>
	<div class="spacer medium"></div>
	<div class="horizontal-divider"></div>
	<div class="spacer medium"></div>
	<?php endforeach; ?>
	<?= $pagination ?>
</div>
