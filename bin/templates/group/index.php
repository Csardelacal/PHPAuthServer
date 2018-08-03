
<div class="spacer" style="height: 50px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>Group created successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<?php if(isset($userIsAdmin) && $userIsAdmin): ?>
<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= url('group', 'create') ?>">Create Group</a>
	</div>
</div>
<?php endif; ?>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
		<table>
			<thead>
				<tr>
					<th>Group</th>
					<th>Owner</th>
					<th></th>
				</tr>
			</thead>
			<?php foreach ($records as $record): ?> 
			<tr>
				<td><?= __($record->name) ?> <?= $record->_id === SysSettingModel::getValue('admin.group')? '<i>(Admin)</i>': '' ?></td>
				<td><?= __($record->creator) ?></td>
				<td><a href="<?= url('group', 'detail', $record->_id) ?>">Show</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<div class="spacer" style="height: 30px"></div>
		
		<?= $pagination ?>
	</div>
</div>
