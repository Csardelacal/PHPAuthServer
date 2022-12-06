

<!-- Go on about the contexts defined by this application-->
<div class="spacer" style="height: 50px"></div>

<div class="heading" data-sticky="top">
	<div class="row l5 m4 s3 fluid">
		<div class="span l4 m3 s2">
			<h1 class="unpadded">Keys</h1>
		</div>
		<div class="span l1 m1 s1" style="text-align: right">
			<a class="button" style="font-size:.6em;" href="<?= url('certificates', 'keygen') ?>">Generate new key</a>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<?php foreach ($keys as $key): ?>
<div class="row l5 has-dials">
	<div class="span l4">
		<div style="font-size: .75em; color: #000"><?= $key->expires === null? 'active' : date('Y/m/d', $key->expires) ?></div>
		<div style="font-size: .75em; color: #555; white-space:pre"><?= __($key->public) ?></div>
	</div>
</div>
	
<div class="separator large light"></div>
<?php endforeach; ?>

<?php if ($keys->isEmpty()): ?>
<div style="padding: 50px; text-align: center; font-style: italic; color: #666">
	This application has defined no keys
</div>
<?php endif; ?>
