<?php /** @var UserModel $user */ ?>
<?php /** @var UsernameModel */ $username = $user->usernames->getQuery()->where('expires', 'IS', null)->fetch(); ?>
<div class="row">
	<div class="span">
		<div class="heading" data-sticky="top">
			<img src="<?= url('image', 'user', $user->_id, 64) ?>" width="64" height="64" class="user-icon small">
			<?= __($username->name) ?>
		</div>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>


<div class="row l3">
	<div class="span l1">
		<div style="font-size: .75em; color: #555">
			Registered since
		</div>
	</div>
	<div class="span l2">
			<?= date('m/d/Y', $user->created) ?>
	</div>
</div>

<div class="spacer" style="height: 20px"></div>

<div class="row l3">
	<div class="span l1">
		<div style="font-size: .75em; color: #555">
			Aliases
		</div>
	</div>
	<div class="span l2">
		<?php /** @var \spitfire\collection\Collection<UsernameModel> */$aliases = $user->usernames->getQuery()->where('expires', '>', strval(time()))->fetchAll(); ?>
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
				<img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/1x1/<?= strtolower($session->country) ?>.svg" style="vertical-align: middle; height: 18px;">
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



<?php $sessions = db()->table('session')->get('user', $user)->where('expires', '>', time())->all(); ?>
<?php foreach ($sessions as $session): ?>
<div class="h-6"></div>
<div class="container max-w-5xl mx-auto p-4 bg-white border border-gray-100 rounded box-shadow flex justify-between">
	<div>
		<div class="text-sm text-gray-600">Started <?= date('M d Y', $session->created) ?></div>
		<div class="h-2"></div>
		<div class="flex items-center gap-2">
			<img src="https://raw.githubusercontent.com/lipis/flag-icons/main/flags/1x1/<?= strtolower($session->country?: 'DE') ?>.svg" class="w-5 h-5 rounded-full">
			<span><?= $session->city?: 'Unknown city' ?></span>
			<?php if ($session->userTime && $session->country && !(\utils\TimeZone::check($session->getTimeZoneOffset(), $session->country))): ?>
				<span class="inline-block py-0.5 px-1 border border-indigo-500 text-indigo-500 bg-indigo-50 font-bold rounded leading-tight text-sm" title="Suspected VPN use">vpn</span>
			<?php endif; ?>
			<?php if ($session->ip): ?>
				<a href="<?= url('session', 'ip', $session->ip) ?>" class="inline-block py-0.5 px-1 border border-green-500 text-green-500 bg-green-50 font-bold rounded leading-tight text-sm" title="Recorded the IP of the session">ip</a>
			<?php endif; ?>
		</div>
		<div class="flex items-center gap-2">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
				<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
			</svg>
			<?= htmlspecialchars($session->locale) ?>
		</div>
	</div>
	<div>
		<a href="<?= url('session', 'end', $session->_id) ?>" class="gap-1 text-gray-600 hover:text-gray-800 flex items-center">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
			<span class="text-sm">End session</span>

		</a>
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
					<option value="y">Prevent the user from logging in</option>
					<option value="n">Restrict the user's functionality</option>
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
			<input type="submit" class="button error small" value="Suspend">
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

<?php $suspensions = db()->table(user\SuspensionModel::class)->get('user', $user)->where('expires', '>', time() - 360 * 2 * 86400)->setOrder('expires', 'DESC')->fetchAll(); ?>

<?php foreach ($suspensions as $suspension): ?>
	<div class="spacer" style="height: 20px"></div>
	
	<!--Here goes a suspension-->
	<div class="container mx-auto rounded-md bg-white border-solid border border-slate-100 relative">
		<div class="p-6 grow">
			<div class="whitespace-pre-wrap font-bold text-lg"><?= __($suspension->reason) ?></div>
			<div class="h-1"></div>
			<div class="whitespace-pre-wrap text-slate-500"><?= __($suspension->notes?: 'No reason given') ?></div>
			
			<div class="h-4"></div>
			
			<div class="flex gap-8 text-slate-500">
				<span class="flex items-center text-sm">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" /></svg>
					<span class="font-semibold"><?= $suspension->blame?: 'Unknown' ?></span>
				</span>
				<span class="flex items-center text-sm">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
					<span><span class="font-semibold ban-time" data-ban-time="<?=$suspension->created?>"><?= date('m/d/Y', $suspension->created) ?></span></span>
				</span>
				<span class="flex items-center text-sm">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
					<span><?=($suspension->expires > time())?'Ends':'Ended' ?> <span class="font-semibold ban-time" data-ban-time="<?=$suspension->expires?>"><?= date('m/d/Y', $suspension->expires) ?></span></span>
				</span>
			</div>
			<div class="absolute top-3 right-8">
				<?php if ($suspension->expires > time()): ?>
				<a class="hover:underline text-sm text-slate-500 hover:text-slate-700 flex items-center" href="<?= url('suspension', 'end', $suspension->_id) ?>">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
					Lift
				</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>
<script type="text/javascript">
	formatBanTime();
</script>
<?php endif; ?>

<div class="spacer" style="height: 50px"></div>
