

<div id="app" class="mx-auto max-w-screen-lg">
	<vtable :headers="['User', 'Location', 'IP', '']">
		<?php foreach ($records as $session): ?> 
		<?php $user = ['username' => strval($session->user), 'avatar' => strval(url('image', 'user', $session->user->_id, 256))]; ?>
		<?php $location = ['country' => $session->country?: 'DE', 'city' => $session->city]; ?>
		<session 
			id="<?= $session->_id ?>"
			ip="<?= $session->ip ?>"
			:user="<?= htmlentities(json_encode($user), ENT_QUOTES) ?>"
			:location="<?= htmlentities(json_encode($location), ENT_QUOTES) ?>"
			:vpn="<?= $session->userTime && $session->country && !(\utils\TimeZone::check($session->getTimeZoneOffset(), $session->country))? 'true' : 'false' ?>"
			></session>
		<?php endforeach; ?> 
	</vtable>
</div>
<script src="<?= url() ?>assets/js/session/ip.min.js"></script>

<div class="spacer" style="height: 20px"></div>

<?= $pages ?>

<div class="spacer" style="height: 50px"></div>
