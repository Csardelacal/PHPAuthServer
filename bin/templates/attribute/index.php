
<div class="spacer" style="height: 50px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>Attribute created successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('attribute', 'create') ?>">Create Attribute</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
		<table>
			<thead>
				<tr>
					<th>Attribute</th>
					<th></th>
				</tr>
			</thead>
			<?php foreach($attributes as $attr): ?>
			<tr>
				<td><?= $attr->name ?><?= $attr->required? ' (Required)' : '' ?></td>
				<td><a href="<?= new URL('attribute', 'edit', $attr->_id) ?>">Edit</a></td>
			</tr>
			<?php endforeach; ?>
		</table>
		
		<div class="spacer" style="height: 30px"></div>
		
		<?= $pagination ?>
	</div>
</div>