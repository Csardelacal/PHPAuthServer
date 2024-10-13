

<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<h1 class="unpadded">
				<img class="app-icon small" width="64" height="64" src="<?= url('image', 'app', $app->_id, 64) ?>">
				<?= __($app->name) ?>
			</h1>
		</div>
		
		<p class="small secondary">
			This is the list of authorizations the application has received on your 
			account. Some of these may have been provided by an administrator due to
			policy, you can disable access to this information to the application regardless.
			<strong>Applications may stop working if they cannot access the data they need</strong>
		</p>
		
		<div class="heading">
			<h1 class="unpadded">Attributes</h1>
		</div>
		
		<div class="spacer" style="height: 30px"></div>
		
		<?php foreach ($attributes as $a): ?>
		<?php $lock  = new magic3w\phpauth\AttributeLock($a, $authUser); ?>
		<?php $read  = $lock->unlock($app); ?>
		<?php $write = $lock->unlock($app, magic3w\phpauth\AttributeLock::MODE_W); ?>
		
		<div class="row l3">
			<div class="span l2">
				<a href="<?= url('edit', 'attribute', $a->_id) ?>"><?= $a->name ?></a>
			</div>
			<div class="span l1">
				<div class="styled-select">
					<form action="<?= url('permissions', 'set', $a->_id, $app->appID) ?>" method="GET">
						<input type="hidden" name="_XSRF"    value="<?= new spitfire\io\XSSToken() ?>">
						<input type="hidden" name="returnto" value="<?= url('permissions', 'on', $app->_id) ?>">
						<select name="grant" id="attr-<?= $a->_id ?>" onchange="this.form.submit()">
							<option value="<?= magic3w\phpauth\AttributeLock::MODE_N ?>" <?= $read === false && $write === false? 'selected' : '' ?>>No access</option>
							<option value="<?= magic3w\phpauth\AttributeLock::MODE_R ?>" <?= $read === true  && $write === false? 'selected' : '' ?>>Read-only access</option>
							<option value="<?= magic3w\phpauth\AttributeLock::MODE_W ?>" <?= $read === false && $write === true ? 'selected' : '' ?>>Write-only access</option>
							<option value="<?= magic3w\phpauth\AttributeLock::MODE_RW ?>" <?= $read === true  && $write === true ? 'selected' : '' ?>>Full access</option>
						</select>
					</form>
				</div>
			</div>
		</div>
		<div class="spacer" style="height: 30px"></div>
		<?php endforeach; ?>
		
		<div class="spacer" style="height: 30px"></div>
		
		<div class="heading">
			<h1 class="unpadded">Application connections</h1>
		</div>
		
		<div class="spacer" style="height: 10px"></div>
		
		<p class="small secondary">
			Connections are data exchanges between applications that are connected 
			to this authentication server. Please note that, while the authentication
			server manages whether these connections are allowed, the application 
			providing the data is required to enforce the policy you select. 
			<strong>
				The authentication server does not tap into the data being exchanged
				between the applications.
			</strong>
		</p>
		
		<div class="spacer" style="height: 30px"></div>
		
		<?php foreach($connections as $a): ?>
		<div class="row l9 fluid has-dials">
			<div class="span l1" style="text-align: center">
				<img class="app-icon middle" width="64" height="64" src="<?= url('image', 'app', $a->source->_id, 64) ?>">
			</div>
			<div class="span l6">
				<div class="app-name"><?= __($a->source->name) ?></div>
				<div>
					<p class="secondary small unpadded">
						<?php $ctx = $a->source->getContext($a->context); ?>
						<?php if($ctx->getDefined()): ?>
						<strong><?= __($ctx->getName()) ?></strong>
						<?= __($a->source->getContext($a->context)->getDescription()) ?>
						<?php else: ?>
						<!-- 
							If an application does not renew an expired context, the 
							context will cease to exist. To prevent this, the application
							is required to check whether the context was expired and create
							it anew.
						-->
						<strong><?= __($ctx->getId()) ?></strong>
						This context has expired, the application apparently does no longer 
						service it and has not renewed it. It should be safe to remove.
						<?php endif; ?>
					</p>
					<p class="secondary small unpadded">
						Expires <?= $a->expires? date('m/d/Y', $a->expires) : 'when revoked' ?>
						· Created <?= date('m/d/Y', $a->created) ?>
						<?php if ($a->user === null): ?>· <strong>Created by an administrator</strong><?php endif; ?>
						<?php if ($a->state == 1): ?>· <strong>Being denied access</strong><?php endif; ?>
						<?php if ($a->state == 0): ?>· <strong>Revoked</strong><?php endif; ?>
						<?php if ($a->final     ): ?>· <strong title="Cannot be revoked. This rule is mandatory. For further assistance, contact administration">Final</strong><?php endif; ?>
					</p>
				</div>
			</div>
			<div class="span l2 dials">
				<ul>
					<?php if ($a->state == 2): ?>
					<li><a href="<?= url('context', 'deny', $a->_id) ?>" title="This will prevent the app from accessing your data">Deny</a></li>
					<?php endif; ?>
					<?php if (!$a->final): ?>
					<li><a href="<?= url('context', 'revoke', $a->_id) ?>" title="This will require the app to request permission to access the data">Revoke</a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="spacer" style="height: 20px"></div>
		<?php endforeach; ?>
		<?php if ($connections->isEmpty()): ?>
		<div style="padding: 30px">
			<p class="small secondary" style="text-align: center">
				<strong>No connections.</strong>
				This means that this application has no special access privileges to
				data contained in other applications that refers to your account.
			</p>
		</div>
		<?php endif; ?>
		
		<div class="spacer" style="height: 30px"></div>
	</div>
</div>
