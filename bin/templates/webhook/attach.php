
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Create a new webhook
</div>

<div class="spacer" style="height: 25px"></div>

<p style="font-size: .8em; color: #555">
	Webhooks allow applications to request being informed when there are changes
	to a certain piece of data inside your authentication server.
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
				Select data type and action to be reported.
			</div>
			
			<div class="" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<div class="row2 fluid">
					<div class="span1">
						<span class="styled-select">
							<select name="type">
								<option value="<?= \webhook\HookModel::USER ?>">User</option>
								<option value="<?= \webhook\HookModel::TOKEN ?>">Token</option>
								<option value="<?= \webhook\HookModel::APP ?>">App</option>
								<option value="<?= \webhook\HookModel::GROUP ?>">Group</option>
							</select>
						</span>
					</div>
					<div class="span1">
						<span class="styled-select">
							<select name="action">
								<option value="<?= \webhook\HookModel::CREATED ?>">Created</option>
								<option value="<?= \webhook\HookModel::UPDATED ?>">Updated</option>
								<option value="<?= \webhook\HookModel::DELETED ?>">Deleted</option>
								<option value="<?= \webhook\HookModel::MEMBER ?>">Member change</option>
							</select>
						</span>
					</div>
				</div>
			</div>
			
			<div class="spacer" style="height: 20px"></div>
			
			<div style="font-size: .75em; color: #555">
				<strong>Name</strong> [optional]
			</div>
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input type="text" name="name" placeholder="Name...">
			</div>
			
			<div class="spacer" style="height: 20px"></div>
			
			<div style="font-size: .75em; color: #555">
				<strong>URL</strong>
			</div>
			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<input type="text" name="url" placeholder="URL...">
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