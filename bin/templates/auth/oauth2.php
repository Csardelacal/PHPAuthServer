


<form method="POST" action="" class="bg-slate-100">
	<div class="h-4"></div>
	<div class="container mx-auto max-w-xl">
		<div class="justify-center text-green-600 flex items-center">
			<img src="<?= url('image', 'app', $client->_id, 128) ?>" width="128" style="border-radius: 50%; border: solid 1px #777; vertical-align: middle;">
			<div style="display: inline-block; width: 50px; border-top: solid 1px #CCC; vertical-align: middle;"></div>
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="height: 40px; vertical-align: middle;">
				<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
			</svg>
			<div style="display: inline-block; width: 50px; border-top: solid 1px #CCC; vertical-align: middle;"></div>
			<!-- TODO: There might not be an audience -->
			<img src="<?= $audience->_id? url('image', 'app', $audience->_id, 128) : url('image', 'user', $user->_id, 128) ?>" width="128"  style="border-radius: 50%; border: solid 1px #777; vertical-align: middle;">
		</div>
		
		<div class="h-16"></div>
		
		<div class="text-center">
			<span class="text-grey-700 text-2xl font-bold">Authorize <?= $client->name ?></span>
			<div class="h-8"></div>
		</div>
		
		<div class="rounded-md bg-white shadow">
			<div class="p-4">
				<div class="h-4"></div>
			
				<?php $owner = $client->owner?: db()->table('user')->get('_id', 1)->first(); ?>
				<div class="flex items-center gap-4">
					<img src="<?= url('image', 'user', $owner->_id, 128) ?>" width="128"  class="rounded-full w-16 h-16">
					<div class="text-gray-600">
						<div>
							Developed by <strong class="text:grey-800"><?= $owner->usernames->getQuery()->first()->name ?></strong>
						</div>
						<div>
							is requesting access to your account on <?= $_SERVER['SERVER_NAME'] ?>.
						</div>
					</div>
				</div>
		
			<div class="h-8"></div>
		</div>
		
		<div class="border-t border-t-gray-300"></div>
		
		<div class="p-4">
			<div class="h-4"></div>
			<div class="flex items-center gap-4">
				<div class="w-16 flex-0">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
				</div>
				<div>
					<div><strong>Account information</strong></div>
					<div>Basic account information. Including your user-id, username, and avatar.</div>
				</div>
			</div>
			<div class="h-4"></div>
		</div>
		
		<div class="border-t border-t-gray-300"></div>
		
		<div class="p-4">
			<div class="h-8"></div>
			
			<?php if ($audience) : ?>
				<-- TODO Implement audiences -->
				your data on <strong class="text:grey-800"><?= $audience->name ?></strong>
			<?php endif; ?>

			<div class="text-center">
				<input type="hidden" name="grant" value="grant">
				<input type="submit" value="Grant access to your account" class="w-full bg-sky-500 text-white rounded px-6 py-2 font-bold">
				<div class="h-2"></div>
				<a href="<?= $cancel ?>">Cancel</a>
			</div>
				
				
				<div class="spacer medium"></div>
			</div>
		</div>
		
		<div class="spacer large"></div>

		<p class="text-gray-600 text-center p-4 text-sm">
			Authorizing redirects to <strong class="text:grey-700"><?= $redirect ?></strong>
		</p>
	</div>

</form>