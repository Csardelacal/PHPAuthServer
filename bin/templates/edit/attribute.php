

<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Change your <?= $attribute->name ?>
</div>

<div class="spacer" style="height: 30px"></div>

<?php if (isset($errors) && !empty($errors)): ?>
<div class="row1">
	<div class="span1">
		<ul class="validation-errors">
			<?php foreach($errors as $error): ?>
			<li>
				<span class="error-message"><?= __($error->getMessage()) ?></span>
				<?php if ($error->getExtendedMessage()): ?><span class="extended-message"><?= __($error->getExtendedMessage()) ?></span><?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php endif; ?>

<div class="spacer" style="height: 30px"></div>

<form class="regular" method="POST" enctype="multipart/form-data">
	<?php if ($attribute->datatype == 'file'): ?>
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Select your new <?= $attribute->name ?>
			</div>

			<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input type="file" name="value">
			</div>
		</div>
	</div>
	<?php elseif ($attribute->datatype === 'boolean'): ?>
	<!--Styled checkbox switch needs to go here -->
	<?php elseif ($attribute->datatype === 'text'): ?>
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Set your <?= $attribute->name ?>
			</div>
			
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<textarea name="value" placeholder="<?= $attribute->name ?>..."><?= __(_def($_POST['value'], $value)) ?></textarea>
			</div>
		</div>
	</div>
	<?php else: ?>
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Set your <?= $attribute->name ?>
			</div>

			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input type="text" name="value" placeholder="<?= $attribute->name ?>" value="<?= __(_def($_POST['value'], $value)) ?>">
			</div>
		</div>
	</div>
	<?php endif; ?>
	
	<div style="text-align: right">
		<input type="submit" class="button success" value="Store">
	</div>
</form>


<div class="spacer" style="height: 300px"></div>