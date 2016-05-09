
<div class="spacer" style="height: 30px"></div>

<?php if ($editable): ?>
<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button error" href="<?= new URL('app', 'delete', $group->_id) ?>">Delete Group</a>
	</div>
</div>
<?php endif; ?>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1">
		<h1><?= $group->name ?></h1>
	</div>
</div>

<div class="row1">
	<div class="span1 material">
		<p>Here you can see this group and it's members</p>
		
		<?php if ($editable): ?>
		<form method="POST" enctype="multipart/form-data">
			<div class="field">
				<label for="name">Name</label>
				<input type="text" name="name" id="name" value="<?= __($group->name) ?>">
			</div>
			<div class="field">
				<label for="name">Name</label>
				<textarea name="name" id="name"><?= __($group->description) ?>"</textarea>
			</div>
			<div class="field">
				<input type="submit" value="Create">
			</div>
		</form>
		<?php else: ?>
		<div class="field">
			<p>Name: <?= __($group->name) ?></p>
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
					<a href="<?= new URL('user', 'remove', $member->_id) ?>">Kick</a>
				</td>
				<?php endif; ?> 
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>