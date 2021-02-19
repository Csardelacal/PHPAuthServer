
<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<h1 class="text:grey-300">Phones registered with your account</h1>
		
		<p class="text:grey-500">
			<small>
				These are the phones connected to your account, you can add or remove
				additional phones to your account to improve your account security.
				Phones are used to verify that you are actually the person trying to 
				log into your account or authenticating with an application.
			</small>
		</p>
	</div>
</div>
<div class="spacer medium"></div>

<div class="row l1">
	<div class="span l1">
		<?php foreach ($phones as $phone): ?>
		<div class="material">
			<div class="row l9">
				<div class="span l1">
					<!-- Icon -->
				</div>
				<div class="span l7">
					<div class="spacer small"></div>
					
					<div>
						<strong><?= __(substr($phone->content, 0, 4)) . str_repeat('*', strlen($phone->content) - 8) . __(substr($phone->content, -4)) ?></strong>
					</div>
					
					<div class="spacer minuscule"></div>
					
					<?php if ($phone->passport): ?>
					<span class="text:grey-500">
						<small>Can be used to log into this account &centerdot; You do not allow it to be used with other accounts</small>
					</span>
					<?php endif ?>
					
					<div class="spacer small"></div>
				</div>
				<div class="span l1 align-right">
					<div style="font-size: .8rem">
						<?php if ($phone->verified) : ?>
						<span class="text:grey-500"><?= date('d/m/Y', $phone->verified) ?></span>
						<?php else: ?>
						<a class="text:red-500" href="<?= url('phone', 'twofactor', $phone->_id) ?>"><strong>Not yet verified</strong></a>
						<?php endif; ?>
					</div>
					
					<div class="spacer small"></div>
					
					<a class="button outline small button-color-red-300" href="<?= url('phone', 'remove', $phone->_id) ?>">Remove</a>
				</div>
			</div>
		</div>
		<div class="spacer medium"></div>
		<?php endforeach; ?>
	</div>
</div>

<div class="spacer medium"></div>

<div class="align-center">
	<a class="button outline" href="<?= url('phone', 'create') ?>">+ Add a phone</a>
</div>

<div class="spacer large"></div>

<div class="row l1">
	<div class="span l1">
		<p class="text:grey-500">
			<small>
				All phones on this list can be used to provide Multi-Factor (or two factor)
				authentication. 
				<strong>
					If this list includes a phone number that is not yours you should
					remove it as soon as possible.
				</strong>
			</small>
		</p>
	</div>
</div>
<div class="spacer medium"></div>