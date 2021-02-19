
<div class="spacer large"></div>

<div class="row l1">
	<div class="span l1">
		<h1>Your backup codes</h1>
		
		<p>
			Backup codes allow you to recover access to your account, or to strongly
			authenticate yourself. These codes are only displayed once, please keep 
			them safe.
		</p>
		
		<?php foreach ($flash as $code): ?>
		<div class="spacer medium"></div>
		
		<div class="material align-center">
			<strong><?= __($code) ?></strong>
		</div>
		<?php endforeach; ?>
	</div>
</div>