
<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<img src="<?= url('image', 'user', $user->_id, 64) ?>" width="64" height="64" class="user-icon small">
			<?= __($user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name) ?>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>


<div class="row l2">
	<div class="span l1">
		<div style="font-size: .75em; color: #555">
			Registered since
		</div>
	</div>
	<div class="span l1">
			<?= date('m/d/Y', $user->created) ?>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row l2">
	<div class="span l1">
		<div style="font-size: .75em; color: #555">
			Aliases
		</div>
	</div>
	<div class="span l1">
		<?php $aliases = $user->usernames->getQuery()->addRestriction('expires', time(), '>')->fetchAll(); ?>
		<?= $aliases->count()? $aliases->join('<br>') : '<i>None</i>'; ?>
	</div>
</div>

<div class="spacer" style="height: 50px"></div>

<?php if (isset($userIsAdmin) && $userIsAdmin): ?>
<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			Sessions
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<?php $sessions = db()->table('token')->get('user', $user)->where('expires', '>', time() - 86400 * 90)->all(); ?>

<?php foreach ($sessions as $session): ?>
<div class="spacer" style="height: 10px"></div>
<div class="row">
	<div class="span">
		<div class="row l6 fluid has-dials">
			<div class="span l3">
				<img src="<?= url('image', 'app', $session->app->_id, 32) ?>" class="app-icon small" width="32" height="32">
				<span class="app-name"><?= $session->app->name ?></span>
			</div>
			<div class="span l2">
				<?php if ($session->country): ?>
				<img src="https://lipis.github.io/flag-icon-css/flags/4x3/<?= strtolower($session->country) ?>.svg" style="vertical-align: middle; height: 18px;">
				<span><?= $session->city ?></span>
				<?php endif; ?>
			</div>
			<div class="span l1 dials">
				<ul>
					<?php if ($session->expires > time()): ?>
					<li><a href="<?= url('token', 'end', $session->token) ?>">End session</a></li>
					<?php else: ?>
					<li>Expired</li>
					<?php endif; ?> 
				</ul>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>

<div class="spacer" style="height: 20px"></div>

<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			New suspension
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<form class="regular" method="POST" action="<?= url('suspension', 'create', $user->_id); ?>">
	<!--Reason for the suspension-->
	<div class="row l2">
		<div class="span l1">
			<div style="font-size: .75em; color: #555">
				Reason given to the user
			</div>
			
			<div class="spacer" style="height: 5px"></div>

			<div class="field">
				<textarea name="reason" placeholder="Enter a reason that explains to the user why they were suspended..." style="height: 50px"></textarea>
			</div>
		</div>
	
		<!--Administrative notes-->
		<div class="span l1">
			<div style="font-size: .75em; color: #555">
				Administrative notes
			</div>
			
			<div class="spacer" style="height: 5px"></div>

			<div class="field">
				<textarea name="notes" placeholder="Notes here won't be shown to the user, just administrators..." style="height: 50px"></textarea>
			</div>
		</div>
	</div>
	
	<div class="row l10 m5 s3">
		<div class="span l5 desktop-only"></div>
		<div class="span l2 m2 s1" style="text-align: right">
			<div class="styled-select">
				<select name="blockLogin">
					<option value="n">Restrict the user's functionality</option>
					<option value="y">Prevent the user from logging in</option>
				</select>
			</div>
		</div>
		<div class="span l2 m2 s1" style="text-align: right">
			<div class="styled-select">
				<select name="duration">
					<option value="0h">Immediate</option>
					<option value="6h">6 Hours</option>
					<option value="12h">12 Hours</option>
					<option value="1d">1 Day</option>
					<option value="3d">3 Days</option>
					<option value="1w">1 Week</option>
					<option value="2w">2 Weeks</option>
					<option value="1m">1 Month</option>
					<option value="3m">3 Months</option>
					<option value="6m">6 Months</option>
					<option value="1y">1 Year</option>
					<option value="10y">10 Years</option>
				</select>
			</div>
		</div>
		<div class="span l1 m1 s1" style="text-align: right">
			<input type="submit" class="button error" value="Suspend">
		</div>
	</div>
</form>

<div class="spacer" style="height: 20px"></div>

<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			Previous suspensions
		</div>
	</div>
</div>

<?php $suspensions = db()->table('user\suspension')->get('user', $user)->setOrder('expires', 'DESC')->fetchAll(); ?>

<?php foreach ($suspensions as $suspension): ?>
	<div class="spacer" style="height: 20px"></div>
	
	<!--Here goes a suspension-->
	<div class="row l5 has-dials">
		<div class="span l4">
			<div style="font-size: .75em; color: #555">
				<?= $suspension->preventLogin? 'Banned' : 'Suspended' ?> until <?= date('m/d/Y', $suspension->expires) ?> for:
			</div>

			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<p style="white-space: pre-wrap; margin: 0; font-style: italic"><?= __($suspension->reason) ?></p>
				<div class="spacer" style="height: 10px"></div>
				<p style="white-space: pre-wrap; margin: 0;"><?= __($suspension->notes) ?></p>
			</div>
		</div>
		
		<div class="span l1 dials">
			<ul>
				<?php if ($suspension->expires > time()): ?>
				<li><a href="<?= url('suspension', 'end', $suspension->_id) ?>">Lift</a></li>
				<?php endif; ?>
				<li><a href="<?= url('suspension', 'edit', $suspension->_id) ?>">Edit notes</a></li>
			</ul>
		</div>
	</div>
<?php endforeach; ?>
<?php endif; ?>

<div class="spacer" style="height: 50px"></div>