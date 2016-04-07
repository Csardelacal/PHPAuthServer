
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