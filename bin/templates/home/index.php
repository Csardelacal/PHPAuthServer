<?php /** @var $user UserModel */ ?>

<?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
<div class="row1">
	<div class="span1">
		<div class="message success">
			Request successful.
		</div>
	</div>
</div>

<div class="spacer" style="height: 30px"></div>
<?php endif; ?>

<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Edit your profile
</div>

<div class="spacer" style="height: 30px"></div>

<div class="row7 fluid">
	<div class="span1 desktop-only" style="text-align: right">
		<!--Avatar-->
		<a href="<?= url('edit', 'avatar') ?>">
			<img src="<?= url('image', 'user', $user->_id, 64) ?>" class="user-icon round medium">
		</a>
	</div>
	
	<div class="span5">
		<h1>
			<?= ucfirst($user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name) ?>
			<small><a href="<?= url('edit', 'username') ?>">Edit</a></small>
		</h1>
		
		<div>
			<?= $user->email ?>
			<a href="<?= url('edit', 'email') ?>">Edit</a>
		</div>
	</div>
</div>


<div class="spacer" style="height: 40px"></div>

<?php $attributes = db()->table('attribute')->get('writable', Array('me', 'groups', 'public'))->fetchAll(); ?>
<?php foreach ($attributes as $attribute): ?>

<div class="row6 fluid has-dials">
	<div class="span5">
		<div style="font-size: .75em; color: #555">
			<?= $attribute->name ?>
		</div>
		
		<?php $attrValue = $user->attributes->getQuery()->addRestriction('attr', $attribute)->fetch() ?>
		
		<div class="spacer" style="height: 5px"></div>
		<div style="border-left: solid 2px #2a912e; padding: 8px 15px; font-size: .85em; color: #333; margin: 7px 0;">
			<div>
				<?php if ($attribute->datatype === 'file'): ?>
				<div style="text-align: center">
					<img src="<?= url('image', 'attribute', $attribute->_id, $user->_id, ['nonce' => time()]) ?>" style="max-width: 100%; max-height: 200px">
				</div>
				<?php elseif ($attribute->datatype === 'text'): ?>
				<div style="white-space: pre-wrap;"><?= $attrValue? __($attrValue->value, 200) : '<em>Undefined</em>' ?></div>
				<?php elseif ($attribute->datatype === 'boolean'): ?>
				<div style="white-space: pre-wrap;"><?= $attrValue->value? 'Yes' : 'No' ?></div>
				<?php else: ?>
				<div><?= $attrValue? __($attrValue->value, 45) : '<em>Undefined</em>' ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="span1 dials">
		<ul>
			<li><a href="<?= url('edit', 'attribute', $attribute->_id) ?>">Edit</a></li>
		</ul>
	</div>
</div>
<div class="spacer" style="height: 30px;"></div>
<?php endforeach; ?>

<!-- 
	List the user's authorized apps. This way they can see what applications they
	can log into with their account without confirmation.
-->
<?php $authorized = db()->table('user\authorizedapp')->get('user', $user)->fetchAll(); ?>

<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Authorized apps
</div>

<?php foreach ($authorized as $auth): ?>
<?php $app = $auth->app ?>
<div class="spacer" style="height: 10px"></div>
<div class="row6 fluid has-dials">
	<div class="span5">
		<img src="<?= url('image', 'app', $app->_id, 32) ?>" style="vertical-align: middle;  height: 18px;">
		
		<?php if ($app->url): ?><a href="<?= $app->url ?>"><?= $app->name ?></a>
		<?php else: ?><span><?= $app->name ?></span><?php endif; ?>
	</div>
	<div class="span1 dials">
		<ul>
			<li><a href="<?= url('app', 'deauthorize', $app->_id) ?>">Remove</a></li>
		</ul>
	</div>
</div>
<?php endforeach; ?>

<div class="spacer" style="height: 30px"></div>


<?php $sessions = db()->table('token')->get('user', $user)->addRestriction('expires', time(), '>')->addRestriction('app', null, 'IS NOT')->fetchAll(); ?>
<div class="topbar sticky">
	<span class="toggle-button-target" style="background: #2a912e; padding: 12px; margin: 0 10px 0 -10px; vertical-align: middle"></span>
	Active sessions
</div>

<?php foreach ($sessions as $session): ?>
<div class="spacer" style="height: 10px"></div>
<div class="row6 fluid has-dials">
	<div class="span3">
		<img src="<?= url('image', 'app', $session->app->_id, 32) ?>" style="vertical-align: middle; height: 18px;">
		<?php if ($session->app->url): ?><a href="<?= $session->app->url ?>"><?= $session->app->name ?></a>
		<?php else: ?><span><?= $session->app->name ?></span><?php endif; ?>
	</div>
	<div class="span2">
		<?php if ($session->country): ?>
		<img src="https://lipis.github.io/flag-icon-css/flags/4x3/<?= strtolower($session->country) ?>.svg" style="vertical-align: middle; height: 18px;">
		<span><?= $session->city ?></span>
		<?php endif; ?>
	</div>
	<div class="span1 dials">
		<ul>
			<li><a href="<?= url('token', 'end', $session->token) ?>">End session</a></li>
		</ul>
	</div>
</div>
<?php endforeach; ?>

<div class="spacer" style="height: 50px"></div>
