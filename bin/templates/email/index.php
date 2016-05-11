
<div class="spacer" style="height: 50px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>Email queued sucessfully</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row4">
	<div class="span3 tabs">
		<a href="<?= new URL('email', 'index') ?>" class="tab <?= !isset($_GET['history'])? 'active' : '' ?>">Queue</a>
		<a href="<?= new URL('email', 'index', Array('history' => 1)) ?>" class="tab <?= isset($_GET['history'])? 'active' : '' ?>">History</a>
	</div>
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('email', 'create') ?>">Create Email</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
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
				<td><a href="<?= new URL('email', 'detail', $record->_id) ?>">Show</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<div class="spacer" style="height: 30px"></div>
		
		<?= $pagination ?>
	</div>
</div>

<pre>
	<?php var_dump(spitfire()->getMessages()); ?>
</pre>