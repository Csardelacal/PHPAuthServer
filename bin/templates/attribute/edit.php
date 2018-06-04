
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1 material">
		<h1><?= $bean->getRecord()->name ?></h1>
		<p>Fill in the new settings to make your attribute fit your customization</p>
		
		<?= $bean->makeForm(new spitfire\io\renderers\SimpleFormRenderer()); ?>
		
		<div class="separator"></div>
		
		<a class="button" style="float: right" href="<?= url('attribute', 'validator', 'add', $bean->getRecord()->_id) ?>">Add validator</a>
		<h2>Validators</h2>
		<?php $validators = $bean->getRecord()->validate->toArray(); ?>
		<?php foreach ($validators as $validator): ?>
		<?= $validator->validator ?>
		<?php endforeach; ?>
		<?php if (empty($validators)): ?>
		<p>No validators defined</p>
		<?php endif; ?>
	</div>
</div>