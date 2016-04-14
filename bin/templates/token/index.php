
<div class="spacer" style="height: 50px"></div>

<div class="row1">
	<div class="span1 material">
		<table>
			<thead>
				<tr>
					<th>Token</th>
					<th>User</th>
					<th></th>
				</tr>
			</thead>
			<?php foreach ($records as $record): ?> 
			<tr>
				<td><?= $record->token ?></td>
				<td><?= $record->user ?></td>
				<td><a href="<?= new URL('token', 'end', $record->token) ?>">End session</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<?= $pagination ?>
	</div>
</div>

<pre>
	<?php var_dump(spitfire()->getMessages()); ?>
</pre>