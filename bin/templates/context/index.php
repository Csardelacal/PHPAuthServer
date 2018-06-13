
<div class="row">
	<div class="span">
		<div class="material">
			<?php foreach ($records as $context): ?>
			<div class="row l5 m3 fluid has-dials">
				<div class="span l4 m2">
					<div style="font-size: .85em; color: #000"><?= __($context->title) ?></div>
					<div style="font-size: .75em; color: #555"><?= __($context->descr, 200) ?></div>
				</div>
				<div class="span l1 m1 dials">
					<ul>
						<li><a href="<?= url('context', 'edit', $app->_id, $context->ctx) ?>">Edit</a></li>
						<li><a href="<?= url('context', 'revoke', $app->_id, $context->ctx) ?>">Revoke</a></li>
						<li><a href="<?= url('context', 'granted', $app->_id, $context->ctx) ?>">Applications</a></li>
					</ul>
				</div>
			</div>
			<?php endforeach; ?>

			<?php if ($records->isEmpty()): ?>
			<div style="padding: 50px; text-align: center; font-style: italic; color: #666">
				This application has defined no contexts
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>