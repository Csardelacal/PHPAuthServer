
<div class="spacer" style="height: 40px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>User edited successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row1">
	<div class="span1 material">
			<form method="get" class="regular row l3">
				<div class="field span l1" style="display: inline-block; margin: 0;">
					<input type="text" name="q" placeholder="Search..." <?=isset($_GET['q'])?'value="'.htmlspecialchars($_GET['q'], ENT_QUOTES|ENT_HTML5|ENT_SUBSTITUTE ).'"':''/* don't allow a url XSS attack to target mods*/?> />
				</div>
				<div class="span l2">
					<input type="submit" value="Search" class="button" style="margin:0;"/>
				</div>
			</form>
		<div class="spacer" style="height: 20px"></div>
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Username</th>
					<th>Registered</th>
					<th>Disabled</th>
					<th>Banned</th>
					<th>Profile</th>
				</tr>
			</thead>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><?= $user->_id ?></td>
				<td><?= $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name ?></td>
				<td><?= date('m/d/Y', $user->created) ?></td>
				<td><?=$user->disabled !== null?'<b style="color: #490C0E;">Disabled</b>':'<i style="color: #999;">Active</i>'?></td>
				<td>
					<?php
						$banned     = $user? db()->table('user\suspension')->get('user', $user)->addRestriction('expires', time(), '>')->fetch() : false;
						if ($banned) {
							echo '<b style="color: #490C0E;">Banned</b></br>';
							echo (new DateTime('now'))->diff(new DateTime('@'.$banned->expires))->format('Expires in: %a days, %h hours and %i minutes').'</br>';
							echo 'Reason: '.$banned->reason.'</br>';
							echo 'Notes: '.$banned->notes.'</br>';
							echo 'Login prevented: '.($banned->preventLogin?'Yes':'No');
						} else {
							echo '<i style="color: #999;">Not banned</i>';
						}
					?>
				</td>
				<td><a class="button small" href="<?= new URL('user', 'detail', $user->_id) ?>">Profile</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?= $pagination ?>
	</div>
</div>
