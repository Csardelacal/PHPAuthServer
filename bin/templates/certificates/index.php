

<!-- Go on about the contexts defined by this application-->
<div class="h-12"></div>

<div class="mx-auto max-w-screen-lg">
	<h1 class="text-2xl font-bold text-black">Signing Keys</h1>
	<div class="font-bold text-md text-sky-500">
		To sign JWT tokens using assymetric crypto.
	</div>
</div>

<div class="h-8"></div>

<?php foreach ($keys as $key): ?>
<div class="mx-auto max-w-screen-lg border border-gray-200 bg-white shadow rounded p-4 relative">
	<?php if ($key->expires === null) : ?>
	<a class="absolute top-2 right-2 hover:underline text-sm text-slate-500 hover:text-slate-700 flex items-center" href="<?= url('certificates', 'expire', $key->_id) ?>">
		<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
		Expire
	</a>
	<?php else: ?>
	<span class="absolute top-2 right-2 text-sm text-slate-500 flex items-center">
		<svg xmlns="http://www.w3.org/2000/svg" class="fill-current text-slate-500 w-3 h-3 mr-2" viewBox="0 0 20 20" fill="currentColor">
			<path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
		</svg>
		<?= $key->expires === null? 'active' : date('Y/m/d', $key->expires) ?>
	</span>
	<?php endif; ?>
	<div class="">
		<div class="text-sm whitespace-pre font-mono text-gray-600"><?= __($key->public) ?></div>
	</div>
</div>
	
<div class="h-8"></div>
<?php endforeach; ?>

<?php if ($keys->isEmpty()): ?>
<div class="p-12 text-center italic text-gray-500">
	This application has defined no keys
</div>
<?php endif; ?>


<a href="<?= url('certificates', 'keygen') ?>" class="rounded-full bg-sky-600 hover:bg-sky-500 active:bg-sky-600 text-white px-6 py-4 fixed bottom-10 right-6 text-lg font-bold shadow-md">
	<div class="flex items-center">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" /></svg>
		Generate new key
	</div>
</a>
