
<div class="spacer" style="height: 40px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>User edited successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row1">
	<div class="span1 material">
		<table>
			<thead>
				<tr>
					<th>User</th>
					<th>Registered</th>
					<th></th>
				</tr>
			</thead>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><?= $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name ?></td>
				<td><?= date('m/d/Y', $user->created) ?></td>
				<td><a href="<?= new URL('user', 'detail', $user->_id) ?>">Profile</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?= $pagination ?>
	</div>
</div>