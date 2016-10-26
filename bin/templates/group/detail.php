
<div class="spacer" style="height: 50px"></div>

<div class="row4">
	<div class="span3">
		<h1><?= $group->name ?></h1>
	</div>
	<?php if ($editable): ?>
	<div class="span1" style="text-align: right; padding-top: 10px;">
		<a href="<?= new URL('group', 'delete', $group->_id) ?>">Delete Group</a>
	</div>
	<?php endif; ?>
</div>

<div class="row1">
	<div class="span1 material">
		
		<?php if ($editable): ?>
		<form class="regular" method="POST" enctype="multipart/form-data">
			<div class="field">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" value="<?= __($group->name) ?>">
			</div>
			<div class="field">
				<label for="description">Description</label>
				<textarea name="description" id="description"><?= __($group->description) ?></textarea>
			</div>
			<div class="form-footer">
				<input type="submit" value="Store">
			</div>
		</form>
		<?php else: ?>
		<div class="field">
			<p><?= __($group->description) ?></p>
		</div>
		<?php endif; ?>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1 material">
		<table>
			<thead>
				<tr>
					<th>User</th>
					<th>Role</th>
					<?php if ($editable): ?><th></th><?php endif; ?>
				</tr>
			</thead>
			<?php foreach ($members as $member): ?>
			<tr>
				<td><?= $member->user ?></td>
				<td><?= $member->role ?></td>
				<?php if ($editable): ?> 
				<td>
					<a href="<?= new URL('group', 'remove', $member->_id, 'from', $group->_id) ?>">Kick</a>
				</td>
				<?php endif; ?> 
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>

<?php if ($editable): ?>
<div class="row1">
	<div class="span1">
		<h2>Add a user</h2>
		<div class="material">
			<form method="POST" action="<?= new URL('group', 'addUser', $group->_id); ?>" class="regular">
				<input type="hidden" name="group" value="<?= $group->_id ?>">
				<div class="field">
					<label for="username">Name</label>
					<input type="text" name="username" id="name" placeholder="Username...">
				</div>
				<div class="form-footer">
					<input type="submit" value="Add">
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
