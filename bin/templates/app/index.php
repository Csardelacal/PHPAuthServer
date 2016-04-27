
<div class="spacer" style="height: 50px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>Application created successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('app', 'create') ?>">Create App</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th></th>
				</tr>
			</thead>
			<?php foreach ($query->fetchAll() as $app): ?>
			<tr>
				<td>
					<img src="<?= new URL('image', 'app', $app->_id) ?>" style="vertical-align: middle;">
					<?=  $app->name ?>
				</td>
				<td><a href="<?= new URL('app', 'detail', $app->_id) ?>">Edit</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?= $pagination ?>
	</div>
</div>