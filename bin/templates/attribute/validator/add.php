

<div class="spacer" style="height: 40px"></div>

<div class="row1">
	<div class="span1 material">
		<h1>Add a validator to <?= $attribute->name ?></h1>
		
		<form method="POST" enctype="multipart/form-data">
			<select name="validator">
				<?php foreach ($validators as $validator): ?>
				<option value="<?= get_class($validator) ?>"><?= $validator->getName() ?></option>
				<?php endforeach; ?>
			</select>
			
			<input type="text" name="arguments" placeholder="Arguments...">
			<input type="submit">
		</form>
	</div>
</div>