
<div class="spacer" style="height: 20px"></div>


<div class="row">
	<div class="span" style="text-align: right">
		<a class="button" href="<?= url('app', 'inbox', 'create') ?>">Create Inbox</a>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row">
	<div class="span">
		<div class="material unpadded">
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>App</th>
						<th></th>
					</tr>
				</thead>
				<?php foreach ($inboxes as $inbox): ?>
				<tr>
					<td>
						<span class="app-name"><?= $inbox->address ?></span>
					</td>
					<td>
						<img src="<?= url('image', 'app', $inbox->app->_id) ?>" width="32" height="32" class="app-icon small">
						<span class="app-name"><?=  $inbox->app->name ?></span>
					</td>
					<td><a href="<?= url('app', 'inbox', 'detail', $inbox->_id) ?>">Details</a></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
