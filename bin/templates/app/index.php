
<div class="spacer" style="height: 50px"></div>

<?php if (isset($_GET['message']) && in_array($_GET['message'], array('success','deleted'))): ?>
<div class="message success">
	<p>Application <?= $_GET['message'] === 'success' ? 'created' : 'deleted' ?> successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row">
	<div class="span" style="text-align: right">
		<a class="button" href="<?= url('app', 'create') ?>">Create App</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row">
	<div class="span">
		<div class="material unpadded">
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th></th>
					</tr>
				</thead>
				<?php foreach ($pagination->records() as $app): ?>
				<tr>
					<td>
						<img src="<?= url('image', 'app', $app->_id) ?>" class="app-icon small">
						<?=  $app->name ?>
					</td>
					<td><a href="<?= url('app', 'detail', $app->_id) ?>">Details</a></td>
				</tr>
				<?php endforeach; ?>
			</table>
			<?= $pagination ?>
		</div>
	</div>
</div>
