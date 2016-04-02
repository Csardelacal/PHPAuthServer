
<?php if($userIsAdmin): ?>
<div class="admin-navigation">
	<div class="row7">
		<div class="span1"><a href="<?= new URL('admin') ?>" class="menu-item">Admin</a></div>
		<div class="span1"><a href="<?= new URL('app') ?>" class="menu-item">Apps</a></div>
		<div class="span1"><a href="<?= new URL('token') ?>" class="menu-item">Sessions</a></div>
		<div class="span1"><a href="<?= new URL('user') ?>" class="menu-item">Users</a></div>
		<div class="span1"><a href="<?= new URL('group') ?>" class="menu-item">Groups</a></div>
	</div>
</div>
<?php endif; ?>

<div class="spacer" style="height: 30px;"></div>

<div class="row1">
	<div class="span1">
		<h1>Your account</h1>
	</div>
</div>

<div class="row1">
	<div class="span1 material">
		
	</div>
</div>

<?= $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name ?>
<?= $user->attributes->getQuery()->count() ?>

<pre><?= var_dump(spitfire()->getMessages()) ?></pre>