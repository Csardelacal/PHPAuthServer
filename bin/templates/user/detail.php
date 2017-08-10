
<div class="topbar sticky">
	<span class="toggle-button-target"  style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Profile: <?= __($profile->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name) ?>
</div>

<div class="spacer" style="height: 20px"></div>


<div class="row1 fluid">
	<div class="span1">
		<div style="font-size: .75em; color: #555">
			Registered since
		</div>

		<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
			<?= date('m/d/Y', $profile->created) ?>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row1 fluid">
	<div class="span1">
		<div style="font-size: .75em; color: #555">
			Aliases
		</div>

		<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
			<?php $aliases = $profile->usernames->getQuery()->addRestriction('expires', time(), '>')->fetchAll(); ?>
			<?= $aliases->count()? $aliases->join('<br>') : '<i>None</i>'; ?>
		</div>
	</div>
</div>

<div class="spacer" style="height: 50px"></div>

<?php if (isset($userIsAdmin) && $userIsAdmin): ?>
<div class="topbar sticky">
	<span class="toggle-button-target"  style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	New suspension
</div>

<div class="spacer" style="height: 20px"></div>

<form class="regular" method="POST" action="<?= url('suspension', 'create', $profile->_id); ?>">
	<!--Reason for the suspension-->
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Reason given to the user
			</div>

			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<textarea name="reason" placeholder="Enter a reason that explains to the user why they were suspended..." style="height: 50px"></textarea>
			</div>
		</div>
	</div>
	
	<!--Administrative notes-->
	<div class="row1 fluid">
		<div class="span1">
			<div style="font-size: .75em; color: #555">
				Administrative notes
			</div>

			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<textarea name="notes" placeholder="Notes here won't be shown to the user, just administrators..." style="height: 50px"></textarea>
			</div>
		</div>
	</div>
	
	<div class="row1 fluid">
		<div class="span1" style="text-align: right">
			<select name="blockLogin">
				<option value="n">Restrict the user's functionality</option>
				<option value="y">Prevent the user from logging in</option>
			</select>
			<select name="duration">
				<option value="0h">Immediate</option>
				<option value="6h">6 Hours</option>
				<option value="1d">1 Day</option>
				<option value="3d">3 Days</option>
				<option value="1w">1 Week</option>
				<option value="2w">2 Weeks</option>
				<option value="1m">1 Month</option>
				<option value="3m">3 Months</option>
				<option value="6m">6 Months</option>
				<option value="1y">1 Year</option>
			</select>
			<input type="submit" class="button error" value="Suspend">
		</div>
	</div>
</form>

<div class="spacer" style="height: 20px"></div>

<div class="topbar sticky">
	<span class="toggle-button-target"  style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Previous suspensions
</div>

<?php $suspensions = db()->table('user\suspension')->get('user', $profile)->setOrder('expires', 'DESC')->fetchAll(); ?>

<?php foreach ($suspensions as $suspension): ?>
	<div class="spacer" style="height: 20px"></div>
	
	<!--Here goes a suspension-->
	<div class="row5 fluid has-dials">
		<div class="span4">
			<div style="font-size: .75em; color: #555">
				<?= $suspension->preventLogin? 'Banned' : 'Suspended' ?> until <?= date('m/d/Y', $suspension->expires) ?> for:
			</div>

			<div class="field" style="border-left: solid 2px #2a912e; padding: 8px 0px 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
				<p style="white-space: pre-wrap; margin: 0; font-style: italic"><?= __($suspension->reason) ?></p>
				<div class="spacer" style="height: 10px"></div>
				<p style="white-space: pre-wrap; margin: 0;"><?= __($suspension->notes) ?></p>
			</div>
		</div>
		
		<div class="span1 dials">
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