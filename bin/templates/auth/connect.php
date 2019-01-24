
<div class="spacer" style="height: 40px"></div>

<div class="row l4">
	<div class="span l1"></div>
	
	<div class="span l2">
		<div class="row l5 fluid">
			<div class="span l1"></div>
			<div class="span l3">
				<div class="row l3 fluid desktop-only"><!--
					--><div class="span l1"><img src="<?= url('image', 'app', $src->_id, 256) ?>" style="width: 100%; border-radius: 3px; border: solid 2px #FFF;"></div><!--
					--><div class="span l1" style="text-align: center; line-height: 90px;"><img src="<?= spitfire\core\http\URL::asset('img/link.png') ?>" style="width: 50%; vertical-align: middle"></div><!--
					--><div class="span l1"><img src="<?= url('image', 'app', $tgt->_id, 256) ?>" style="width: 100%; border-radius: 3px; border: solid 2px #FFF;"></div>
				</div>
			</div>
		</div>
		
		<div class="spacer" style="height: 40px"></div>
		
		<div class="material unpadded">
			<div class="padded">
				<div class="row l1 fluid" style="font-size: .8em;">
					<div class="span l1">
						<strong><?= __($src->name) ?></strong> requested permission to access data contained in 
						<strong><?= __($tgt->name) ?></strong>. 

						<?php if (!$ctx->isEmpty()): ?>
						Please confirm that you wish to allow these applications to exchange the following data:
						<?php else: ?>
						Please confirm that you wish to allow these applications to exchange data.
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ($ctx): ?>
			<div class="spacer" style="height: 10px"></div>
			
			<div class="row l1 fluid" style="background: #707070; box-shadow: 0px 1px 2px #444 inset; padding: 15px; font-size: .8em; border: solid 1px #444; border-left: none; border-right: none; color: #FFF;">
				<div class="span l1">
					<?php  foreach ($ctx as $c): ?>
					<div><strong style="font-size: 1.2em"><?= __($c->getName()) ?></strong></div>
					<div class="spacer" style="height: 10px"></div>
					<div><?= __($c->getDescription()) ?></div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
			
			<div class="padded">
				<div class="row l1 fluid" style="font-size: .7em; color: #555;">
					<div class="span l1">
						You can revoke this permission at any time from your account
						settings page. Do not proceed if you do not trust <?= __($src->name) ?>
						to treat your data carefully.
					</div>
				</div>
			</div>
		</div>
		
		<div class="spacer" style="height: 20px;"></div>
		
		<div style="text-align: right">
			<a class="button" href="<?= url('auth', 'connect', $confirm, $_GET->getRaw()) ?>">Allow connection</a>
		</div>
	</div>
</div>