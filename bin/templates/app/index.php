
<div class="spacer" style="height: 50px"></div>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="message success">
	<p>Application created successfully.</p>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="row1">
	<div class="span1" style="text-align: right">
		<a class="button" href="<?= new URL('app', 'create') ?>">Create App</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1">
	<div class="span1 material">
		<?php foreach ($query->fetchAll() as $app): ?>
		<div class="list-element">
			<a href="<?= new URL('app', 'detail', $app->_id) ?>"><?=  $app->name ?></a>
		</div>
		<?php endforeach; ?>
		<?= $pagination ?>
	</div>
</div>

<?= var_dump(base64_encode(openssl_random_pseudo_bytes(35, $secure))) ?>
<pre><?= print_r(spitfire()->getMessages()) ?></pre>