
<div class="spacer" style="height: 30px;"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="row1">
	<div class="span1">
		<div class="message success">
			Request successful.
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row1">
	<div class="span1">
		<h1>Your account</h1>
	</div>
</div>

<div class="row1">
	<div class="span1 material unpadded">
		<div class="padded">
			<p>
				In this area you can edit your account. Feel free to modify your credentials,
				username and other data. You can also access your sessions and see where 
				you're logged in.
			</p>
		</div>
		
		<div class="separator"></div>
		
		<div class="padded" style="padding-top: 5px; padding-bottom: 5px;">
			<div class="editable-property"><!--
				--><div class="property-name">Username</div><!--
				--><div class="property-value"><?= $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name ?></div><!--
				--><div class="edit-link"><a href="<?= new URL('edit', 'username') ?>">Change</a></div><!--
			--></div>
		</div>
		
		<div class="separator"></div>
		
		<div class="padded" style="padding-top: 5px; padding-bottom: 5px;">
			<div class="editable-property"><!--
				--><div class="property-name">Email Address</div><!--
				--><div class="property-value"><?= $user->email ?></div><!--
				--><div class="edit-link"><a href="<?= new URL('edit', 'email') ?>">Change</a></div><!--
			--></div>
		</div>
		
		<div class="separator"></div>
		
		<div class="padded" style="padding-top: 5px; padding-bottom: 5px;">
			<div class="editable-property"><!--
				--><div class="property-name">Password</div><!--
				--><div class="property-value"><em>Encrypted</em></div><!--
				--><div class="edit-link"><a href="<?= new URL('edit', 'password') ?>">Change</a></div><!--
			--></div>
		</div>
		
		<div class="separator"></div>
		
		<div class="padded" style="padding-top: 5px; padding-bottom: 5px;">
			<div class="editable-property"><!--
				--><div class="property-name">Avatar</div><!--
				--><div class="property-value"></div><!--
				--><div class="edit-link"><a href="<?= new URL('edit', 'avatar') ?>">Change</a></div><!--
			--></div>
		</div>
		
		<?php $attributes = db()->table('attribute')->get('writable', Array('me', 'groups', 'public'))->fetchAll(); ?>
		<?php foreach ($attributes as $attribute): ?>
		<div class="separator"></div>
		
		<?php $attrValue = $user->attributes->getQuery()->addRestriction('attr', $attribute)->fetch() ?>
		<div class="padded" style="padding-top: 5px; padding-bottom: 5px;">
			<div class="editable-property"><!--
				--><div class="property-name"><?= $attribute->name ?></div><!--
				--><div class="property-value"><?= $attrValue? __($attrValue->value) : '<em>Undefined</em>' ?></div><!--
				--><div class="edit-link"><a href="<?= new URL('edit', 'attribute', $attribute->_id) ?>">Edit</a></div><!--
			--></div>
		</div>
		<?php endforeach; ?>
		
		<div class="spacer" style="height: 10px;"></div>
	</div>
</div>
