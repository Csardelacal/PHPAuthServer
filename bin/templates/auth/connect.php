
<div class="spacer" style="height: 40px"></div>

<div class="row4">
	<div class="span1"></div>
	
	<div class="span2">
		<div class="row5 fluid">
			<div class="span1"></div>
			<div class="span3">
				<div class="row3 fluid desktop-only">
					<div class="span1"><img src="<?= url('image', 'app', $src->_id, 256) ?>" style="width: 100%; border-radius: 3px; border: solid 2px #FFF;"></div>
					<div class="span1" style="text-align: center; line-height: 90px;"><img src="<?= spitfire\core\http\URL::asset('img/link.png') ?>" style="width: 50%; vertical-align: middle"></div>
					<div class="span1"><img src="<?= url('image', 'app', $tgt->_id, 256) ?>" style="width: 100%; border-radius: 3px; border: solid 2px #FFF;"></div>
				</div>
			</div>
		</div>
		
		<div class="spacer" style="height: 40px"></div>
		
		<div class="material unpadded">
			<div class="padded">
				<div class="row1 fluid" style="font-size: .8em;">
					<strong><?= __($src->name) ?></strong> requested permission to access data contained in 
					<strong><?= __($tgt->name) ?></strong>. 

					<?php if ($ctx): ?>
					Please confirm that you wish to allow these applications to exchange the following data:
					<?php else: ?>
					Please confirm that you wish to allow these applications to exchange data.
					<?php endif; ?>
				</div>
			</div>

			<?php if ($ctx): ?>
			<div class="spacer" style="height: 10px"></div>

			<div class="row1 fluid" style="background: #707070; box-shadow: 0px 1px 2px #444 inset; padding: 15px; font-size: .8em; border: solid 1px #444; border-left: none; border-right: none; color: #FFF;">
				<div><strong style="font-size: 1.2em"><?= __($ctx->title) ?></strong></div>
				<div class="spacer" style="height: 10px"></div>
				<div><?= __($ctx->descr) ?></div>
			</div>
			<?php endif; ?>
			
			<div class="padded">
				<div class="row1 fluid" style="font-size: .7em; color: #555;">
					You can revoke this permission at any time from your account
					settings page. Do not proceed if you do not trust <?= __($src->name) ?>
					to treat your data carefully.
				</div>
			</div>
		</div>
	</div>
</div>