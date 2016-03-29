
<?php if($userIsAdmin): ?>
<div class="admin-navigation">
	Admin panel
</div>
<?php endif; ?>

<?= $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name ?>
<?= $user->attributes->getQuery()->count() ?>

<pre><?= var_dump(spitfire()->getMessages()) ?></pre>