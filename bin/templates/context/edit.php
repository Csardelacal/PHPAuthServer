

<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">Editing context</h1>
		</div>
	</div>
</div>

<?php if(isset($messages) && $messages): ?>
<div class="row">
	<div class="span">
		<?php foreach ($messages as $message): ?>
		<div class="message error">
			<p class="small unpadded"><?= $message->getMessage() ?></p>
		</div>
		<div class="spacer" style="height: 5px;"></div>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>

<div class="row">
	<div class="span">
		<form method="POST" class="regular">
			<div class="field">
				<label for="field-title">Title</label>
				<input type="text" name="title" id="field-title" value="<?= _def($_POST['title'], $context->title) ?>">
			</div>
			
			<div class="spacer" style="height: 20px"></div>
			
			<div class="field">
				<label for="field-descr">Description</label>
				<textarea type="text" name="description" id="field-descr" ><?= _def($_POST['descr'], $context->descr) ?></textarea>
			</div>
			
			<div class="spacer" style="height: 20px"></div>
			
			<div class="field">
				<label for="field-ctx">Identifier</label>
				<input type="text" name="ctx" id="field-ctx" value="<?= _def($_POST['ctx'], $context->ctx) ?>">
			</div>
			
			<div class="form-footer">
				<a href="<?= url('context', 'destroy', $context->_id) ?>">Destroy</a>
				<input type="submit" value="Store"> 
			</div>
		</form>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>