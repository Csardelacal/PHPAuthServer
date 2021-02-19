
<div class="container">
	<div class="row l1">
		<div class="span l1">
			<h1>Sessions</h1>
		</div>
	</div>
	
	<div class="spacer large"></div>
	
	<?php foreach ($sessions as $session): ?>
	<div class="box box-soft padded">
		<div class="row l9">
			<div class="span l1">
				<?php if ($session->location): ?>
				<img src="https://lipis.github.io/flag-icon-css/flags/1x1/<?= $session->location->country ?>.svg" style="border-radius: 50%;">
				<?php else: ?>
				<div style="background-color: #DDD; border-radius: 50%; text-align: center; width: 100%; padding: 1.5rem;" title="Unknown location">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="vertical-align: middle">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
				</div>
				<?php endif; ?>
			</div>
			<div class="span l6">
				<div class="spacer minuscule"></div>
				<div>
					<?php if ($session->device): ?>
					<span class="device-icon <?= $session->device->category() ?>" title="<?= $session->device->category() ?>"></span>
					<span class="badge bg-grey-100 text:grey-700"><?= $session->device->category() ?></span>
					<?php endif ?>
					<?php if ($session->expires < time()): ?>
					<span class="badge bg-green-100 text:green-700">Expired</span>
					<?php endif ?>
					<?php if (!$session->authenticated): ?>
					<span class="badge bg-orange-100 text:orange-600">Login attempt</span>
					<?php endif ?>
				</div>
				<div class="spacer small"></div>
				<div>
					<span class="text:grey-500">Created</span>
					<strong class="text:grey-700"><?= date('M d, Y', $session->created) ?></strong>
				</div>
				<div class="spacer minuscule"></div>
				<div>
					<span class="text:grey-500">Expires</span>
					<strong class="text:grey-700"><?= date('M d, Y', $session->expires) ?></strong>
				</div>
			</div>
			<div class="span l2 align-right">
				<div class="spacer small"></div>
				<div>
					<a class="button small outline button-color-red-800 hover-fill-red-800">End session</a>
				</div>
			</div>
		</div>
	</div>
	
	<div class="spacer medium"></div>
	<?php endforeach; ?>
</div>