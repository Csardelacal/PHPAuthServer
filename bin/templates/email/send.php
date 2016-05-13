
<div class="spacer" style="height: 30px"></div>

<div class="row1">
	<div class="span1 material">
		<?php if ($this->request->isPost() && !isset($errors)): ?>
		<h2>Message sent succesfully.</h2>
		<p>The user will receive an email notification very soon.</p>
		<?php else: ?>
		<?php if (isset($errors)): foreach ($errors as $error): ?>
		<div class="row1 fluid">
			<div class="span1"><?= $error ?></div>
		</div>
		<?php endforeach; endif; ?>
		<form class="regular" method="POST">
			<div class="field">
				<label for="to">User-id to send to</label>
				<input type="text" name="to" id="to">
			</div>
			<div class="field">
				<label for="subject">Subject</label>
				<input type="text" name="subject" id="to">
			</div>
			<div class="field">
				<label for="message">Message body</label>
				<textarea name="body" id="message"></textarea>
			</div>
			<div class="form-footer">
				<input type="submit" class="primary" value="Send">
			</div>
		</form>
		<?php endif; ?>
	</div>
</div>