
<div class="spacer" style="height: 50px"></div>

<form class="condensed standalone" method="POST" enctype="multipart/form-data">
	<div class="description">
		Set your new <strong><?= $attribute->name ?></strong>.
	</div>
	<?php if ($attribute->datatype == 'file'): ?>
	<div style="padding: 5px; background: #FFF;">
		<input type="file" name="value">
	</div>
	<?php elseif ($attribute->datatype === 'boolean'): ?>
	<!--Styled checkbox switch needs to go here -->
	<?php else: ?>
	<input type="text" name="value" placeholder="<?= $attribute->name ?>" value="<?= $value ?>">
	<?php endif; ?>
	<input type="submit" value="Store">
</form>