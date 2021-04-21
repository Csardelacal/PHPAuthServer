
<div class="spacer large"></div>

<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<h1>Use backup code</h1>
		<div class="spacer medium"></div>
		
		<?php if (isset($messages) && !empty($messages)): ?>
		<div class="message error">
			<?php foreach ($messages as $message): ?><p><?= $message ?></p><?php endforeach; ?>
		</div>
		<?php else :?>
		<p>
			Enter the code you received in the email that we just sent. Alternatively you can click
			on the link in the email to be authenticated.
		</p>
		<?php endif; ?>
		
		<div class="spacer large"></div>
		
		<form method="POST" action="">
			<div class="frm-ctrl-outer">
				<input type="text" class="frm-ctrl" placeholder="" name="secret" autocomplete="off">
				<label>The email code</label>
			</div>
			<div class="spacer medium"></div>
			<input type="submit" style="width: 100%" class="button" value="Authenticate" />
		</form>
		
		<div class="spacer medium"></div>
	</div>
</div>
