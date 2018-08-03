

<div class="row">
	<div class="span">
		<div class="heading">
			<h1 class="unpadded">Grants for <?= $context->app->name ?> (<?= $context->title ?>)</h1>
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>

<form method="GET" action="<?= url('context', 'grant', $context->_id) ?>" class="regular">
	<div class="row">
		<div class="span">
			<div class="row l6 m5 s5">
				<div class="span l2 m2 s1">
					<div class="styled-select">
						<select name="app">
							<?php foreach(db()->table('authapp')->getAll()->all() as $app):?> 
							<option value="<?= $app->_id ?>"><?= __($app->name) ?></option>
							<?php endforeach ?> 
						</select>
					</div>
				</div>
				<div class="span l2 m1 s1">
					<div class="styled-select">
						<select name="grant">
							<option value="<?= \connection\AuthModel::STATE_DENIED ?>">Deny</option>
							<option value="<?= \connection\AuthModel::STATE_AUTHORIZED ?>">Authorize</option>
						</select>
					</div>
				</div>
				<div class="span l1 m1 s2">
					<div style="padding: 5px">
						<input type="checkbox" name="final">
						<label>Final <span class="not-mobile">rule</span></label>
					</div>
				</div>
				<div class="span l1 m1 s1">
					<input type="submit" value="Add">
				</div>
			</div>
				
		</div>
	</div>
</form>

<div class="spacer" style="height: 30px"></div>

<?php foreach($grants as $grant): ?>
<div class="row l9 m7 has-dials">
	<div class="span l1 m1 not-mobile" style="text-align: center">
		<img class="app-icon medium" width="64" height="64" src="<?= url('image', 'app', $grant->target->_id, 64) ?>">
	</div>
	
	<div class="span l6 m6">
		<div><?= __($grant->target->name) ?></div>
		<div>
			<p class="small unpadded secondary">
				<?= $grant->state == 1? 'Deny' : 'Granted'?> 
				<?= $grant->final? ' · <strong>Final</strong>' : '' ?> 
			</p>
		</div>
	</div>
	
	<div class="span l2 dials">
		<ul>
			<li><a href="<?= url('context', 'revoke', $grant->_id) ?>">Revoke</a></li>
		</ul>
	</div>
</div>

<div class="spacer" style="height: 10px"></div>
<?php endforeach; ?>

<div class="spacer" style="height: 30px"></div>
