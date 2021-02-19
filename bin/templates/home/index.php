<?php /** @var $user UserModel */ ?>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="row l1">
	<div class="span l1">
		<div class="message success">
			Request successful.
		</div>
	</div>
</div>


<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="spacer medium"></div>

<div class="row l5 s1">
	<div class="span l1 s1 align-center">
		<img src="<?= url('image', 'user', $user->_id, 256) ?>" class="user-icon round big not-mobile">
		<img src="<?= url('image', 'user', $user->_id, 256) ?>" class="user-icon round big mobile-only">
	</div>
	<div class="span l4 s1 jumbo">
		<div class="spacer minuscule"></div>
		<h1 class="text:grey-300">
			<span id="time-of-day-greeting">Welcome back</span>, <?= ucfirst($user->usernames->getQuery()->where('expires', null)->first()->name) ?>
		</h1>
		<p class="text:grey-500">
			On this page you will find all the settings relating to your account security and
			the devices and applications connected to your account.
		</p>
	</div>
</div>

<div class="spacer huge"></div>


<div class="row l1">
	<div class="span l1">
		<h2>Active sessions</h2>
	</div>
</div>


<?php $sessions = db()->table('session')->get('user', $user)->addRestriction('expires', time(), '>')->where('authenticated', true)->all(); ?>

<div class="row l1">
	<div class="span l1">
		<div class="material soft">
			<?php foreach ($sessions as $session): ?>
			<div class="row l9">
				<div class="span l1 desktop-only">
					<?php if ($session->location && $session->location->country) : ?>
					<img src="https://lipis.github.io/flag-icon-css/flags/4x3/<?= strtolower($session->country) ?>.svg" style="width: 100%">
					<?php endif; ?>
				</div>
				<div class="span l4">
					
					
					<div class="spacer minuscule"></div>
					
					<div>
						<img src="<?= url('image', 'app', $session->app->_id, 256) ?>" class="mobile-only" style="vertical-align: middle;  width: 32px">
						
						<strong>
							<?php if ($session->location): ?>
							<span  class="text:grey-300"><?= $session->location->city ?></span>
							<?php else: ?>
							<span  class="text:grey-300"><?= $session->app->name ?></span>
							<?php endif; ?>
						</strong>
						
					</div>
					
					<div>
						<small>
							<span  class="text:grey-700">Created <?= Time::relative($session->created) ?></span>
						</small>
					</div>
					
					<div class="spacer minuscule"></div>
					
					<div>
						<?php if ($session->device): ?>
						<span>
							<span class="device-icon <?= $session->device->category() ?>" title="<?= $session->device->category() ?>"></span>
						</span>
						<?php endif ?>
						<img  src="<?= url('image', 'app', $session->app->_id, 32) ?>" width="24" height="24">
					</div>
					
					<div>
						<?php $uses = db()->table('token\usage')->get('token', $session)->all(); ?>
						<?php foreach ($uses as $use) : ?>
						<span>
							<img src="<?= url('image', 'app', $use->app->_id, 64) ?>" title="<?= $use->app->name ?>" style="width: 32px">
						</span>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="span l4">

					<div class="align-right">
						<a class="button small outline button-color-red-300" href="<?= url('token', 'end', $session->token) ?>">End session</a>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div class="spacer large"></div>

<script type="text/javascript">
(function () {
	var greeting = document.getElementById('time-of-day-greeting');
	var hour = (new Date()).getHours();
	
	if (hour <  6) { greeting.innerHTML = 'Good night'; return; }
	if (hour < 11) { greeting.innerHTML = 'Good morning'; return; }
	if (hour < 15) { greeting.innerHTML = 'Good afternoon'; return; }
	if (hour < 19) { greeting.innerHTML = 'Good evening'; return; }
	greeting.innerHTML = 'Good night';
}());
</script>
