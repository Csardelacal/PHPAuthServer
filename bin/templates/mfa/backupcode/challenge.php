
<div class="spacer large"></div>

<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<h1>Use backup code</h1>
		<div class="spacer medium"></div>
		
		<?php if (isset($error) && $error): ?>
		<div class="message error">
			<p>Sorry, that didn't work.</p>
		</div>
		<?php else :?>
		<p>
			In the field below you can enter a backup code that you have not yet 
			used.
		</p>
		<?php endif; ?>
		
		<div class="spacer large"></div>
		
		<form method="POST" action="">
			<div class="frm-ctrl-outer">
				<input type="text" class="frm-ctrl" placeholder="" name="secret" autocomplete="off">
				<label>Backup code</label>
			</div>
			<div class="spacer medium"></div>
			<input type="submit" style="width: 100%" class="button" value="Authenticate" />
		</form>
		
		<div class="spacer medium"></div>
	</div>
</div>
