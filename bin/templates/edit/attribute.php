

<?php if (isset($errors) && !empty($errors)): ?>
<div class="row1">
	<div class="span1">
		<ul class="validation-errors">
			<?php foreach($errors as $error): ?>
			<li>
				<span class="error-message"><?= __($error->getMessage()) ?></span>
				<?php if ($error->getExtendedMessage()): ?><span class="extended-message"><?= __($error->getExtendedMessage()) ?></span><?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<?php endif; ?>

<div class="spacer" style="height: 30px"></div>

<form class="regular" method="POST" enctype="multipart/form-data">
	
	<div class="row l3 s1">
		<div class="span l2 r1">
			<div class="material unpadded">
				<?php if ($attribute->datatype == 'file'): ?>
				<div class="row1 fluid">
					<div class="span1">
						<div style="font-size: .75em; color: #555">
							Select your new <?= $attribute->name ?>
						</div>

						<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
							<input type="file" name="value">
						</div>
					</div>
				</div>
				<?php elseif ($attribute->datatype === 'boolean'): ?>
				<!--Styled checkbox switch needs to go here -->
				<div class="padded">
					<input type="checkbox" name="value" <?= _def($_POST['value'], $value)? 'checked' : '' ?> id="switch"><label for="switch"><?= __($attribute->name) ?></label>
				</div>
				<?php elseif ($attribute->datatype === 'text'): ?>
				<div class="field">
					<textarea class="borderless" name="value" placeholder="<?= $attribute->name ?>..."><?= __(_def($_POST['value'], $value)) ?></textarea>
				</div>
				<?php else: ?>
				<div class="padded">
					<div class="spacer" style="height: 20px"></div>
					<div class="field">
						<input type="text" name="value" placeholder="<?= $attribute->name ?>" value="<?= __(_def($_POST['value'], $value)) ?>">
					</div>
					<div class="spacer" style="height: 20px"></div>
				</div>
				<?php endif; ?>
				
				<div class="inset padded">
					<?php if ($attribute->readable === 'public'): ?>
					<p class="unpadded small">
						<strong>This data is public by default</strong> When you authorize 
						an application to access your data it will be able to read this value
						unless you explicitly disable access to this data.
					</p>
					<?php else: ?>
					<p class="unpadded small">
						<strong>Private information</strong> No application connected to
						your account will be able to access this data without your explicit 
						consent. Administration may have granted default access to an
						application, you're free to revoke this access at any time.
						
						You can review the information you have provided to any application
						connected to your account at any time.
						
						Please be mindful which applications you allow to retrieve this 
						information. Third parties may store, cache or distribute the 
						data after they retrieved it from our server.
					</p>
					<?php endif; ?>
					<div class="spacer" style="height: 5px"></div>
					
					<p class="unpadded small">
						On the right hand side you will find a list of applications that
						may read this information from your account. If you prefer one 
						of these applications to be unable to access your data, head to
						the <a href="<?= url('appgrant') ?>">Application grant</a> page. Disabling access to
						certain information may cause that application to stop working
						properly.
					</p>
				</div>
				
				<div class="padded">
					<div style="text-align: right">
						<input type="submit" class="button success" value="Store">
					</div>
				</div>
			</div>
		</div>
		
		<div class="span l1 s1">
			<h2>Apps able to access this data</h2>
			
			<?php foreach ($apps as $app): ?>
			<div><img src="<?= url('image', 'app', $app->_id, 32); ?>" style="width: 16px; vertical-align: middle" > <?= $app->name ?></div>
			<div class="spacer" style="height: 5px"></div>
			<?php endforeach; ?>
			
			<?php if ($apps->isEmpty()): ?>
			<p class="small">No applications are be able to access this information.</p>
			<?php endif; ?>
		</div>
	</div>
</form>


<div class="spacer" style="height: 300px"></div>