

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>Email queued sucessfully</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row l4">
	<div class="span l3">
	</div>
	<div class="span l1" style="text-align: right">
		<a class="button" href="<?= url('email', 'create') ?>">Create Email</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row l1">
	<div class="span l1">
		<div class="material unpadded">
			<table>
				<thead>
					<tr>
						<th>To</th>
						<th>Subject</th>
						<th></th>
					</tr>
				</thead>
				<?php foreach ($records as $record): ?> 
				<tr>
					<td><?= __($record->to) ?></td>
					<td><?= __($record->subject) ?></td>
					<td><a href="<?= url('email', 'detail', $record->_id) ?>">Show</a></td>
				</tr>
				<?php endforeach; ?>
			</table>

			<div class="spacer" style="height: 30px"></div>

			<?= $pagination ?>
		</div>
	</div>
</div>
