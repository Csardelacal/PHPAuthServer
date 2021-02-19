
<div class="spacer large"></div>

<div class="row l4">
	<div class="span l1"></div>
	<div class="span l2">
		<h1>Enter your code</h1>
		<div class="spacer medium"></div>
		
		<?php if (isset($messages) && $messages): ?>
		<div class="message error">
			<p>Sorry, that didn't work.</p>
		</div>
		<?php else :?>
		<p>
			Enter the code displayed on your TOTP device in the box below. 
		</p>
		<?php endif; ?>
		
		<div class="spacer large"></div>
		
		<form method="POST" action="" id="password-form">
			<div class="frm-ctrl-outer">
				<input class="frm-ctrl" placeholder="" type="text" name="challenge" id="password" required minlength="6" maxlength="6">
				<label for="challenge">Challenge</label>
			</div>
			<div class="spacer medium"></div>
			<input type="submit" style="width: 100%" class="button" value="Authenticate" id="password-submit" />
		</form>
		
		<div class="spacer medium"></div>
	</div>
</div>
