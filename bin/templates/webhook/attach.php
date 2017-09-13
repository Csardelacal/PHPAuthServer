
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Create a new webhook
</div>

<div class="spacer" style="height: 25px"></div>

<p style="font-size: .8em; color: #555">
	Enter your new username below to change it. Your old username will be kept
	as an alias for 3 months before it expires.
</p>

<?php if (isset($messages) && !empty($messages)): ?>
<div class="row1">
	<div class="span1">
		<ul class="validation-errors">
			<?php foreach($messages as $message): ?>
			<li>
				<span class="error-message"><?= __($message->getMessage()) ?></span>
				<?php if ($message->getExtendedMessage()): ?><span class="extended-message"><?= __($message->getExtendedMessage()) ?></span><?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php endif; ?>

<div class="spacer" style="height: 25px"></div>

<form class="regular" method="POST">
	
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Select a new username
			</div>
			
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input type="text" name="username" placeholder="Your new username" value="<?= __(_def($_POST['username'], '')) ?>">
			</div>
		</div>
	</div>
	
	<div class="spacer" style="height: 25px"></div>
	
	<div class="row1 fluid">
		<div class="span1" style="text-align: right">
			<input type="submit" class="button success" value="Store">
		</div>
	</div>
</form>

<div class="spacer" style="height: 250px"></div>