
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


<!--New layout-->
<form class="regular" method="POST">
	
	<div class="row l5 s1">
		<div class="span l1"></div>
		<div class="span l3 s1">
			<div class="material unpadded">
				<div class="padded">
					
					
					<div>
						Update your username
					</div>

					<p class="small">
						Enter your new username below to change it. Your old username will be kept
						as an alias for 3 months before it expires. After that period,
						other users may be able to register it.
					</p>
					
					<div class="field">
						<input type="text" name="username" placeholder="Your new username" value="<?= __(_def($_POST['username'], '')) ?>">
					</div>
				</div>
				
				<div class="inset padded">
					<p class="small unpadded">
						Your username is public and cannot be made private. Users and
						applications will always be able to find your account by providing
						the correct username.
						
						You can request applications to not show your profile to guest
						users from the privacy settings tab.
					</p>
				</div>
				
				<div class="padded">
					
					<div class="spacer" style="height: 10px"></div>

					<div class="row1 fluid">
						<div class="span1" style="text-align: right">
							<input type="submit" class="button success" value="Store">
						</div>
					</div>
					
					<div class="spacer" style="height: 25px"></div>
				</div>
			</div>
		</div>
	</div>
</form>