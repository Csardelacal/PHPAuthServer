
<div class="spacer" style="height: 50px"></div>

<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('app', 'create') ?>">Create App</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
		<?php foreach ($query->fetchAll() as $row): ?>
		<?php endforeach; ?>
		<?= $pagination ?>
	</div>
</div>