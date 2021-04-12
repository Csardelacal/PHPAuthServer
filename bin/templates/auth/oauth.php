

<div class="spacer small"></div>

<div class="row l5">
	<div class="span l1">
		
	</div>
	<div class="span l3">
		<form method="POST" action="">
			<div class="align-center text:green-600">
				<img src="<?= url('image', 'app', $client->_id, 128) ?>" width="128" style="border-radius: 50%; border: solid 1px #777; vertical-align: middle;">
				<div style="display: inline-block; width: 50px; border-top: solid 1px #CCC; vertical-align: middle;"></div>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="height: 40px; vertical-align: middle;">
					<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
				</svg>
				<div style="display: inline-block; width: 50px; border-top: solid 1px #CCC; vertical-align: middle;"></div>
				<img src="<?= url('image', 'app', $audience->_id, 128) ?>" width="128"  style="border-radius: 50%; border: solid 1px #777; vertical-align: middle;">
			</div>
			
			<div class="align-center">
				<div class="spacer large"></div>
				<h1 class="text:grey-700" style="font-size: 1.8rem">Authorize <?= $client->name ?></h1>
				<div class="spacer small"></div>
			</div>
		
			<div class="box box-soft">
				<div class="padded">
					<div class="spacer medium"></div>
					
					<div class="row s7">
						<div class="span s1">
							<img src="<?= url('image', 'user', $client->owner->_id, 128) ?>" width="128"  style="border-radius: 50%; vertical-align: middle;">
						</div>
						<div class="span s6 text:grey-700">
							<div class="spacer minuscule"></div>
							<div>
								<strong class="text:grey-800"><?= $client->name ?></strong>
								by <strong class="text:grey-800"><?= $client->owner->usernames->getQuery()->first()->name ?></strong>
							</div>
							<div>
								is requesting access your data on <strong class="text:grey-800"><?= $audience->name ?></strong>
							</div>
						</div>
					</div>
			
					<p class="">
					</p>
					
					<div class="spacer large"></div>

					<div style="text-align: center">
						<input type="hidden" name="grant" value="grant">
						<input type="submit" value="Grant access to your account" class="button full-width" style="width: 100%">
						<div class="spacer small"></div>
						<a href="<?= $cancel ?>">Cancel</a>
					</div>
					
					
					<div class="spacer medium"></div>
				</div>
			</div>
		</form>
		
		<div class="spacer large"></div>

		<p class="text:grey-600 align-center" style="font-size: .8rem">
			Authorizing redirects to <strong class="text:grey-700"><?= $redirect ?></strong>
		</p>
	</div>
</div>
